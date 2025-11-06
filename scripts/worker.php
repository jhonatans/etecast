<?php

require __DIR__ . '/../vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Aponta para a raiz /etecast
// $dotenv->load();
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignora comentários
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove aspas se existirem
        $value = trim($value, '"\'');
        
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/database.php';

use app\models\Content;

// Configurações de logging para o worker
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_LOG_PATH);

echo "[" . date('Y-m-d H:i:s') . "] Worker started in continuous mode.\n";

// LOOP INFINITO PARA SUPERVISOR
while (true) {
    $maxJobsPerRun = 5;
    $processedJobs = 0;

    try {
        // Busca um trabalho pendente na fila
        $stmt = $pdo->prepare("SELECT * FROM queue_jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1");
        $stmt->execute();
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $processedJobs++;
            echo "[" . date('Y-m-d H:i:s') . "] Processing job ID: " . $job['id'] . "\n";

            // Atualiza o status para 'processing'
            $updateStmt = $pdo->prepare("UPDATE queue_jobs SET status = 'processing', started_at = NOW() WHERE id = ?");
            $updateStmt->execute([$job['id']]);

            // Decidir a ação com base no tipo de trabalho
            switch ($job['job_type']) {
                case 'transcode_media':
                    processTranscodeMediaJob($pdo, $job);
                    break;
                default:
                    echo "[" . date('Y-m-d H:i:s') . "] Unknown job type: " . $job['job_type'] . " for job ID: " . $job['id'] . "\n";
                    // Marca como falho
                    $updateStmt = $pdo->prepare("UPDATE queue_jobs SET status = 'failed', finished_at = NOW(), log = CONCAT(log, '\n[ERROR] Tipo de trabalho desconhecido.') WHERE id = ?");
                    $updateStmt->execute([$job['id']]);
                    break;
            }
        } else {
            // Se não há jobs, espera 10 segundos antes de verificar novamente
            echo "[" . date('Y-m-d H:i:s') . "] No pending jobs found. Sleeping for 10 seconds...\n";
            sleep(10);
        }

    } catch (\Exception $e) {
        error_log("Worker FATAL ERROR: " . $e->getMessage());
        echo "[" . date('Y-m-d H:i:s') . "] Worker encountered a fatal error: " . $e->getMessage() . "\n";
        // Espera 30 segundos antes de continuar em caso de erro grave
        sleep(30);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Processed " . $processedJobs . " jobs in this cycle.\n";
    
    // Pequena pausa entre ciclos mesmo quando há jobs
    sleep(2);
}

/**
 * Função para processar o trabalho de transcodificação de mídia.
 */
function processTranscodeMediaJob(PDO $pdo, array $job) {
    $contentModel = new Content($pdo);
    $jobLog = "Iniciando transcodificação para " . $job['original_file_path'] . "\n";
    $status = 'failed';

    try {
        $originalFilePath = $job['original_file_path'];
        $contentType = $job['content_type'];
        $title = $job['title'];
        $description = $job['description'];
        $adminId = $job['admin_id'];

        // Garante que o diretório de destino existe e é único para cada conteúdo
        $contentBaseDir = MEDIA_PROTECTED_PATH . $contentType . '/' . uniqid('content_');
        if (!mkdir($contentBaseDir, 0775, true) && !is_dir($contentBaseDir)) {
             throw new \Exception("Não foi possível criar o diretório de destino: " . $contentBaseDir);
        }

        $mediaFilePath = null; // Caminho final do arquivo no DB
        $hlsManifestPath = null; 

        switch ($contentType) {
            case 'video':
                // Chamar o script de transcodificação FFmpeg
                $cmd = escapeshellcmd(__DIR__ . '/transcode_video.sh') . ' ' . escapeshellarg($originalFilePath) . ' ' . escapeshellarg($contentBaseDir);
                $jobLog .= "Executando comando: $cmd\n";
                
                // Redireciona a saída do shell para o log do FFmpeg
                $output = shell_exec($cmd . ' 2>&1');
                file_put_contents(FFMPEG_LOG_PATH, $output, FILE_APPEND);
                $jobLog .= "Output FFmpeg:\n" . $output . "\n";

                // Verificar se o manifesto HLS foi criado com sucesso
                if (file_exists($contentBaseDir . '/stream.m3u8')) {
                    $mediaFilePath = $contentType . '/' . basename($contentBaseDir) . '/stream.m3u8';
                    $hlsManifestPath = $contentType . '/' . basename($contentBaseDir) . '/stream.m3u8';
                    $status = 'completed';
                    $jobLog .= "Transcodificação de vídeo concluída com sucesso.\n";
                } else {
                    $jobLog .= "Erro: Manifesto HLS 'stream.m3u8' não encontrado após transcodificação.\n";
                    throw new \Exception("Manifesto HLS não gerado.");
                }
                break;

            case 'podcast':
                // Para podcast, apenas move o arquivo original ou faz uma cópia
                $destFileName = basename($originalFilePath);
                $finalMediaPath = $contentBaseDir . '/' . $destFileName;
                if (!rename($originalFilePath, $finalMediaPath)) {
                    throw new \Exception("Falha ao mover arquivo de podcast.");
                }
                $mediaFilePath = $contentType . '/' . basename($contentBaseDir) . '/' . $destFileName;
                $status = 'completed';
                $jobLog .= "Processamento de podcast concluído com sucesso.\n";
                break;

            case 'pdf':
                // Para PDF, apenas move o arquivo original
                $destFileName = basename($originalFilePath);
                $finalMediaPath = $contentBaseDir . '/' . $destFileName;
                if (!rename($originalFilePath, $finalMediaPath)) {
                    throw new \Exception("Falha ao mover arquivo PDF.");
                }
                $mediaFilePath = $contentType . '/' . basename($contentBaseDir) . '/' . $destFileName;
                $status = 'completed';
                $jobLog .= "Processamento de PDF concluído com sucesso.\n";
                break;

            default:
                throw new \Exception("Tipo de conteúdo inválido para processamento: " . $contentType);
        }

        // Se o processamento foi um sucesso, insere o conteúdo na tabela `contents`
        if ($status === 'completed') {
            $contentId = $contentModel->create(
                $contentType,
                $title,
                $description,
                $mediaFilePath, 
                $hlsManifestPath, 
                $adminId
            );
            $jobLog .= "Conteúdo inserido no banco de dados com ID: " . $contentId . "\n";
        }
        
        // Remove o arquivo original da fila de uploads temporários
        if (file_exists($originalFilePath)) {
            unlink($originalFilePath);
            $jobLog .= "Arquivo original removido da fila: " . $originalFilePath . "\n";
        }

    } catch (\Exception $e) {
        $jobLog .= "[ERRO CRÍTICO] " . $e->getMessage() . "\n";
        $status = 'failed';
        error_log("Worker job ID " . $job['id'] . " failed: " . $e->getMessage());
    } finally {
        // Atualiza o status final do job e log
        $updateStmt = $pdo->prepare("UPDATE queue_jobs SET status = ?, finished_at = NOW(), log = CONCAT(IFNULL(log, ''), ?) WHERE id = ?");
        $updateStmt->execute([$status, $jobLog, $job['id']]);
    }
}

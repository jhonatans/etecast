#!/bin/bash

# Script OTIMIZADO para Raspberry Pi 5 - Transcoder HLS com FFmpeg
# Uso: ./transcode_video.sh <arquivo_origem> <pasta_destino>

INPUT_FILE="$1"
OUTPUT_DIR="$2"

# Verificar se os argumentos foram fornecidos
if [ -z "$INPUT_FILE" ] || [ -z "$OUTPUT_DIR" ]; then
    echo "Erro: Uso: $0 <arquivo_origem> <pasta_destino>"
    exit 1
fi

# Verificar se o arquivo de origem existe
if [ ! -f "$INPUT_FILE" ]; then
    echo "Erro: Arquivo de origem não encontrado: $INPUT_FILE"
    exit 1
fi

# Criar diretório de destino se não existir
mkdir -p "$OUTPUT_DIR"

# Verificar se FFmpeg está instalado
if ! command -v ffmpeg &> /dev/null; then
    echo "Erro: FFmpeg não está instalado"
    exit 1
fi

echo "Iniciando transcodificação OTIMIZADA para Raspberry Pi 5: $INPUT_FILE -> $OUTPUT_DIR"

# Detectar resolução original para ajustar bitrates
ORIGINAL_RESOLUTION=$(ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0 "$INPUT_FILE")
ORIGINAL_WIDTH=$(echo $ORIGINAL_RESOLUTION | cut -d',' -f1)
ORIGINAL_HEIGHT=$(echo $ORIGINAL_RESOLUTION | cut -d',' -f2)

echo "Resolução original: ${ORIGINAL_WIDTH}x${ORIGINAL_HEIGHT}"

# Ajustar bitrates baseado na resolução original
if [ $ORIGINAL_WIDTH -ge 1920 ]; then
    # 1080p ou maior
    BITRATE_HIGH="1500k"
    BITRATE_MED="800k" 
    BITRATE_LOW="400k"
    SCALE_HIGH="1280:720"
    SCALE_MED="854:480"
    SCALE_LOW="640:360"
elif [ $ORIGINAL_WIDTH -ge 1280 ]; then
    # 720p
    BITRATE_HIGH="1000k"
    BITRATE_MED="600k"
    BITRATE_LOW="300k"
    SCALE_HIGH="1280:720"
    SCALE_MED="854:480" 
    SCALE_LOW="640:360"
else
    # Menor que 720p
    BITRATE_HIGH="800k"
    BITRATE_MED="400k"
    BITRATE_LOW="200k"
    SCALE_HIGH="${ORIGINAL_WIDTH}:${ORIGINAL_HEIGHT}"
    SCALE_MED=$SCALE_HIGH
    SCALE_LOW=$SCALE_HIGH
fi

echo "Bitrates: High=$BITRATE_HIGH, Medium=$BITRATE_MED, Low=$BITRATE_LOW"

# Detectar se o arquivo tem áudio
HAS_AUDIO=$(ffprobe -i "$INPUT_FILE" -show_streams -select_streams a -loglevel error 2>&1 | grep -c "codec_type=audio")

# Configurações OTIMIZADAS para Raspberry Pi 5
FFMPEG_OPTIONS=(
    -i "$INPUT_FILE"
    -preset ultrafast           # Máxima velocidade, menos CPU
    -threads 2                  # Apenas 2 threads para não sobrecarregar
    -g 48
    -sc_threshold 0
    -max_muxing_queue_size 1024 # Evitar erros de muxing
)

if [ "$HAS_AUDIO" -eq 0 ]; then
    echo "AVISO: Arquivo não contém áudio. Gerando apenas streams de vídeo."
    
    # Comando OTIMIZADO para vídeo SEM áudio
    ffmpeg "${FFMPEG_OPTIONS[@]}" \
      -map 0:v:0 -map 0:v:0 -map 0:v:0 \
      -c:v libx264 \
      -b:v:0 $BITRATE_HIGH \
      -b:v:1 $BITRATE_MED \
      -b:v:2 $BITRATE_LOW \
      -var_stream_map "v:0 v:1 v:2" \
      -f hls \
      -hls_time 6 \
      -hls_playlist_type vod \
      -hls_segment_filename "$OUTPUT_DIR/segment_%v_%03d.ts" \
      -master_pl_name "stream.m3u8" \
      "$OUTPUT_DIR/stream_%v.m3u8"
else
    echo "Arquivo contém áudio. Gerando streams de vídeo e áudio."
    
    # Comando OTIMIZADO para vídeo COM áudio
    ffmpeg "${FFMPEG_OPTIONS[@]}" \
      -map 0:v:0 -map 0:a:0 \
      -map 0:v:0 -map 0:a:0 \
      -map 0:v:0 -map 0:a:0 \
      -c:v libx264 -c:a aac \
      -b:v:0 $BITRATE_HIGH -b:a:0 96k \
      -b:v:1 $BITRATE_MED -b:a:1 64k \
      -b:v:2 $BITRATE_LOW -b:a:2 48k \
      -var_stream_map "v:0,a:0 v:1,a:1 v:2,a:2" \
      -f hls \
      -hls_time 6 \
      -hls_playlist_type vod \
      -hls_segment_filename "$OUTPUT_DIR/segment_%v_%03d.ts" \
      -master_pl_name "stream.m3u8" \
      "$OUTPUT_DIR/stream_%v.m3u8"
fi

# Verificar se o comando foi bem-sucedido
if [ $? -eq 0 ] && [ -f "$OUTPUT_DIR/stream.m3u8" ]; then
    echo "✅ Transcodificação concluída com sucesso!"
    echo "📁 Arquivos gerados em: $OUTPUT_DIR"
    echo "📊 Tamanho dos arquivos:"
    ls -lh "$OUTPUT_DIR" | head -10
else
    echo "❌ Erro na transcodificação ou arquivo stream.m3u8 não foi gerado"
    exit 1
fi

#!/bin/bash

# INPUT_FILE="$1"
# OUTPUT_DIR="$2"
# LOG_FILE="/var/www/etecast/storage/logs/ffmpeg.log"

# echo "=== INICIANDO TRANSCODIFICAÇÃO SIMPLIFICADA ===" >> "$LOG_FILE"
# echo "Arquivo: $INPUT_FILE" >> "$LOG_FILE"
# echo "Saída: $OUTPUT_DIR" >> "$LOG_FILE"

# # Criar diretório de saída
# mkdir -p "$OUTPUT_DIR"

# # Verificar se arquivo existe
# if [ ! -f "$INPUT_FILE" ]; then
#     echo "❌ ERRO: Arquivo não encontrado: $INPUT_FILE" >> "$LOG_FILE"
#     exit 1
# fi

# # Obter informações básicas
# RESOLUTION=$(ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 "$INPUT_FILE" 2>/dev/null || echo "unknown")
# HAS_AUDIO=$(ffprobe -v error -select_streams a:0 -show_entries stream=codec_type -of csv=p=0 "$INPUT_FILE" 2>/dev/null || echo "")

# echo "Resolução: $RESOLUTION" >> "$LOG_FILE"
# echo "Tem áudio: $([ -n "$HAS_AUDIO" ] && echo "SIM" || echo "NÃO")" >> "$LOG_FILE"

# # COMANDO FFMPEG SIMPLIFICADO E FUNCIONAL
# if [ -n "$HAS_AUDIO" ]; then
#     # COM ÁUDIO - VERSÃO SIMPLIFICADA
#     echo "Executando transcodificação COM áudio..." >> "$LOG_FILE"
    
#     ffmpeg -i "$INPUT_FILE" \
#         -preset veryfast \
#         -g 48 \
#         -sc_threshold 0 \
#         \
#         -map 0:v:0 -map 0:a:0 \
#         -c:v libx264 -c:a aac \
#         -b:v:0 1500k -b:a:0 128k \
#         -vf "scale=1280:720:force_original_aspect_ratio=decrease" \
#         \
#         -map 0:v:0 -map 0:a:0 \
#         -c:v libx264 -c:a aac \
#         -b:v:1 800k -b:a:1 96k \
#         -vf "scale=854:480:force_original_aspect_ratio=decrease" \
#         \
#         -map 0:v:0 -map 0:a:0 \
#         -c:v libx264 -c:a aac \
#         -b:v:2 400k -b:a:2 64k \
#         -vf "scale=640:360:force_original_aspect_ratio=decrease" \
#         \
#         -var_stream_map "v:0,a:0 v:1,a:1 v:2,a:2" \
#         -f hls \
#         -hls_time 6 \
#         -hls_playlist_type vod \
#         -hls_segment_filename "$OUTPUT_DIR/segment_%v_%03d.ts" \
#         -master_pl_name "stream.m3u8" \
#         -y \
#         "$OUTPUT_DIR/stream_%v.m3u8" >> "$LOG_FILE" 2>&1

# else
#     # SEM ÁUDIO - VERSÃO SIMPLIFICADA
#     echo "Executando transcodificação SEM áudio..." >> "$LOG_FILE"
    
#     ffmpeg -i "$INPUT_FILE" \
#         -preset veryfast \
#         -g 48 \
#         -sc_threshold 0 \
#         \
#         -map 0:v:0 \
#         -c:v libx264 \
#         -b:v:0 1500k \
#         -vf "scale=1280:720:force_original_aspect_ratio=decrease" \
#         \
#         -map 0:v:0 \
#         -c:v libx264 \
#         -b:v:1 800k \
#         -vf "scale=854:480:force_original_aspect_ratio=decrease" \
#         \
#         -map 0:v:0 \
#         -c:v libx264 \
#         -b:v:2 400k \
#         -vf "scale=640:360:force_original_aspect_ratio=decrease" \
#         \
#         -var_stream_map "v:0 v:1 v:2" \
#         -f hls \
#         -hls_time 6 \
#         -hls_playlist_type vod \
#         -hls_segment_filename "$OUTPUT_DIR/segment_%v_%03d.ts" \
#         -master_pl_name "stream.m3u8" \
#         -y \
#         "$OUTPUT_DIR/stream_%v.m3u8" >> "$LOG_FILE" 2>&1
# fi

# # Verificar resultado
# if [ $? -eq 0 ] && [ -f "$OUTPUT_DIR/stream.m3u8" ]; then
#     echo "✅ TRANSCODIFICAÇÃO CONCLUÍDA COM SUCESSO!" >> "$LOG_FILE"
#     echo "Arquivos gerados:" >> "$LOG_FILE"
#     ls -la "$OUTPUT_DIR/" >> "$LOG_FILE"
#     exit 0
# else
#     echo "❌ FALHA NA TRANSCODIFICAÇÃO" >> "$LOG_FILE"
#     echo "Últimas linhas do log FFmpeg:" >> "$LOG_FILE"
#     tail -20 "$LOG_FILE" >> "$LOG_FILE"
#     exit 1
# fi














# #!/bin/bash
# # Transcoder HLS otimizado para Raspberry Pi 5
# # Uso: ./transcode_video.sh <arquivo_origem> <pasta_destino>

# INPUT_FILE="$1"
# OUTPUT_DIR="$2"
# LOG_FILE="/var/www/etecast/storage/logs/ffmpeg.log"

# echo "=== TRANSCODIFICAÇÃO INICIADA ===" >> "$LOG_FILE"
# echo "Arquivo: $INPUT_FILE" >> "$LOG_FILE"
# echo "Destino: $OUTPUT_DIR" >> "$LOG_FILE"

# mkdir -p "$OUTPUT_DIR"

# if [ ! -f "$INPUT_FILE" ]; then
#     echo "❌ ERRO: Arquivo não encontrado: $INPUT_FILE" >> "$LOG_FILE"
#     exit 1
# fi

# # Detectar presença de áudio
# HAS_AUDIO=$(ffprobe -v error -select_streams a -show_entries stream=codec_type -of csv=p=0 "$INPUT_FILE")

# # Configurações básicas
# HLS_TIME=6
# THREADS=2
# SEGMENT_PATTERN="$OUTPUT_DIR/segment_%v_%03d.ts"

# ENCODER="libx264"
# HW_FLAGS=""
# echo "⚙️ Codificação por software (libx264) ativada — VAAPI desabilitado" >> "$LOG_FILE"


# # Executar FFmpeg
# if [ -n "$HAS_AUDIO" ]; then
#     echo "🎵 Transcodificando COM áudio..." >> "$LOG_FILE"
#     ffmpeg -y -i "$INPUT_FILE" \
#         -threads $THREADS -preset veryfast -g 48 -sc_threshold 0 -movflags +faststart -avoid_negative_ts 1 \
#         $HW_FLAGS \
#         -map 0:v:0 -map 0:a:0 -map 0:v:0 -map 0:a:0 -map 0:v:0 -map 0:a:0 \
#         -c:v $ENCODER -c:a aac -b:a:0 128k -b:a:1 96k -b:a:2 64k \
#         -b:v:0 1500k -b:v:1 800k -b:v:2 400k \
#         -filter_complex \
#         "[0:v]split=3[v1][v2][v3]; \
#          [v1]scale=1280:720:force_original_aspect_ratio=decrease[v1out]; \
#          [v2]scale=854:480:force_original_aspect_ratio=decrease[v2out]; \
#          [v3]scale=640:360:force_original_aspect_ratio=decrease[v3out]" \
#         -map "[v1out]" -map 0:a:0 \
#         -map "[v2out]" -map 0:a:0 \
#         -map "[v3out]" -map 0:a:0 \
#         -var_stream_map "v:0,a:0 v:1,a:1 v:2,a:2" \
#         -f hls -hls_time $HLS_TIME -hls_playlist_type vod \
#         -hls_segment_filename "$SEGMENT_PATTERN" \
#         -master_pl_name "stream.m3u8" \
#         "$OUTPUT_DIR/stream_%v.m3u8" >> "$LOG_FILE" 2>&1
# else
#     echo "🔇 Transcodificando SEM áudio..." >> "$LOG_FILE"
#     ffmpeg -y -i "$INPUT_FILE" \
#         -threads $THREADS -preset veryfast -g 48 -sc_threshold 0 -movflags +faststart -avoid_negative_ts 1 \
#         $HW_FLAGS \
#         -filter_complex \
#         "[0:v]split=3[v1][v2][v3]; \
#          [v1]scale=1280:720:force_original_aspect_ratio=decrease[v1out]; \
#          [v2]scale=854:480:force_original_aspect_ratio=decrease[v2out]; \
#          [v3]scale=640:360:force_original_aspect_ratio=decrease[v3out]" \
#         -map "[v1out]" -map "[v2out]" -map "[v3out]" \
#         -c:v $ENCODER -b:v:0 1500k -b:v:1 800k -b:v:2 400k \
#         -var_stream_map "v:0 v:1 v:2" \
#         -f hls -hls_time $HLS_TIME -hls_playlist_type vod \
#         -hls_segment_filename "$SEGMENT_PATTERN" \
#         -master_pl_name "stream.m3u8" \
#         "$OUTPUT_DIR/stream_%v.m3u8" >> "$LOG_FILE" 2>&1
# fi

# # Validação final
# if [ $? -eq 0 ] && [ -f "$OUTPUT_DIR/stream.m3u8" ]; then
#     echo "✅ Transcodificação concluída com sucesso!" >> "$LOG_FILE"
#     ls -lh "$OUTPUT_DIR" >> "$LOG_FILE"
# else
#     echo "❌ Erro durante a transcodificação" >> "$LOG_FILE"
#     tail -20 "$LOG_FILE" >> "$LOG_FILE"
# fi

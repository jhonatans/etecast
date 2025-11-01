1. Estrutura de Diretórios do Projeto

etecast/
│
├── app/
│   ├── controllers/
│   │   ├── AuthController.php       # (Login, logout, registro de senha do aluno)
│   │   ├── AdminAuthController.php  # (Login/logout do painel admin)
│   │   ├── StudentController.php    # (Catálogo, player, histórico)
│   │   ├── AdminController.php      # (Dashboard, CRUD Alunos, Logs)
│   │   ├── ContentController.php    # (Upload, gerenciamento de mídia - Admin)
│   │   └── MediaController.php      # (Endpoint de streaming seguro com token)
│   │
│   ├── models/
│   │   ├── Student.php
│   │   ├── Admin.php
│   │   ├── Content.php
│   │   ├── AccessLog.php
│   │   └── Token.php                # (Lógica de geração/validação de tokens)
│   │
│   └── views/
│       ├── student/
│       │   ├── login.php            # (Form: matrícula + data de nascimento)
│       │   ├── register_password.php # (Form: primeiro acesso, criar senha)
│       │   ├── catalog.php
│       │   └── player.php           # (Container do HLS.js ou PDF.js)
│       │
│       ├── admin/
│       │   ├── login.php
│       │   ├── dashboard.php
│       │   ├── upload_form.php
│       │   └── student_list.php
│       │
│       └── layouts/                 # (Templates: header, footer, nav)
│           ├── header.php
│           └── footer.php
│
├── config/
│   ├── database.php             # (Conexão PDO)
│   ├── app.php                  # (Chave secreta do token, config de Redis)
│   └── ffmpeg.php               # (Paths e presets do FFmpeg)
│
├── public/                      # <-- NGINX WEB ROOT
│   ├── index.php                # (Front Controller / Roteador Principal)
│   ├── assets/
│   │   ├── css/                 # (Bootstrap/Tailwind)
│   │   ├── js/                  # (hls.js, pdf.js, app.js)
│   │   └── img/
│   └── .htaccess                # (Redirecionamento para index.php em Apache - dev)
│
├── media_protected/             # <-- MÍDIA REAL (FORA DO WEB ROOT)
│   ├── video/                   # (Ex: 123/stream.m3u8, 123/stream_0_data01.ts)
│   ├── podcast/                 # (Ex: 456/audio_podcast.mp3)
│   └── pdf/                     # (Ex: 789/livro_capitulo_1.pdf)
│
├── storage/
│   ├── logs/
│   │   ├── app.log              # (Logs gerais da aplicação)
│   │   ├── ffmpeg.log           # (Logs de transcodificação)
│   │   └── cron.log             # (Logs dos scripts agendados)
│   │
│   ├── cache/                   # (Cache de sessão Redis ou arquivos)
│   ├── uploads_queue/           # (Uploads temporários aguardando transcodificação)
│   └── backups/                 # (Destino do backup.sh, idealmente /mnt/ssd/backup)
│
├── scripts/
│   ├── transcode_video.sh       # (Script FFmpeg HLS multibitrate)
│   ├── backup.sh                # (Dump do DB + rsync da mídia)
│   └── worker.php               # (Script da fila de transcodificação, a ser rodado pelo Supervisor)
│
├── vendor/                      # (Dependências do Composer, ex: roteador)
├── composer.json
└── README.md
<?php

return [
    'max_attempts' => 3,
    'timeout' => 7200, // 2 horas
    'output_dir' => '/var/www/etecast/media_protected/video',
    'log_file' => '/var/www/etecast/logs/worker.log',
    'ffmpeg_threads' => 0, // auto
    'ffmpeg_preset' => 'medium'
];
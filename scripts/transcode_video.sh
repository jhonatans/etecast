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
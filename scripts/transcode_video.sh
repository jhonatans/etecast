#!/bin/bash

# Script para transcodificar vídeos para HLS usando FFmpeg
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

echo "Iniciando transcodificação: $INPUT_FILE -> $OUTPUT_DIR"

# Detectar se o arquivo tem áudio
HAS_AUDIO=$(ffprobe -i "$INPUT_FILE" -show_streams -select_streams a -loglevel error 2>&1 | grep -c "codec_type=audio")

if [ "$HAS_AUDIO" -eq 0 ]; then
    echo "AVISO: Arquivo não contém áudio. Gerando apenas streams de vídeo."
    
    # Comando para vídeo SEM áudio
    ffmpeg -i "$INPUT_FILE" \
      -preset medium \
      -g 48 \
      -sc_threshold 0 \
      -map 0:v:0 \
      -map 0:v:0 \
      -map 0:v:0 \
      -c:v libx264 \
      -b:v:0 2000k \
      -b:v:1 1000k \
      -b:v:2 500k \
      -var_stream_map "v:0 v:1 v:2" \
      -f hls \
      -hls_time 6 \
      -hls_playlist_type vod \
      -hls_segment_filename "$OUTPUT_DIR/segment_%v_%03d.ts" \
      -master_pl_name "stream.m3u8" \
      "$OUTPUT_DIR/stream_%v.m3u8"
else
    echo "Arquivo contém áudio. Gerando streams de vídeo e áudio."
    
    # Comando para vídeo COM áudio
    ffmpeg -i "$INPUT_FILE" \
      -preset medium \
      -g 48 \
      -sc_threshold 0 \
      -map 0:v:0 -map 0:a:0 \
      -map 0:v:0 -map 0:a:0 \
      -map 0:v:0 -map 0:a:0 \
      -c:v libx264 -c:a aac \
      -b:v:0 2000k -b:a:0 128k \
      -b:v:1 1000k -b:a:1 96k \
      -b:v:2 500k -b:a:2 64k \
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
    echo "Transcodificação concluída com sucesso!"
    echo "Arquivos gerados em: $OUTPUT_DIR"
    ls -la "$OUTPUT_DIR"
else
    echo "Erro na transcodificação ou arquivo stream.m3u8 não foi gerado"
    exit 1
fi

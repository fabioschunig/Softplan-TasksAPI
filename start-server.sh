#!/bin/bash

echo "🚀 Iniciando servidor PHP na porta 8000..."
echo "📁 Servindo arquivos do diretório: public/"
echo "🌐 API disponível em: http://localhost:8000"
echo ""
echo "Pressione Ctrl+C para parar o servidor"
echo "----------------------------------------"

cd public && php -S localhost:8000

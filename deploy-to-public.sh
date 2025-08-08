#!/bin/bash

# Script para fazer deploy da aplicação React para public/app/
# Execute este script na raiz do projeto

echo "🚀 Iniciando deploy para public/app/..."

# Verificar se estamos na raiz do projeto
if [ ! -d "react-app" ] || [ ! -d "public" ]; then
    echo "❌ Erro: Execute este script na raiz do projeto Softplan-TasksAPI"
    exit 1
fi

# Entrar na pasta react-app
cd react-app

echo "📦 Instalando dependências do React..."
npm install

echo "🔧 Configurando .env para produção..."
# Criar .env para produção se não existir
if [ ! -f ".env" ]; then
    echo "REACT_APP_API_BASE_URL=" > .env
    echo "REACT_APP_ENV=production" >> .env
    echo "✓ Arquivo .env criado"
else
    echo "✓ Arquivo .env já existe"
fi

echo "🏗️ Fazendo build da aplicação React..."
npm run build

# Verificar se o build foi bem-sucedido
if [ ! -d "build" ]; then
    echo "❌ Erro: Build falhou. Pasta 'build' não encontrada."
    exit 1
fi

echo "📁 Criando estrutura no public/..."
cd ..
mkdir -p public/app

echo "🔄 Copiando arquivos do build para public/app/..."
cp -r react-app/build/* public/app/

echo "🔍 Verificando arquivos copiados..."
if [ -f "public/app/index.html" ]; then
    echo "✓ index.html copiado"
else
    echo "❌ Erro: index.html não encontrado"
    exit 1
fi

if [ -d "public/app/static" ]; then
    echo "✓ Pasta static copiada"
else
    echo "❌ Erro: Pasta static não encontrada"
    exit 1
fi

echo ""
echo "✅ DEPLOY CONCLUÍDO COM SUCESSO!"
echo ""
echo "📋 Estrutura final:"
echo "public/"
echo "├── index.php (serve o React)"
echo "├── app/"
echo "│   ├── index.html"
echo "│   ├── static/"
echo "│   └── ..."
echo "├── auth.api.php ✓"
echo "├── task.api.php ✓"
echo "├── project.api.php ✓"
echo "└── user.api.php ✓"
echo ""
echo "🌐 Agora você pode:"
echo "1. Fazer upload da pasta 'public/' para a raiz do seu domínio na HostGator"
echo "2. Acessar https://seudominio.com → React App"
echo "3. APIs estarão em https://seudominio.com/auth.api.php etc."
echo ""
echo "🔧 Não esqueça de:"
echo "- Configurar o arquivo config/.env com dados do banco HostGator"
echo "- Fazer upload das pastas src/, vendor/, config/"
echo ""

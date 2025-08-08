<?php
/**
 * Entry point para servir a aplicação React
 * Este arquivo será executado quando acessar a raiz do domínio
 */

// Verificar se é uma requisição para API
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Se a requisição é para uma API, não interceptar
if (strpos($requestUri, '.api.php') !== false) {
    return false; // Deixa o servidor processar normalmente
}

// Servir arquivos estáticos do React (CSS, JS, imagens)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $requestUri)) {
    $filePath = __DIR__ . '/app' . parse_url($requestUri, PHP_URL_PATH);
    if (file_exists($filePath)) {
        // Definir Content-Type correto
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($filePath);
        exit;
    }
}

// Para todas as outras rotas, servir o index.html do React
$indexPath = __DIR__ . '/app/index.html';

if (file_exists($indexPath)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($indexPath);
} else {
    // Se não encontrar o React, mostrar mensagem de instrução
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Softplan Tasks API</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error { color: #e74c3c; }
            .success { color: #27ae60; }
            .code { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 Softplan Tasks API</h1>
            <p class="error">⚠️ Aplicação React não encontrada!</p>
            
            <h3>📋 Para completar o setup:</h3>
            <ol>
                <li>Copie os arquivos do <strong>react-app/build/</strong> para <strong>public/app/</strong></li>
                <li>Configure o arquivo <strong>react-app/.env</strong>:</li>
            </ol>
            
            <div class="code">
REACT_APP_API_BASE_URL=<br>
REACT_APP_ENV=production
            </div>
            
            <h3>🔧 APIs Disponíveis:</h3>
            <ul>
                <li><a href="/auth.api.php?action=validate">/auth.api.php</a> - Autenticação</li>
                <li><a href="/task.api.php">/task.api.php</a> - Tarefas</li>
                <li><a href="/project.api.php">/project.api.php</a> - Projetos</li>
                <li><a href="/user.api.php">/user.api.php</a> - Usuários</li>
            </ul>
            
            <p class="success">✅ Backend PHP funcionando!</p>
        </div>
    </body>
    </html>
    <?php
}
?>
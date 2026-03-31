<?php
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Racine → index.html
if ($uri === '' || $uri === 'index') {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/index.html');
    exit;
}

// Fichier physique existant (css, images, js, php, pdf...) → servir directement
$file = __DIR__ . '/' . $uri;
if (file_exists($file) && !is_dir($file)) {
    return false;
}

// Chercher dossier/index.html  (ex: detail-teleport/index.html)
$folder_index = __DIR__ . '/' . $uri . '/index.html';
if (file_exists($folder_index)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($folder_index);
    exit;
}

// Chercher dossier/index.php
$folder_php = __DIR__ . '/' . $uri . '/index.php';
if (file_exists($folder_php)) {
    include $folder_php;
    exit;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title></head>
<body style="background:#020c1b;color:#00f2ff;font-family:monospace;text-align:center;padding:100px;">
<h1>404 — PAGE_NOT_FOUND</h1>
<p style="color:#e0ffff;">La page <code style="color:#ffc107;">/' . htmlspecialchars($uri) . '</code> est introuvable.</p>
<a href="/" style="color:#00f2ff;">&larr; Retour accueil</a>
</body></html>';

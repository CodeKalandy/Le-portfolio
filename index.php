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

// ── PONT IMAGES : projets/images/xxx → images/xxx (racine) ──
if (preg_match('#^projets/images/(.+)$#', $uri, $m)) {
    $real = __DIR__ . '/images/' . $m[1];
    if (file_exists($real)) {
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $types = ['png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg',
                  'gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml'];
        if (isset($types[$ext])) {
            header('Content-Type: ' . $types[$ext]);
        }
        readfile($real);
        exit;
    }
}

// ── PONT FICHIERS : projets/fichiers/xxx → fichiers/xxx (racine) ──
if (preg_match('#^projets/fichiers/(.+)$#', $uri, $m)) {
    $real = __DIR__ . '/fichiers/' . $m[1];
    if (file_exists($real)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: inline; filename="' . basename($real) . '"');
        readfile($real);
        exit;
    }
}

// Chercher dossier/index.html  (ex: projets/ocs/index.html)
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
<h1>404 - PAGE_NOT_FOUND</h1>
<p style="color:#e0ffff;">La page <code style="color:#ffc107;">/' . htmlspecialchars($uri) . '</code> est introuvable.</p>
<a href="/" style="color:#00f2ff;">&larr; Retour accueil</a>
</body></html>';

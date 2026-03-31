<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('RESULTS_FILE', __DIR__ . '/quiz_data/results.json');
define('SECRET_KEY', 'b3rn4rd_sisr_2026_secret');

// Vérification de la clé secrète
$key = $_GET['key'] ?? '';
if ($key !== SECRET_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Action : purge
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    if (file_exists(RESULTS_FILE)) {
        file_put_contents(RESULTS_FILE, json_encode([]));
    }
    echo json_encode(['success' => true, 'cleared' => true]);
    exit;
}

// Lire les résultats
if (!file_exists(RESULTS_FILE)) {
    echo json_encode([]);
    exit;
}

$results = json_decode(file_get_contents(RESULTS_FILE), true) ?: [];

// Trier par score décroissant
usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

echo json_encode($results);

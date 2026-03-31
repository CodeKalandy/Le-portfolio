<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('SECRET_KEY',   'b3rn4rd_sisr_2026_secret');
define('DATA_DIR',     '/home/thiery/www/quiz_data');
define('RESULTS_FILE', '/home/thiery/www/quiz_data/results.json');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (
    empty($input['name'])   ||
    !isset($input['score']) ||
    !isset($input['pct'])   ||
    empty($input['time'])   ||
    empty($input['key'])    ||
    $input['key'] !== SECRET_KEY
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$results = [];
if (file_exists(RESULTS_FILE)) {
    $results = json_decode(file_get_contents(RESULTS_FILE), true) ?: [];
}

// ══ Le champ quiz est OBLIGATOIRE ══
$results[] = [
    'name'  => htmlspecialchars(strip_tags(trim($input['name'])), ENT_QUOTES, 'UTF-8'),
    'score' => (int) $input['score'],
    'pct'   => (int) $input['pct'],
    'time'  => htmlspecialchars($input['time'], ENT_QUOTES, 'UTF-8'),
    'quiz'  => htmlspecialchars($input['quiz'] ?? 'unknown', ENT_QUOTES, 'UTF-8'),
    'date'  => date('d/m/Y H:i'),
    'id'    => uniqid('r_', true),
];

if (file_put_contents(RESULTS_FILE, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'count' => count($results)]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Write failed — check permissions on quiz_data/']);
}

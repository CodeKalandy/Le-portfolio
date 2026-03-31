<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ══════════════════════════════════════════
// MOT DE PASSE ADMIN — modifie uniquement ici
define('ADMIN_PWD', 'BernardAdmin2026!');
// ══════════════════════════════════════════

// Liste fixe des quiz disponibles (indépendant des résultats)
define('QUIZ_IDS', ['arista','linux','reseaux','cybersecurite','virtualisation','active-directory','scripting-bash','sauvegarde-pra','glpi-support']);

define('RESULTS_FILE', '/home/thiery/www/quiz_data/results.json');
define('SESSION_FILE', '/home/thiery/www/quiz_data/sessions.json');
define('DATA_DIR',     '/home/thiery/www/quiz_data');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// ── LOGIN ──────────────────────────────────────────────────────
if ($action === 'login') {
    if (($input['password'] ?? '') === ADMIN_PWD) {
        $token    = bin2hex(random_bytes(32));
        $sessions = file_exists(SESSION_FILE) ? json_decode(file_get_contents(SESSION_FILE), true) : [];
        $sessions = array_filter($sessions, fn($s) => $s['expires'] > time());
        $sessions[$token] = ['expires' => time() + 3600];
        file_put_contents(SESSION_FILE, json_encode($sessions));
        echo json_encode(['success' => true, 'token' => $token]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid password']);
    }
    exit;
}

// ── Vérifier le token ──────────────────────────────────────────
$token    = $input['token'] ?? '';
$sessions = file_exists(SESSION_FILE) ? json_decode(file_get_contents(SESSION_FILE), true) : [];
if (empty($token) || !isset($sessions[$token]) || $sessions[$token]['expires'] < time()) {
    http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit;
}

// ── GET QUIZZES — liste fixe + stats par quiz ──────────────────
if ($action === 'get_quizzes') {
    $results = file_exists(RESULTS_FILE) ? (json_decode(file_get_contents(RESULTS_FILE), true) ?: []) : [];

    $output = [];
    foreach (QUIZ_IDS as $qid) {
        $filtered = array_values(array_filter($results, fn($r) => ($r['quiz'] ?? '') === $qid));
        $output[] = [
            'id'    => $qid,
            'count' => count($filtered),
            'avg'   => count($filtered) > 0 ? round(array_sum(array_column($filtered, 'score')) / count($filtered), 1) : null,
        ];
    }
    echo json_encode($output);
    exit;
}

// ── GET RESULTS ────────────────────────────────────────────────
if ($action === 'get_results') {
    if (!file_exists(RESULTS_FILE)) { echo json_encode([]); exit; }
    $results    = json_decode(file_get_contents(RESULTS_FILE), true) ?: [];
    $quizFilter = $input['quiz'] ?? 'all';
    if ($quizFilter !== 'all') {
        $results = array_values(array_filter($results, fn($r) => ($r['quiz'] ?? '') === $quizFilter));
    }
    usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
    echo json_encode($results);
    exit;
}

// ── CLEAR ──────────────────────────────────────────────────────
if ($action === 'clear') {
    $quizFilter = $input['quiz'] ?? 'all';
    if ($quizFilter === 'all') {
        file_put_contents(RESULTS_FILE, json_encode([]));
    } else {
        $results = file_exists(RESULTS_FILE) ? json_decode(file_get_contents(RESULTS_FILE), true) : [];
        $results = array_values(array_filter($results, fn($r) => ($r['quiz'] ?? '') !== $quizFilter));
        file_put_contents(RESULTS_FILE, json_encode($results));
    }
    echo json_encode(['success' => true]);
    exit;
}

// ── PURGE UNKNOWN ─────────────────────────────────────────────
if ($action === 'purge_unknown') {
    $results = file_exists(RESULTS_FILE) ? json_decode(file_get_contents(RESULTS_FILE), true) : [];
    $results = array_values(array_filter($results, fn($r) => !empty($r['quiz']) && $r['quiz'] !== 'unknown'));
    file_put_contents(RESULTS_FILE, json_encode($results));
    echo json_encode(['success' => true, 'remaining' => count($results)]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);

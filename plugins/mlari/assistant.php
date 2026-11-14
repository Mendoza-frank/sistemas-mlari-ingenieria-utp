<?php
include_once '../../include/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
if (!api_plugin_user_realm_auth('assistant.php')) { http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); echo json_encode(['error'=>'Método no permitido']); exit; }
if (!csrf_check_tokens($_POST['__csrf_magic'] ?? '')) { http_response_code(403); echo json_encode(['error'=>'Token de sesión no válido']); exit; }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) { http_response_code(413); echo json_encode(['error'=>'Solicitud demasiado extensa']); exit; }
if (!isset($_SESSION['mlari_ai']) || !is_array($_SESSION['mlari_ai']) ||
    ($_SESSION['mlari_ai']['schema'] ?? null) !== 2 ||
    ($_SESSION['mlari_ai']['user_id'] ?? null) !== ($_SESSION['sess_user_id'] ?? null)) {
    $_SESSION['mlari_ai'] = ['schema'=>2, 'user_id'=>$_SESSION['sess_user_id'] ?? null, 'times'=>[], 'history'=>[]];
}
$state = &$_SESSION['mlari_ai'];
if (($_POST['action'] ?? '') === 'history') {
    echo json_encode(['history'=>array_slice($state['history'], -8)], JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$question = trim((string)($_POST['message'] ?? ''));
if ($question === '' || mb_strlen($question) > 1000) { http_response_code(400); echo json_encode(['error'=>'Escribe un mensaje de hasta 1000 caracteres']); exit; }
require_once __DIR__.'/lib/assistant.php';
$state['times'] = array_values(array_filter($state['times'], static fn($t)=>is_int($t) && $t > time()-60));
if (count($state['times']) >= 4) { http_response_code(429); echo json_encode(['error'=>'Espera un minuto antes de otra consulta.']); exit; }
$state['times'][] = time();
try {
    $context = mlari_ai_context($question);
    $result = mlari_ai_generate($question, $context, $state['history']);
    if (isset($result['error'])) { http_response_code(503); echo json_encode($result, JSON_UNESCAPED_UNICODE); exit; }
    $state['history'][] = ['role'=>'user','text'=>$question];
    $state['history'][] = ['role'=>'model','text'=>$result['answer']];
    $state['history'] = array_slice($state['history'], -8);
    echo json_encode($result + ['context_time'=>$context['observed_at'], 'devices_consulted'=>count($context['devices'])], JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'No se pudo completar la consulta.'], JSON_UNESCAPED_UNICODE);
}

<?php
include_once '../../include/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!api_plugin_user_realm_auth('api.php')) { http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit; }
require_once __DIR__.'/lib/data.php';
$action=$_GET['action'] ?? 'snapshot';
try {
    if ($action==='snapshot') $data=mlari_snapshot();
    elseif ($action==='device') {
        $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
        if (!$id || $id<1) throw new InvalidArgumentException('ID no válido');
        $data=mlari_host_detail($id);
        if (!$data) { http_response_code(404); $data=['error'=>'Dispositivo no encontrado']; }
    } elseif ($action==='report') {
        $rows=mlari_hosts();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mlari-dispositivos.csv"');
        $out=fopen('php://output','w'); fputcsv($out,['ID MLARI','Nombre','IP','Estado','Último sondeo','Disponibilidad %','Sondeos','Fallos']);
        foreach ($rows as $h) fputcsv($out,[$h['id'],$h['name'],$h['hostname'],$h['state'],$h['last_updated'],$h['availability_percent'],$h['total_polls'],$h['failed_polls']]);
        fclose($out); exit;
    } else { http_response_code(404); $data=['error'=>'Acción desconocida']; }
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['error'=>'No se pudo consultar MLARI']); }

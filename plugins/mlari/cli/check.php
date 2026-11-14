<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
session_save_path(sys_get_temp_dir());
chdir(dirname(__DIR__, 3));
require 'include/global.php';
require 'plugins/mlari/lib/data.php';
$s=mlari_snapshot();
$r8=mlari_host_detail(2);
echo json_encode([
    'nodes'=>count($s['topology']['nodes']),
    'links'=>count($s['topology']['links']),
    'cacti_hosts'=>count($s['hosts']),
    'host_states'=>array_map(fn($h)=>[$h['id'],$h['state']],$s['hosts']),
    'r8_state'=>$r8['host']['state'] ?? null,
    'r8_last_updated'=>$r8['host']['last_updated'] ?? null,
    'r8_graphs'=>count($r8['graphs'] ?? []),
    'r8_interfaces'=>count($r8['interfaces'] ?? []),
    'poller_last_update'=>$s['poller']['last_update'] ?? null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;

<?php
require_once __DIR__ . '/topology.php';

function mlari_hosts() {
    $rows = db_fetch_assoc('SELECT id, description, hostname, host_template_id, disabled, status, availability_method, snmp_version, snmp_sysDescr, snmp_sysUpTimeInstance, status_fail_date, status_rec_date, status_last_error, cur_time, avg_time, total_polls, failed_polls, availability, last_updated, TIMESTAMPDIFF(SECOND,last_updated,NOW()) AS age_seconds FROM host WHERE deleted = "" ORDER BY id');
    $result=[];
    foreach ($rows as $h) {
        $age = isset($h['age_seconds']) ? max(0,(int)$h['age_seconds']) : null;
        $state = $h['disabled']==='on' ? 'disabled' : ($age===null || $age>600 ? 'stale' : [0=>'unknown',1=>'down',2=>'recovering',3=>'up'][(int)$h['status']] ?? 'unknown');
        $result[]=[
            'id'=>(int)$h['id'],'name'=>$h['description'],'hostname'=>$h['hostname'],
            'template_id'=>(int)$h['host_template_id'],'state'=>$state,'cacti_status'=>(int)$h['status'],
            'last_updated'=>$h['last_updated'],'age_seconds'=>$age,'availability_method'=>(int)$h['availability_method'],
            'snmp_version'=>(int)$h['snmp_version'],'sys_descr'=>$h['snmp_sysDescr'] ?: null,
            'sys_uptime'=>$h['snmp_sysUpTimeInstance'] ?: null,'fail_date'=>$h['status_fail_date'] ?: null,
            'recovery_date'=>$h['status_rec_date'] ?: null,'last_error'=>$h['status_last_error'] ?: null,
            'current_response_ms'=>is_numeric($h['cur_time']) ? (float)$h['cur_time'] : null,
            'average_response_ms'=>is_numeric($h['avg_time']) ? (float)$h['avg_time'] : null,
            'total_polls'=>(int)$h['total_polls'],'failed_polls'=>(int)$h['failed_polls'],
            'availability_percent'=>is_numeric($h['availability']) ? (float)$h['availability'] : null
        ];
    }
    return $result;
}
function mlari_snapshot() {
    $top=mlari_topology(); $hosts=mlari_hosts(); $byId=[];
    foreach ($hosts as $h) $byId[$h['id']]=$h;
    foreach ($top['nodes'] as &$n) {
        $h=null;
        if (isset($n['cacti_id'])) $h=$byId[$n['cacti_id']] ?? null;
        if (!$h && isset($n['ip'])) foreach ($hosts as $candidate) if ($candidate['hostname']===$n['ip']) { $h=$candidate; break; }
        $n['host']=$h;
        $n['state']=$h ? $h['state'] : 'unverified';
        $n['proposed_ip']=$n['ip'] ?? null;
        $n['registered_ip']=$h ? $h['hostname'] : null;
        $n['verified_ip']=$h && in_array($h['state'],['up','recovering'],true) ? $h['hostname'] : null;
        unset($n['ip']);
    }
    unset($n);
    $poller=db_fetch_row('SELECT id,status,last_update,total_polls FROM poller WHERE id=1');
    $counts=[]; foreach ($hosts as $h) $counts[$h['state']]=($counts[$h['state']]??0)+1;
    return ['topology'=>$top,'hosts'=>$hosts,'counts'=>$counts,'poller'=>$poller ?: null,'generated_at'=>date('c'),
        'note'=>'Los enlaces permanecen sin verificar hasta disponer de estados de interfaz actuales y medidos.'];
}
function mlari_graphs($hostId) {
    $rows=db_fetch_assoc_prepared('SELECT gl.id, gt.name AS template_name, gtg.title_cache FROM graph_local gl LEFT JOIN graph_templates gt ON gt.id=gl.graph_template_id LEFT JOIN graph_templates_graph gtg ON gtg.local_graph_id=gl.id WHERE gl.host_id=? ORDER BY gl.id',[$hostId]);
    $out=[]; foreach ($rows as $g) $out[]=['id'=>(int)$g['id'],'title'=>$g['title_cache'] ?: $g['template_name'],'template'=>$g['template_name'], 'suspect'=>stripos(($g['title_cache']??'').' '.($g['template_name']??''),'ACME')!==false];
    return $out;
}
function mlari_interfaces($hostId) {
    $rows=db_fetch_assoc_prepared('SELECT snmp_index,field_name,field_value,last_updated,TIMESTAMPDIFF(SECOND,last_updated,NOW()) AS age_seconds FROM host_snmp_cache WHERE host_id=? AND field_name IN ("ifName","ifDescr","ifAlias","ifAdminStatus","ifOperStatus","ifSpeed","ifHighSpeed") ORDER BY snmp_index',[$hostId]);
    $out=[]; foreach ($rows as $r) { $i=$r['snmp_index']; if(!isset($out[$i])) $out[$i]=['index'=>$i,'last_updated'=>$r['last_updated'],'age_seconds'=>$r['age_seconds']]; $out[$i][$r['field_name']]=$r['field_value']; }
    foreach ($out as &$i) $i['stale']=!isset($i['age_seconds']) || (int)$i['age_seconds']>600;
    return array_values($out);
}
function mlari_host_detail($hostId) {
    foreach (mlari_hosts() as $h) if ($h['id']===$hostId) return ['host'=>$h,'graphs'=>mlari_graphs($hostId),'interfaces'=>mlari_interfaces($hostId)];
    return null;
}

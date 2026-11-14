<?php
function mlari_topology() {
    $nodes = [
        ['id'=>'net_l','name'=>'Net izquierda','type'=>'cloud','x'=>150,'y'=>75],
        ['id'=>'net_r','name'=>'Net derecha','type'=>'cloud','x'=>890,'y'=>75],
        ['id'=>'r7','name'=>'ISP_PRINCIPAL_R7','type'=>'router','ip'=>'172.29.129.45','x'=>310,'y'=>185],
        ['id'=>'r8','name'=>'ISP_RESPALDO_R8','type'=>'router','ip'=>'172.29.129.118','cacti_id'=>2,'x'=>730,'y'=>185],
        ['id'=>'r14','name'=>'SW_DISTRIBUCION_1_R14','type'=>'switch','ip'=>'10.10.10.31','x'=>380,'y'=>340],
        ['id'=>'r13','name'=>'SW_DISTRIBUCION_2_R13','type'=>'switch','ip'=>'10.10.10.32','x'=>660,'y'=>340],
        ['id'=>'r1','name'=>'SW_ACCESO_IZQUIERDO_R1','type'=>'switch','ip'=>'10.10.10.33','x'=>380,'y'=>510],
        ['id'=>'r2','name'=>'SW_ACCESO_DERECHO_R2','type'=>'switch','ip'=>'10.10.10.34','x'=>660,'y'=>510],
        ['id'=>'mt_l','name'=>'MT_CLIENTE_IZQUIERDO','type'=>'mikrotik','ip'=>'10.10.10.10','x'=>295,'y'=>670],
        ['id'=>'mt_r','name'=>'MT_CLIENTE_DERECHO','type'=>'mikrotik','ip'=>'10.10.10.20','x'=>745,'y'=>670],
        ['id'=>'pc1','name'=>'PC1','type'=>'pc','x'=>295,'y'=>810],
        ['id'=>'pc2','name'=>'PC2','type'=>'pc','x'=>745,'y'=>810]
    ];
    $raw = [
        ['net_l','r7','','Ethernet0/0'],['net_r','r8','','Ethernet0/0'],
        ['r7','r8','Ethernet0/1','Ethernet0/1'],['r7','r14','Ethernet0/2','Ethernet0/1'],
        ['r7','r13','Ethernet0/3','Ethernet0/1'],['r8','r14','Ethernet0/2','Ethernet0/0'],
        ['r8','r13','Ethernet0/3','Ethernet0/0'],['r14','r13','Ethernet0/2','Ethernet0/2'],
        ['r14','r1','Ethernet0/3','Ethernet0/1'],['r13','r2','Ethernet0/3','Ethernet0/1'],
        ['r1','r2','Ethernet0/2','Ethernet0/2'],['r1','r2','Ethernet0/3','Ethernet0/3'],
        ['r1','mt_l','Ethernet0/0','ether1'],['mt_l','pc1','ether2',''],
        ['r2','mt_r','Ethernet0/0','ether1'],['mt_r','pc2','ether2','']
    ];
    $links = [];
    foreach ($raw as $i=>$edge) $links[]=['id'=>'link_'.($i+1),'source'=>$edge[0],'target'=>$edge[1],'source_port'=>$edge[2],'target_port'=>$edge[3],'state'=>'unverified'];
    return ['nodes'=>$nodes,'links'=>$links];
}

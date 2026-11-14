<?php
include_once '../../include/auth.php';
if (!api_plugin_user_realm_auth('index.php')) { http_response_code(403); exit('Acceso denegado'); }
top_header();
?>
<link rel="stylesheet" href="assets/mlari.css?v=1">
<main id="mlari" class="mlari">
  <header class="mlari-header"><div><span class="eyebrow">MLARI · CABLE MILENIO S.A.C.</span><h1>Centro de Control de Red</h1><p>Topología EVE-NG y telemetría registrada en MLARI</p></div><div class="header-status"><span id="refresh-status">Consultando MLARI…</span><button id="refresh-button" type="button">Actualizar</button></div></header>
  <nav class="mlari-nav" aria-label="Secciones MLARI">
    <button data-view="resumen">Resumen</button><button data-view="topologia">Topología</button><button data-view="dispositivos">Dispositivos</button><button data-view="interfaces">Interfaces</button><button data-view="rendimiento">Rendimiento</button><button data-view="alertas">Alertas</button><button data-view="diagnostico">Diagnóstico</button><button data-view="reportes">Reportes</button><button data-view="configuracion">Configuración</button><button data-view="ia">IA MLARI</button>
  </nav>
  <div id="mlari-error" class="error" hidden></div>
  <section id="mlari-content" aria-live="polite"></section>
  <aside id="mlari-drawer" class="drawer" hidden><button id="drawer-close" type="button" aria-label="Cerrar">×</button><div id="drawer-content"></div></aside>
</main>
<script src="assets/mlari.js?v=2" defer></script>
<?php bottom_footer(); ?>

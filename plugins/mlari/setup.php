<?php
function plugin_mlari_install() {
    api_plugin_register_hook('mlari', 'config_arrays', 'mlari_config_arrays', 'setup.php');
    api_plugin_register_hook('mlari', 'draw_navigation_text', 'mlari_navigation', 'setup.php');
    api_plugin_register_hook('mlari', 'page_head', 'mlari_assistant_head', 'setup.php');
    api_plugin_register_hook('mlari', 'page_bottom', 'mlari_assistant_bottom', 'setup.php');
    api_plugin_register_realm('mlari', 'index.php,api.php,assistant.php', 'MLARI Centro de Control', 1);
}
function plugin_mlari_uninstall() { return true; }
function plugin_mlari_version() { return array('name'=>'mlari','version'=>'0.2.0','longname'=>'MLARI Centro de Control de Red','author'=>'MLARI','homepage'=>'https://localhost/cacti/','compat'=>'1.2.29'); }
function plugin_mlari_check_config() { return true; }
function plugin_mlari_upgrade() {
    api_plugin_register_hook('mlari', 'page_head', 'mlari_assistant_head', 'setup.php');
    api_plugin_register_hook('mlari', 'page_bottom', 'mlari_assistant_bottom', 'setup.php');
    api_plugin_register_realm('mlari', 'index.php,api.php,assistant.php', 'MLARI Centro de Control', 1);
    return true;
}
function mlari_assistant_head() {
    global $config;
    if (!api_plugin_user_realm_auth('assistant.php')) return;
    echo '<link rel="stylesheet" href="' . html_escape($config['url_path']) . 'plugins/mlari/assets/assistant.css?v=2">';
}
function mlari_assistant_bottom() {
    global $config;
    if (!api_plugin_user_realm_auth('assistant.php')) return;
    $base = html_escape($config['url_path']) . 'plugins/mlari/';
    echo '<div id="mlari-assistant" data-api="' . $base . 'assistant.php" data-csrf="' . html_escape(csrf_get_tokens()) . '"></div>';
    echo '<script defer src="' . $base . 'assets/assistant.js?v=2"></script>';
}
function mlari_config_arrays() {
    global $menu;
    $menu[__('Management')]['plugins/mlari/index.php'] = array('plugins/mlari/index.php' => 'MLARI — Centro de Control');
}
function mlari_navigation($nav) {
    $nav['index.php:'] = array('title'=>'MLARI — Centro de Control','mapping'=>'','url'=>'index.php','level'=>'0');
    return $nav;
}

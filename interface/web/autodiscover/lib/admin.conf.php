<?php
/**
 * ispconfig Autodiscover Module - Admin Configuration
 */

$app->auth->add_permission('autodiscover_list', 'Tools');
$app->auth->add_permission('autodiscover_edit', 'Tools');
$app->auth->add_permission('autodiscover_del', 'Tools');

// Register module
if (!isset($modules) || !is_array($modules)) {
    $modules = array();
}

if (!isset($modules['autodiscover'])) {
    $modules['autodiscover'] = 1;
}
?>
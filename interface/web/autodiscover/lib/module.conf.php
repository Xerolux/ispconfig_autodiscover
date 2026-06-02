<?php
/**
 * ispconfig Autodiscover Module Configuration
 */

// Module name
$module['autodiscover']['title'] = 'Autodiscover';
$module['autodiscover']['description'] = 'Mail Client Autodiscover Service';

// Menu entry
$module['autodiscover']['nav']['autodiscover_list']['title'] = 'Autodiscover';
$module['autodiscover']['nav']['autodiscover_list']['image'] = 'autodiscover.png';
$module['autodiscover']['nav']['autodiscover_list']['target'] = 'autodiscover_list.php';
$module['autodiscover']['nav']['autodiscover_list']['sort'] = 70;
$module['autodiscover']['nav']['autodiscover_list']['visible'] = true;

// Permissions
$module['autodiscover']['permissions']['autodiscover_list']['title'] = 'View Autodiscover';
$module['autodiscover']['permissions']['autodiscover_list']['description'] = '';

$module['autodiscover']['permissions']['autodiscover_edit']['title'] = 'Edit Autodiscover';
$module['autodiscover']['permissions']['autodiscover_edit']['description'] = '';

$module['autodiscover']['permissions']['autodiscover_del']['title'] = 'Delete Autodiscover';
$module['autodiscover']['permissions']['autodiscover_del']['description'] = '';
?>
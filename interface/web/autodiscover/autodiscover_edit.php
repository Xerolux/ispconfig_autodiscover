<?php
/**
 * ispconfig Autodiscover Module - Edit View
 */

global $app;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'add';

// Get data if editing
if ($mode == 'edit') {
    $result = $app->db->queryOneRecord("SELECT * FROM autodiscover_config WHERE id = " . $app->db->quote($id));
    if (!$result) {
        $app->error('record_not_found');
    }
} else {
    $result = array(
        'domain' => '',
        'enabled' => 'y',
        'imap_host' => 'mail.example.com',
        'imap_port' => 993,
        'imap_security' => 'SSL',
        'smtp_host' => 'mail.example.com',
        'smtp_port' => 465,
        'smtp_security' => 'SSL',
        'smtp_auth_required' => 'y'
    );
}

// Process form
if ($_POST && isset($_POST['save'])) {
    $data = array(
        'domain' => $_POST['domain'],
        'enabled' => $_POST['enabled'],
        'imap_host' => $_POST['imap_host'],
        'imap_port' => (int)$_POST['imap_port'],
        'imap_security' => $_POST['imap_security'],
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => (int)$_POST['smtp_port'],
        'smtp_security' => $_POST['smtp_security'],
        'smtp_auth_required' => isset($_POST['smtp_auth_required']) ? 'y' : 'n'
    );

    if ($mode == 'edit') {
        $app->db->dataupdate('autodiscover_config', $data, 'id', $id);
        $app->tpl->setVar('message', $app->lng('msg_updated_successfully'));
    } else {
        $id = $app->db->datainsert('autodiscover_config', $data);
        $app->tpl->setVar('message', $app->lng('msg_added_successfully'));
    }
    $result = $data;
}

// Set form variables
$app->tpl->setVar('id', $id);
$app->tpl->setVar('mode', $mode);
$app->tpl->setVar('domain', htmlspecialchars($result['domain']));
$app->tpl->setVar('enabled', $result['enabled']);
$app->tpl->setVar('imap_host', htmlspecialchars($result['imap_host']));
$app->tpl->setVar('imap_port', (int)$result['imap_port']);
$app->tpl->setVar('imap_security', $result['imap_security']);
$app->tpl->setVar('smtp_host', htmlspecialchars($result['smtp_host']));
$app->tpl->setVar('smtp_port', (int)$result['smtp_port']);
$app->tpl->setVar('smtp_security', $result['smtp_security']);
$app->tpl->setVar('smtp_auth_required_checked', $result['smtp_auth_required'] == 'y' ? 'checked' : '');
?>
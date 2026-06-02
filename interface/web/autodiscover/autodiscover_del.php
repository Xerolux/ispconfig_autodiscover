<?php
/**
 * ispconfig Autodiscover Module - Delete Function
 */

global $app;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Verify record exists
    $result = $app->db->queryOneRecord("SELECT * FROM autodiscover_config WHERE id = " . $app->db->quote($id));
    if ($result) {
        $app->db->query("DELETE FROM autodiscover_config WHERE id = " . $app->db->quote($id));
        $app->tpl->setVar('message', $app->lng('msg_deleted_successfully'));
    }
}

// Redirect back to list
header('Location: autodiscover_list.php');
exit;
?>
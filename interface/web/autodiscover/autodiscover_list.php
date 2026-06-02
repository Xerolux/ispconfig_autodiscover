<?php
/**
 * ispconfig Autodiscover Module - List View
 */

// Set template
$app->tpl->setVar("list_head_autodiscover", $app->lng("list_head_autodiscover"));
$app->tpl->setVar("list_head_domain", $app->lng("list_head_domain"));
$app->tpl->setVar("list_head_imap", $app->lng("list_head_imap"));
$app->tpl->setVar("list_head_smtp", $app->lng("list_head_smtp"));

// Fetch autodiscover configs
$sql = "SELECT * FROM autodiscover_config ORDER BY domain";
$result = $app->db->queryAllRecords($sql);

$autodiscoverlist = '';
if (is_array($result) && count($result) > 0) {
    foreach ($result as $row) {
        $autodiscoverlist .= '<tr class="' . ($row['enabled'] == 'y' ? 'active' : 'inactive') . '">';
        $autodiscoverlist .= '<td>' . htmlspecialchars($row['domain']) . '</td>';
        $autodiscoverlist .= '<td>' . htmlspecialchars($row['imap_host']) . ':' . $row['imap_port'] . ' (' . $row['imap_security'] . ')</td>';
        $autodiscoverlist .= '<td>' . htmlspecialchars($row['smtp_host']) . ':' . $row['smtp_port'] . ' (' . $row['smtp_security'] . ')</td>';
        $autodiscoverlist .= '<td style="text-align: center;">';
        $autodiscoverlist .= '<a href="autodiscover_edit.php?id=' . $row['id'] . '">' . $app->lng('btn_edit') . '</a> ';
        $autodiscoverlist .= '<a href="autodiscover_del.php?id=' . $row['id'] . '" onclick="return confirm(\'' . $app->lng('confirm_delete') . '\');">' . $app->lng('btn_delete') . '</a>';
        $autodiscoverlist .= '</td>';
        $autodiscoverlist .= '</tr>';
    }
}

$app->tpl->setVar("autodiscoverlist", $autodiscoverlist);
?>
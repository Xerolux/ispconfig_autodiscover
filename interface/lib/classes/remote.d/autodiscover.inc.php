<?php
/**
 * ispconfig Autodiscover Module - Remote API Functions
 */

class remote_autodiscover
{
    public $module_name = 'autodiscover';
    public $identifer = 'autodiscover';

    public function __construct()
    {
        global $app;
        $app->auth->add_permission('autodiscover_list', 'API');
        $app->auth->add_permission('autodiscover_edit', 'API');
        $app->auth->add_permission('autodiscover_del', 'API');
    }

    public function autodiscover_get($session_id, $primary_id)
    {
        global $app;
        $app->auth->check_module_permissions($session_id, $this->module_name);

        return $app->db->queryOneRecord("SELECT * FROM autodiscover_config WHERE id = " . $app->db->quote($primary_id));
    }

    public function autodiscover_add($session_id, $params)
    {
        global $app;
        $app->auth->check_module_permissions($session_id, $this->module_name);

        // Validation
        if (empty($params['domain'])) {
            throw new Exception('Domain is required.');
        }

        $insert_data = array(
            'domain' => $params['domain'],
            'enabled' => isset($params['enabled']) ? $params['enabled'] : 'y',
            'imap_host' => isset($params['imap_host']) ? $params['imap_host'] : 'mail.example.com',
            'imap_port' => isset($params['imap_port']) ? (int)$params['imap_port'] : 993,
            'imap_security' => isset($params['imap_security']) ? $params['imap_security'] : 'SSL',
            'smtp_host' => isset($params['smtp_host']) ? $params['smtp_host'] : 'mail.example.com',
            'smtp_port' => isset($params['smtp_port']) ? (int)$params['smtp_port'] : 465,
            'smtp_security' => isset($params['smtp_security']) ? $params['smtp_security'] : 'SSL',
            'smtp_auth_required' => isset($params['smtp_auth_required']) ? 'y' : 'n'
        );

        return $app->db->datainsert('autodiscover_config', $insert_data);
    }

    public function autodiscover_update($session_id, $primary_id, $params)
    {
        global $app;
        $app->auth->check_module_permissions($session_id, $this->module_name);

        $update_data = array();

        foreach (array('domain', 'enabled', 'imap_host', 'imap_port', 'imap_security',
                       'smtp_host', 'smtp_port', 'smtp_security', 'smtp_auth_required') as $key) {
            if (isset($params[$key])) {
                $update_data[$key] = $params[$key];
            }
        }

        if (!empty($update_data)) {
            $app->db->dataupdate('autodiscover_config', $update_data, 'id', $primary_id);
        }

        return $app->db->queryOneRecord("SELECT * FROM autodiscover_config WHERE id = " . $app->db->quote($primary_id));
    }

    public function autodiscover_delete($session_id, $primary_id)
    {
        global $app;
        $app->auth->check_module_permissions($session_id, $this->module_name);

        $app->db->query("DELETE FROM autodiscover_config WHERE id = " . $app->db->quote($primary_id));

        return true;
    }

    public function autodiscover_list($session_id)
    {
        global $app;
        $app->auth->check_module_permissions($session_id, $this->module_name);

        return $app->db->queryAllRecords("SELECT * FROM autodiscover_config ORDER BY domain");
    }
}
?>
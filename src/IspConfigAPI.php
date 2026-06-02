<?php

namespace Autodiscover;

class IspConfigAPI
{
    private $config;
    private $session_id = null;

    public function __construct(array $config)
    {
        $this->config = $config['ispconfig'];
    }

    /**
     * Login zu ispconfig API
     */
    public function login(): bool
    {
        $post_data = [
            'username' => $this->config['api_username'],
            'password' => $this->config['api_password'],
        ];

        $response = $this->call('remote/json.php?action=login', $post_data);

        if ($response && !empty($response['response']['session_id'])) {
            $this->session_id = $response['response']['session_id'];
            return true;
        }

        return false;
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if ($this->session_id) {
            $this->call('remote/json.php?action=logout', [
                'session_id' => $this->session_id,
            ]);
            $this->session_id = null;
        }
    }

    /**
     * Get domain information by domain name
     */
    public function getDomainInfo(string $domain): ?array
    {
        if (!$this->session_id && !$this->login()) {
            return null;
        }

        $post_data = [
            'session_id' => $this->session_id,
            'primary_domain' => $domain,
        ];

        $response = $this->call('remote/json.php?action=domain_get', $post_data);

        if ($response && !empty($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Get mail user by email
     */
    public function getMailUserByEmail(string $email): ?array
    {
        if (!$this->session_id && !$this->login()) {
            return null;
        }

        [$username, $domain] = explode('@', $email);

        $post_data = [
            'session_id' => $this->session_id,
            'email' => $email,
        ];

        $response = $this->call('remote/json.php?action=mail_user_get', $post_data);

        if ($response && !empty($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Get server info
     */
    public function getServerInfo(): ?array
    {
        if (!$this->session_id && !$this->login()) {
            return null;
        }

        $post_data = [
            'session_id' => $this->session_id,
        ];

        $response = $this->call('remote/json.php?action=server_get', $post_data);

        if ($response && !empty($response['response'])) {
            return $response['response'];
        }

        return null;
    }

    /**
     * Internal API call
     */
    private function call(string $endpoint, array $post_data): ?array
    {
        $url = rtrim($this->config['api_url'], '/') . '/' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['verify_ssl']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            return null;
        }

        return json_decode($response, true);
    }
}

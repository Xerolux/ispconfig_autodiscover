#!/usr/bin/env php
<?php
/**
 * ispconfig Autodiscover Module - Installation Script
 *
 * Usage: php install.php [from the module directory]
 */

// Check we're in the right directory
if (!is_dir('interface') || !is_dir('sql') || !is_dir('website')) {
    die("Error: Please run this script from the module directory.\n");
}

echo "ispconfig Autodiscover Module Installation\n";
echo "============================================\n\n";

// 1. Copy interface files
echo "1. Installing ispconfig interface module...\n";
if (!is_dir('/usr/local/ispconfig')) {
    die("   Error: ispconfig not found at /usr/local/ispconfig\n");
}

$cmd = 'chown -R ispconfig:ispconfig interface && chmod -R 750 interface';
exec($cmd, $output, $return);

if ($return !== 0) {
    die("   Error: Could not set permissions on interface files\n");
}

$cmd = 'cp -prf interface/* /usr/local/ispconfig/interface/';
exec($cmd, $output, $return);

if ($return !== 0) {
    die("   Error: Could not copy interface files\n");
}

exec('chmod -R 770 /usr/local/ispconfig/interface/web/autodiscover/lib/lang/ 2>/dev/null');

echo "   ✓ Interface module installed\n\n";

// 2. Database setup
echo "2. Setting up database...\n";

// Load ispconfig config
if (!file_exists('/usr/local/ispconfig/server/lib/config.inc.php')) {
    die("   Error: ispconfig configuration not found\n");
}

require_once '/usr/local/ispconfig/server/lib/config.inc.php';
require_once '/usr/local/ispconfig/server/lib/mysql_clientdb.conf';

try {
    $pdo = new PDO(
        'mysql:host=' . $clientdb_host,
        $clientdb_user,
        $clientdb_password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database if needed
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $conf['db_database'] . "`");

    // Select database
    $pdo->exec("USE `" . $conf['db_database'] . "`");

    // Execute SQL
    $sql = file_get_contents('sql/autodiscover.sql');
    $pdo->exec($sql);

    echo "   ✓ Database tables created/updated\n\n";

} catch (PDOException $e) {
    die("   Error: Database setup failed - " . $e->getMessage() . "\n");
}

// 3. Create website directory
echo "3. Setting up website files...\n";

$website_dir = '/var/www/autodiscover';
if (!is_dir($website_dir)) {
    @mkdir($website_dir, 0755, true);
}

$cmd = 'cp -prf website/* ' . escapeshellarg($website_dir) . '/';
exec($cmd, $output, $return);

if ($return !== 0) {
    echo "   Warning: Could not copy all website files\n";
} else {
    // Set permissions
    @chown($website_dir, 'www-data');
    @chgrp($website_dir, 'www-data');
    @chmod($website_dir, 0755);
    @chmod($website_dir . '/includes', 0750);

    echo "   ✓ Website files installed to $website_dir\n\n";
}

// 4. Configuration
echo "4. Configuration\n";
echo "   Website config: " . $website_dir . "/website/config.php\n";
echo "   - Copy config.example.php to config.php\n";
echo "   - Update database credentials\n";
echo "   - Update mail server settings\n\n";

// 5. Web Server Setup
echo "5. Web Server Configuration Required\n";
echo "   Apache:\n";
echo "   - Enable mod_rewrite: a2enmod rewrite\n";
echo "   - Restart: systemctl restart apache2\n";
echo "   - .htaccess in " . $website_dir . "/website/.htaccess is configured\n\n";
echo "   Nginx:\n";
echo "   - See README.md for nginx configuration example\n\n";

// 6. Summary
echo "Installation Complete!\n";
echo "======================\n\n";
echo "Next steps:\n";
echo "1. Configure website: cp " . $website_dir . "/website/config.example.php " . $website_dir . "/website/config.php\n";
echo "2. Update config.php with your mail server settings\n";
echo "3. Restart ispconfig GUI (or log out/in)\n";
echo "4. Go to Tools >> Autodiscover in ispconfig\n";
echo "5. Enable autodiscover for your domains\n\n";
echo "Testing:\n";
echo "curl -X POST -d '<?xml version=\"1.0\"?><Autodiscover><Request><EMailAddress>user@domain.com</EMailAddress></Request></Autodiscover>' https://domain.com/autodiscover/autodiscover.xml\n\n";
?>
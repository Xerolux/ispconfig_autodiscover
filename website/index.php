<?php
/**
 * ispconfig Autodiscover Service
 *
 * This is the mail autodiscover service for ispconfig.
 * It provides automatic mail server configuration for Microsoft Outlook,
 * Mozilla Thunderbird, and other mail clients.
 */

if (!file_exists(__DIR__ . '/config.php')) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Autodiscover Service</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .info { padding: 20px; background: #f0f0f0; border-radius: 5px; }
            code { background: #eee; padding: 2px 5px; }
        </style>
    </head>
    <body>
        <h1>Autodiscover Service</h1>
        <div class="info">
            <p><strong>Configuration Required</strong></p>
            <p>Please create <code>config.php</code> from <code>config.example.php</code></p>
            <p>See the installation guide for details.</p>
        </div>
    </body>
    </html>
    <?php
} else {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Autodiscover Service</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .info { padding: 20px; background: #f0f0f0; border-radius: 5px; }
            code { background: #eee; padding: 2px 5px; }
            .endpoints { margin-top: 20px; }
        </style>
    </head>
    <body>
        <h1>Autodiscover Service</h1>
        <div class="info">
            <p>ispconfig Mail Autodiscover Service is running.</p>
            <p>Mail clients will automatically discover your mail server configuration.</p>

            <div class="endpoints">
                <h3>Endpoints:</h3>
                <ul>
                    <li><strong>Microsoft Autodiscover:</strong> <code>/autodiscover/autodiscover.xml</code></li>
                    <li><strong>Mozilla Autoconfig:</strong> <code>/.well-known/autoconfig/mail/config-v1.1.xml</code></li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
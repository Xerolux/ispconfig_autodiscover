# Installation Guide - ispconfig Autodiscover Module

## Voraussetzungen

- ispconfig 3.x (3.1+)
- PHP 7.4 oder höher
- MySQL/MariaDB
- Apache mit mod_rewrite ODER Nginx
- Postfix + Dovecot (Mail Server)
- SSH/Terminal Zugriff

## Quick Start (5 Minuten)

### 1️⃣ Module herunterladen

```bash
cd /var/www
git clone https://github.com/xerolux/ispconfig_autodiscover.git autodiscover
cd autodiscover
```

### 2️⃣ Installation starten

```bash
sudo php install.php
```

Das Script führt automatisch aus:
- Interface Files nach ispconfig kopieren
- Datenbanktabellen erstellen
- Website Files installieren
- Berechtigungen setzen

Output sollte so aussehen:
```
ispconfig Autodiscover Module Installation
============================================

1. Installing ispconfig interface module...
   ✓ Interface module installed

2. Setting up database...
   ✓ Database tables created/updated

3. Setting up website files...
   ✓ Website files installed to /var/www/autodiscover

Installation Complete!
```

### 3️⃣ Website konfigurieren

```bash
# Config aus Template erstellen
cp website/config.example.php website/config.php

# Berechtigungen setzen
chown -R www-data:www-data /var/www/autodiscover/website
chmod 755 /var/www/autodiscover/website
```

### 4️⃣ Mail-Server eintragen

```bash
nano /var/www/autodiscover/website/config.php
```

Folgende Werte anpassen:

```php
// ispconfig Datenbank
define('ISPCONFIG_DB_HOST', 'localhost');
define('ISPCONFIG_DB_USER', 'dbispconfig');
define('ISPCONFIG_DB_PASS', 'YOUR_ISPCONFIG_DB_PASSWORD');  // ← Anpassen!
define('ISPCONFIG_DB_NAME', 'dbispconfig');

// Mail-Server (Postfix/Dovecot)
$MAIL_CONFIG = [
    'imap' => [
        'host' => 'mail.example.com',      // ← Anpassen!
        'port' => 993,
        'security' => 'SSL',  // oder STARTTLS, NONE
    ],
    'smtp' => [
        'host' => 'mail.example.com',      // ← Anpassen!
        'port' => 465,
        'security' => 'SSL',  // oder STARTTLS, NONE
        'auth_required' => true,
    ],
    'pop3' => [
        'enabled' => false,    // Optional: POP3 aktivieren?
        'host' => 'mail.example.com',
        'port' => 995,
        'security' => 'SSL',
    ],
];
```

### 5️⃣ Web-Server konfigurieren

#### Apache

```bash
# mod_rewrite aktivieren
a2enmod rewrite

# Virtual Host erstellen (optional, wenn separate Domain)
sudo nano /etc/apache2/sites-available/autodiscover.conf
```

Inhalt:
```apache
<VirtualHost *:443>
    ServerName autodiscover.example.com
    ServerAlias example.com *.example.com
    
    DocumentRoot /var/www/autodiscover/website
    
    <Directory /var/www/autodiscover/website>
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Zertifikat (Let's Encrypt)
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/example.com/privkey.pem
</VirtualHost>

# HTTP → HTTPS Redirect
<VirtualHost *:80>
    ServerName autodiscover.example.com
    Redirect permanent / https://autodiscover.example.com/
</VirtualHost>
```

Aktivieren:
```bash
a2ensite autodiscover.conf
a2enmod ssl
systemctl reload apache2
```

#### Nginx

```bash
sudo nano /etc/nginx/sites-available/autodiscover
```

Inhalt:
```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name autodiscover.example.com example.com *.example.com;
    root /var/www/autodiscover/website;
    
    # SSL Zertifikat
    ssl_certificate /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;
    
    # Autodiscover Endpoints
    location ~ /autodiscover/autodiscover\.xml {
        try_files $uri @autodiscover;
    }
    
    location ~ /\.well-known/autoconfig/mail/config-v1\.1\.xml {
        try_files $uri @autoconfig;
    }
    
    location @autodiscover {
        rewrite ^/autodiscover/(.*)$ /autodiscover.php last;
    }
    
    location @autoconfig {
        rewrite ^/\.well-known/autoconfig/mail/(.*)$ /autoconfig.php last;
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # HTTP → HTTPS Redirect
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}

server {
    listen 80;
    listen [::]:80;
    server_name autodiscover.example.com example.com *.example.com;
    return 301 https://$server_name$request_uri;
}
```

Aktivieren:
```bash
ln -s /etc/nginx/sites-available/autodiscover /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 6️⃣ ispconfig neu starten

```bash
systemctl restart ispconfig
# oder
service ispconfig restart
```

Danach im ispconfig Admin Panel:
**Tools >> Autodiscover** sollte sichtbar sein!

---

## Verwendung

### In ispconfig Admin Panel

1. **Tools** >> **Autodiscover** anklicken
2. Für jede Domain, die Autodiscover braucht:
   - **Checkbox aktivieren**
   - Optional Mail-Server Einstellungen anpassen
   - Speichern

### Mail-Clients einrichten

Clients benötigen jetzt nur noch:
- **E-Mail Adresse**: `user@example.com`
- **Passwort**: Mailbox Passwort

Alle Server-Einstellungen werden automatisch erkannt! ✅

---

## Testen

### curl Test (Microsoft Autodiscover)

```bash
curl -X POST \
  -H "Content-Type: application/xml" \
  -d '<?xml version="1.0"?><Autodiscover><Request><EMailAddress>user@example.com</EMailAddress></Request></Autodiscover>' \
  https://example.com/autodiscover/autodiscover.xml
```

Expected Response: XML mit Server-Einstellungen

### curl Test (Mozilla Autoconfig)

```bash
curl "https://example.com/.well-known/autoconfig/mail/config-v1.1.xml?emailaddress=user@example.com"
```

Expected Response: XML mit Server-Einstellungen

### Browser Test

- `https://example.com/` → Status Seite
- `https://example.com/autodiscover/autodiscover.xml` → Error (POST erwartet)
- `https://example.com/.well-known/autoconfig/mail/config-v1.1.xml` → Error (Parameter fehlt)

---

## Troubleshooting

### "Configuration not found"

```bash
# config.php existiert?
ls -la /var/www/autodiscover/website/config.php

# Falls nicht:
cp /var/www/autodiscover/website/config.example.php /var/www/autodiscover/website/config.php
nano /var/www/autodiscover/website/config.php  # Bearbeiten!
```

### Autodiscover nicht in ispconfig sichtbar

```bash
# ispconfig neu starten
systemctl restart ispconfig

# Logs checken
tail -f /var/log/ispconfig/ispconfig.log
```

### "Database connection failed"

DB-Credentials prüfen in `config.php`:
```bash
# ispconfig DB Password prüfen
cat /usr/local/ispconfig/server/lib/config.inc.php | grep "DB\|password"
```

Dann in `config.php` eintragen:
```php
define('ISPCONFIG_DB_PASS', 'THE_CORRECT_PASSWORD');
```

### Mail-Clients finden Autodiscover nicht

1. **DNS MX Records** prüfen:
```bash
dig example.com MX
```

2. **SSL Zertifikat** prüfen:
```bash
openssl s_client -connect example.com:443
```

3. **Firewall** prüfen:
```bash
curl -v https://example.com/autodiscover/autodiscover.xml
```

### Logs anschauen

```bash
# Apache Errors
tail -f /var/log/apache2/error.log

# Nginx Errors  
tail -f /var/log/nginx/error.log

# PHP-FPM Errors
tail -f /var/log/php-fpm.log

# ispconfig Errors
tail -f /var/log/ispconfig/ispconfig.log
```

---

## Production Checklist

Vor Live-Betrieb:

- [ ] SSL/TLS Zertifikat gültig (Let's Encrypt)
- [ ] Mail-Server erreichbar (IMAP/SMTP Ports offen)
- [ ] config.php ist geschützt (600 Berechtigungen)
- [ ] Database Credentials korrekt
- [ ] Web-Server mod_rewrite/nginx routing funktioniert
- [ ] ispconfig Module sichtbar im Admin Panel
- [ ] Test mit echtem Mail-Client (Thunderbird/Outlook)
- [ ] Logs monitored (zB. via Monit/Nagios)
- [ ] Backup erstellt

---

## Updates

Module ist update-proof - einfach latest Version pullen:

```bash
cd /var/www/autodiscover
git pull origin main

# Eventuell DB-Schema Update?
cd /var/www/autodiscover
php install.php  # Nochmal laufen, überschreibt nur wenn nötig
```

ispconfig Core wird nicht beeinträchtigt! ✅

---

## Support

Probleme?

1. Logs checken (siehe oben)
2. config.php validieren
3. Firewall/Ports prüfen
4. Test mit curl machen

Bei Problemen: GitHub Issue erstellen! 🐛

---

## FAQ

**Q: Können Mail-Clients auch Autodiscover ohne DNS MX Records nutzen?**
A: Ja, aber der Client muss domain.com/autodiscover/... direkt kennen.

**Q: Brauche ich separate Subdomains (autodiscover.example.com)?**
A: Nein! Das Module läuft auf alle Domains automatisch.

**Q: Funktioniert es auch mit PopMail statt IMAP?**
A: Ja, POP3 in config.php aktivieren.

**Q: Sind die Passwörter sicher gespeichert?**
A: Nein, Passwörter werden nicht gespeichert. Mail-Clients geben sie direkt ein.

**Q: Kann ich es pro Domain aktivieren/deaktivieren?**
A: Ja! Einfach Checkbox in ispconfig (Tools >> Autodiscover) setzen.

---

Fertig! 🎉 Viel Erfolg beim Deployment!
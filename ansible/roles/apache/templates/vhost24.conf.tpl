# Default Apache virtualhost template

<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot {{ apache.docroot }}
    ServerName {{ apache.servername }}
    ErrorLog /var/log/apache2/bewelcome-error.log
    CustomLog /var/log/apache2/bewelcome-access.log combined
    php_admin_value error_reporting "E_ALL"
    <Directory {{ apache.docroot }}>
        AllowOverride All
        Options -Indexes +FollowSymLinks
        Require all granted
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^/*([^/]*)\.php /bw/$1.php [L,R,QSA]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^.* index.php [L,QSA,PT]
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName bewelcome
    DocumentRoot {{ rox.root_folder }}/htdocs
    ErrorLog /var/log/apache2/bewelcome-error.log
    CustomLog /var/log/apache2/bewelcome-access.log combined
    php_admin_value error_reporting "E_ALL"

    <Directory {{ rox.root_folder }}/htdocs>
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

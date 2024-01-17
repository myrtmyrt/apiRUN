# Projet API RUN

## Table des matières

- [Introduction](#introduction)
- [Programme de l'API](#programme-de-lapi)
- [Étape3](#etape3)


## Introduction

## Programme de l'API
- Jour 1 : 
- Jour 2 : 
- Jour 3 : 
- Jour 4 : 
- Jour 5 : 

## Étape 3 : Hello Docker

### Installation Nginx 

### Installation Dokuwiki

On met à jour le système et on s'assure que le système est à jour :

```bash
sudo apt update
sudo apt upgrade
```

On configure le Nginx. On crée un fichier de configuration pour notre site DokuWiki. 

```bash
sudo nano /etc/nginx/sites-available/dokuwiki
```


On ajoute la configuration suivante à mon fichier de base nommé projet. 

```php
server {
listen 80;
server_name wiki.107.picagraine.net;  

    root /var/www/dokuwiki;
    index doku.php;

    location / {
        try_files $uri $uri/ @dokuwiki;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Vérifiez la version de PHP
    }

    location @dokuwiki {
        rewrite ^/_media/(.*) /lib/exe/fetch.php?media=$1 last;
        rewrite ^/_detail/(.*) /lib/exe/detail.php?media=$1 last;
        rewrite ^/_export/([^/]+)/(.*) /doku.php?do=export_$1&id=$2 last;
        rewrite ^/(.*) /doku.php?id=$1&$args last;
    }
}
```

On installe PHP et ses dépendances.

```bash
sudo apt install php-fpm php-cli php-gd php-xml php-mbstring
```

Redémarrage de Nginx et PHP-FPM :

```bash
sudo service nginx restart
sudo service php8.2-fpm restart 
```


On teéléchage Téléchargement de DokuWiki :
Accédez au répertoire où vous souhaitez installer DokuWiki.

```bash
cd /var/www/
```

Téléchargez l'archive DokuWiki et extrayez-la.

```bash
sudo wget https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz
sudo tar -xzvf dokuwiki-stable.tgz
sudo mv dokuwiki-*/ dokuwiki
```

On configure DokuWiki et on s'assure que les permissions sont correctes.

```bash
sudo chown -R www-data:www-data /var/www/dokuwiki

sudo certbot 

sudo systemctl reload nginx
```

Enfin on peut tester à cette adresse : https://dokuwiki.107.picagraine.net/





1. Clone le repository:

```bash
git https://gitlab.utc.fr/simde/bobby.git
```

2. Lancer l'application en local :




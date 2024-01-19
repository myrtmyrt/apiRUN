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

On accéde au répertoire où on veut installer DokuWiki.

```bash
cd /var/www/
```

On télécharge l'archive DokuWiki et on l'extraye.

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


## Projet : installation et hébergement du gestionnaire de mot de passe Vaultwarden

Nous pour cela utilisé l'image Vaultwarden de Docker. À noter que nous aurions aussi pu faire un building binary en installant Vaultwarden de manière plus manuelle.

Il a fallu effectuer ces différentes étapes : 
* Télécharger le docker 
* Paramétrer le proxy inverse et le /etc/nginx/sites-available/vaultwarden
* Créer un lien symbolique entre /etc/nginx/sites-available/vaultwarden et  /etc/nginx/sites-enabled/vaultwarden
* Lancer le docker et se mettre sur un port non utilisé
* Créer et lancer une base de données associés à Vaultwarden permettant de stocker les mots de passe.

```bash
docker pull vaultwarden/server:latest
docker run -d --name vaultwarden -v /vw-data/:/data/ --restart unless-stopped -p 6769:80 -e ROCKET_DATABASES="{postgresql://bitwarden:coucou@postgres-bitwarden/bitwarden}" vaultwarden/server:latest

```

```php
server {
    server_name chat.107.picagraine.net;
    location / {
        proxy_pass http://localhost:6769;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        }

    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/chat.107.picagraine.net/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/chat.107.picagraine.net/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

}


server {
    if ($host = chat.107.picagraine.net) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    listen 80;
    server_name chat.107.picagraine.net;
    return 404; # managed by Certbot
}
```


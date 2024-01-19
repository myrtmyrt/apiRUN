# Projet API RUN

## Table des matières

- [Introduction](#introduction)
- [Programme de l'API](#programme-de-lapi)
- [Installation Dokuwiki](#installation-dokuwiki)
- [Installation Conduit](#installation-conduit-)
- [Installation Vaultwerden](#projet-vaultwarden)


## Introduction
Objectif de l’Api : Découvrir l'administration de serveurs sous Linux et le fonctionnement de la technologie Docker

Objectifs spécifiques :
* Savoir utiliser un serveur sous Linux
* Savoir installer et configurer un serveur web, un serveur PHP, un serveur PostgreSQL
* Savoir utiliser la technologie de conteneurisation Docker

Objectifs transversaux :
* Savoir lire la documentation technique
* Savoir gérer les erreurs système (compréhension, analyse, recherche)


## Programme de l'API
- Jour 1 : Hello Serveur : Linux/Debian (rappels), SSH, HTML, Serveur Web Ngnix (mono-site, sans domaine)
- Jour 2 : Hello Web : Nom de domaine, Serveur multi-sites, PHP, PostgreSQL
- Jour 3 : Hello Docker : Principes de Docker (utiliser des images existantes)
- Jour 4 : Créer sa propre image Docker et Approfondir Ngnix, PHP, PostgreSQL, l'usage de conteneurs Docker
- Jour 5 : Finalisation et documentation du projet

## Installation Dokuwiki

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


## Projet Vaultwarden

Le but du projet est d'installer et d'heberger un gestionnaire de mot de passe Vaultwwarden.
Nous pour cela utilisé l'image Vaultwarden de Docker. À noter que nous aurions aussi pu faire un building binary en installant Vaultwarden de manière plus manuelle.

Il a fallu effectuer ces différentes étapes : 
* Télécharger le docker 
* Paramétrer le proxy inverse et le /etc/nginx/sites-available/vaultwarden
* Créer un lien symbolique entre /etc/nginx/sites-available/vaultwarden et  /etc/nginx/sites-enabled/vaultwarden
* Lancer le docker et se mettre sur un port non utilisé
* Créer et lancer une base de données associés à Vaultwarden permettant de stocker les mots de passe.

### Les commandes à lancer 
```bash
docker pull vaultwarden/server:latest
docker run -d --name vaultwarden -v /vw-data/:/data/ --restart unless-stopped -p 6769:80 -e ROCKET_DATABASES="{postgresql://bitwarden:coucou@postgres-bitwarden/bitwarden}" vaultwarden/server:latest

```
### Le fichier de config /etc/nginx/sites-available/vaultwarden
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

Le Vaultwarden est disponible à l'adresse suivante : https://chat.107.picagraine.net/
J'ai du le passer sur chat afin qu'il soit certifié en HTTPS.

## Installation Conduit 

* Récupérer l'image Matrix

```bash
docker image pull docker.io/matrixconduit/matrix-conduit:latest

```

* Ajouter cette partie de code dans dans /etc/nginx/nginx.conf dans http{} :

```php
proxy_headers_hash_max_size 1024;
proxy_headers_hash_bucket_size 128;
```

* Ajouter cette paartie dans /etc/nginx/sites-available/chat juste en dessous de la ligne proxypass dans le location /

```php
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
```

* Commande pour lancer le docker : 
```bash
sudo docker run -d -p 8080:6167   -v db:/var/lib/matrix-conduit/   -e
 CONDUIT_SERVER_NAME="chat.107.picagraine"   -e
  CONDUIT_DATABASE_BACKEND="rocksdb"   -e
   CONDUIT_ALLOW_REGISTRATION=true   -e
    CONDUIT_ALLOW_FEDERATION=true   -e
     CONDUIT_MAX_REQUEST_SIZE=20000000   -e
      CONDUIT_TRUSTED_SERVERS="[\"matrix.org\"]"   -e
       CONDUIT_MAX_CONCURRENT_REQUESTS="100"   -e
        CONDUIT_LOG="warn,rocket=off,_=off,sled=off"   --name conduit matrixconduit/matrix-conduit:latest
```



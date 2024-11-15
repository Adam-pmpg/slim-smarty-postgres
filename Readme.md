#PHP Slim Smarty Postgres

##Opis
Potrzebuję małe API do odebrania pociętego video

- Apache
- PostgreSQL
- phpAdmin
- PHP 8.1

##Adresacja aplikacji
Aplikacja PHP Slim będzie dostępna na porcie 8180 (http://localhost:8180)

phpPgAdmin będzie dostępny na porcie 8181 (http://localhost:8181)

##Uruchomienie aplikacji | Docker

#1. Zbuduj obraz Docker na podstawie pliku Dockerfile
docker-compose build

#2. Uruchom wszystkie kontenery zdefiniowane w docker-compose.yml

docker-compose up -d --build

docker-compose up -d

##Przebudowanie obrazu z nowymi zależnościami

docker-compose up -d --build

##zatrzymanie kontenerów

docker-compose down

##Obrazy

0ac6bdb33cba   dockage/phppgadmin             "/sbin/entrypoint ap…"   About a minute ago   Up About a minute   443/tcp, 0.0.0.0:8181->80/tcp, [::]:8181->80/tcp   php-slim-api-phppgadmin-slim-1
fcb08ad4c99d   php-slim-api-php-apache-slim   "docker-php-entrypoi…"   About a minute ago   Up About a minute   0.0.0.0:8180->80/tcp, [::]:8180->80/tcp            php-slim-api-php-apache-slim-1
e6faa3355787   postgres:13                    "docker-entrypoint.s…"   About a minute ago   Up About a minute   5432/tcp                                           php-slim-api-db-slim-1

###Statusy muszą być UP

###Jeśli potrzeba, uruchomienie coposer'a wewnątrz kontenera

docker-compose exec php-apache-slim composer install

###Testowanie dostępu do aplikacji i phpPgAdmin

####Aplikacja Slim PHP
Aplikacja Slim powinna być dostępna pod adresem: http://localhost:8180

Przykład: http://localhost:8180/hello/World

####PhpPgAdmin
phpPgAdmin będzie dostępny pod adresem: http://localhost:8181

#####Logowanie do pgAdmina | image: dpage/pgadmin4

- dpage/pgadmin4: potrzebuje uproszczonej konfiguracji, nie obsługuje zmiennych środowiskowych związanych z logowaniem. Tworzy się do  w samej bazie danych.

Po zalogowaniu się do pgAdmina (np. pod http://localhost:8181), dodaj serwer PostgreSQL w zakładce "Add New Server", gdzie ręcznie wpiszesz dane:

Host: db-slim
Port: 5432
Username: user
Password: password
###Monitorowanie logów

docker-compose logs -f

###Zatrzymywanie kontenerów

docker-compose down

##Do kontenera

docker-compose exec php-apache-slim bash

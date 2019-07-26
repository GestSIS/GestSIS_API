[![Build Status](https://travis-ci.com/Ph0tonic/GestSIS_API.svg?token=CpCE2t9dSFqCXfyBr7VR&branch=master)](https://travis-ci.com/Ph0tonic/GestSIS_API)

# GestSIS_API

Nouvelle version de l'api GestSIS

## Installation

Installation des dépendances :

```sh
composer install
cp .env.example .env
```

Modifier le fichier `.env` afin de configurer la base de données. Attention, la création de la base de données n'est pas réalisé de ce script.

Puis :
```sh
php artisan key:generate
php artisan migrate --step
php artisan db:seed
```

## Configuration de l'authentification

```sh
mkdir storage/keys
```

Ajouter un fichier `auth-public.key` dans le dossier `storage/keys` contenant la clé publique.

Il est possible de générer une paire de clé à l'adresse suivante :
- [http://travistidwell.com/jsencrypt/demo/](http://travistidwell.com/jsencrypt/demo/) 

## Serveur de développement

Pour lancer le serveur de dev :

```sh
php artisan serve
```

## Architecture Hexagonale

Ce projet utilise une architecture hexagonale afin d'améliorer la maintenance du projet.

### Couche application

Tout ce qui permet d'interagir avec le domaine, `app\application`

### Le domaine

Se compose de 3 couches les API's, le business et les SPI's.

### API's

Cette couche se compose de services qui définissent les entrés du code métier, toute action à destination du métier passe par là. `app\domaine\API`
Toute action de modification doit être passée à la couche Business. Seul les actions de listing peuvent directemenet être résolues.

### Business

Le code métier dans le dossier`app\domaine\Business`

### SPI's

Défini les besoins qui seront implémentés dans la couche infrastructure. `app\domaine\SPI`

### Couche Infrastructure

Ce qui est piloté par le domaine, `app\infrastructure`


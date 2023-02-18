[![CI](https://github.com/Ph0tonic/GestSIS_API/actions/workflows/main.yml/badge.svg)](https://github.com/Ph0tonic/GestSIS_API/actions/workflows/main.yml)

# GestSIS_API

Nouvelle version de l'api GestSIS

## Installation

Installation des dépendances :

```sh
composer install
cp .env.example .env
```

Modifier le fichier `.env` afin de configurer la base de données. Attention, la création de la base de données n'est pas réalisée par ce script. La base de données pour ce serveur (GestSIS_API) ne doit pas
être la même que celle du serveur d'authentification (GestSIS_Auth).

Puis :
```sh
php artisan key:generate
php artisan migrate --step
php artisan db:seed
```

### Configuration de l'authentification

```sh
mkdir storage/keys
```

Ajouter le fichier `auth-public.key` dans le dossier `storage/keys` contenant la clé publique.

Ce fichier doit au préalable avoir été généré dans le projet GestSIS_Auth.

### Etats de sortie

Tous les états de sorties pour impression sont générés au format `html`, aucun `pdf` n'est généré.

Le service `GestSIS_Print` permet de convertir la version `html` en `pdf` en respectant tous les styles CSS ce qui n'était pas le cas de la précédente solution se basant sur `wkhtmltopdf`.

### Serveur de développement

Pour lancer le serveur de dev :

```sh
php artisan serve
```

### Développement dans une machine virtuelle
Si le serveur de développement est lancé dans une machine virtuelle, mais que l'accès se fait depuis l'hôte, il est nécessaire d'ajouter `--host=XXX` à la commande ci-dessus.

## Architecture Hexagonale

Ce projet utilise une architecture hexagonale afin d'améliorer la maintenance du projet.

### Couche application

Tout ce qui permet d'interagir avec le domaine, `app\application`

### Le domaine

Se compose de 3 couches les API's, le business et les SPI's.

### API's

Cette couche se compose de services qui définissent les entrés du code métier, toute action à destination du métier passe par là. `App\Domaine\API`
Toute action de modification doit être passée à la couche Business. Seul les actions de listing peuvent directemenet être résolues.

### Business

Le code métier dans le dossier`app\domaine\Business`

### SPI's

Défini les besoins qui seront implémentés dans la couche infrastructure. `app\domaine\SPI`

### Couche Infrastructure

Ce qui est piloté par le domaine, `app\infrastructure`

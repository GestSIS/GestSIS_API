[![CI](https://github.com/Ph0tonic/GestSIS_API/actions/workflows/main.yml/badge.svg)](https://github.com/Ph0tonic/GestSIS_API/actions/workflows/main.yml)

# GestSIS_API

API de gestion des services d'incendie et de secours (SIS) construite avec **Laravel 12** et **PHP 8.4**. Ce système permet la gestion complète des sapeurs-pompiers, interventions, équipements et documents réglementaires dans un environnement multi-tenant.

## 📋 Table des matières

- [Fonctionnalités principales](#-fonctionnalités-principales)
- [Architecture](#-architecture)
- [Technologies](#-technologies)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Développement](#-développement)
- [Tests](#-tests)
- [Multi-tenant](#-architecture-multi-tenant)
- [Génération de documents](#-génération-de-documents)

## ✨ Fonctionnalités principales

- **Gestion des sapeurs-pompiers** : Fichiers personnels, grades, formations, disponibilités
- **Gestion des interventions** : Rapports d'intervention, matériel utilisé, personnel engagé
- **Documents réglementaires** : Génération automatique de fiches sapeur, certificats de salaire, rapports
- **Multi-tenant** : Support de plusieurs SIS avec bases de données séparées
- **Authentification JWT** : Intégration avec microservice GestSIS_Auth
- **Export Excel** : Exports personnalisés pour analyses et archivage
- **Intégrations externes** : API RTA, système d'alarme, envoi de SMS (ASPSMS)

## 🏗 Architecture

> ⚠️ **IMPORTANT - Évolution architecturale en cours**  
> L'architecture hexagonale actuellement en place **sera abandonnée** prochainement. Elle sera remplacée par une architecture simplifiée à **2 couches** :
> - **Controllers** : Gestion des requêtes HTTP et réponses
> - **Business** : Logique métier directement avec Eloquent
> 
> **Raison** : La couche Repository (SPI + implémentation) n'apporte pas de valeur ajoutée avec Eloquent, qui fournit déjà une abstraction efficace de la base de données. Cette simplification réduira la complexité sans compromettre la maintenabilité.

### Architecture actuelle (hexagonale - deprecated)

Ce projet utilise actuellement une **architecture hexagonale** (ports et adapters) qui sera simplifiée.

#### Flux de données (actuel)

```
Controller → Business → SPI Interface → Repository Implementation → Model
```

### Couches

#### 1. Couche Application (`app/Application/`)

Point d'entrée HTTP, gère les aspects techniques de l'application :
- **Controllers** (`Http/Controllers/`) : Reçoivent les requêtes HTTP et délèguent aux API Services
- **Middleware** : Authentification JWT, sélection de base de données multi-tenant
- **Auth** : Validation des tokens JWT
- **Mail** : Templates d'emails
- **Typst** : Génération de documents PDF

**Exemple** : `SapeurController::show($sapeurId)` → `Sapeur::find($sapeurId)`

#### 2. Couche Domaine (`app/Domaine/`)

Cœur métier de l'application, divisé en trois sous-couches :

##### 2.1 Business (`Domaine/Business/`)

Logique métier et règles de gestion :
- Validation des règles métier
- Calculs complexes (ex: statut actif d'un sapeur)
- Orchestration de transactions
- Définition des constantes métier

**Exemple** :
```php
class SapeurBusiness {
    const TYPE_SAPEUR = 0;
    const TYPE_CIVIL = 1;
    
    public function isActif(Sapeur $sapeur): bool {
        // Logique métier pour déterminer si un sapeur est actif
    }
}
```

##### 2.2 SPI - Service Provider Interface (`Domaine/SPI/`)

Interfaces définissant les contrats d'accès aux données :
- Découplage entre le domaine et l'infrastructure
- Permet de changer d'implémentation (Eloquent, Doctrine, API externe, etc.)

**Exemple** :
```php
interface SapeurRepository {
    public function findById(int $id): ?Sapeur;
    public function findAll(): array;
    public function save(Sapeur $sapeur): Sapeur;
}
```

#### 3. Couche Infrastructure (`app/Infrastructure/`)

Implémentation technique, dépendances externes :
- **Repositories** (`Repositories/`) : Implémentations Eloquent des interfaces SPI (suffixe `*RepositoryEloquent`)
- **Models** (`Models/`) : Modèles Eloquent pour l'ORM
- **Collections** (`Collections/`) : Collections pour exports Excel (Maatwebspace)

**Exemple** :
```php
class SapeurRepositoryEloquent implements SapeurRepository {
    public function findById(int $id): ?Sapeur {
        return Sapeur::find($id);
    }
}
```

### Architecture cible (simplifiée)

La future architecture à 2 couches sera organisée ainsi :

```
Controller → Business (avec Eloquent) → Model
```

#### Controllers (`app/Application/Http/Controllers/`)

Responsabilités :
- Réception et validation des requêtes HTTP
- Appel direct des classes Business
- Formatage des réponses (JSON, PDF, Excel)
- Gestion des codes HTTP et erreurs

**Exemple** :
```php
class SapeurController extends Controller {
    public function __construct(
        private SapeurBusiness $sapeurBusiness
    ) {}
    
    public function index() {
        $sapeurs = $this->sapeurBusiness->listActifs();
        return response()->json($sapeurs);
    }
    
    public function store(Request $request) {
        $sapeur = $this->sapeurBusiness->create($request->validated());
        return response()->json($sapeur, 201);
    }
}
```

#### Business (`app/Domaine/Business/`)

Responsabilités :
- Logique métier et règles de gestion
- Utilisation directe d'Eloquent (Models)
- Transactions et orchestration
- Calculs et validations métier

**Exemple** :
```php
class SapeurBusiness {
    const TYPE_SAPEUR = 0;
    const TYPE_CIVIL = 1;
    
    public function listActifs() {
        return Sapeur::where('statut', 'actif')
            ->with(['grade', 'fonction'])
            ->get();
    }
    
    public function create(array $data): Sapeur {
        DB::transaction(function() use ($data) {
            $sapeur = Sapeur::create($data);
            // Autres opérations métier...
            return $sapeur;
        });
    }
    
    public function isActif(Sapeur $sapeur): bool {
        // Logique métier pour déterminer si un sapeur est actif
    }
}
```

### Principes architecturaux (architecture cible)

✅ **À faire** :
- Controllers appellent uniquement les classes Business
- Business contient toute la logique métier
- Utiliser Eloquent directement dans Business (queries, relations, transactions)
- Models restent simples (propriétés, casts, relations)

❌ **À éviter** :
- Logique métier dans les Controllers
- Requêtes Eloquent complexes dans les Controllers
- Logique applicative dans les Models
- Duplication de code métier

### Principes architecturaux actuels (hexagonale - deprecated) (architecture cible)

✅ **À faire** :
- Controllers appellent uniquement les Business
- Business utilise les interfaces SPI
- Repositories implémentent les interfaces SPI

❌ **À éviter** :
- Logique métier dans les Controllers
- Injection directe de Models dans Business
- Repositories sans interface SPI

## 🛠 Technologies

- **Backend** : Laravel 12, PHP 8.4
- **Base de données** : MySQL/MariaDB (multi-tenant)
- **Authentification** : JWT (microservice GestSIS_Auth)
- **Documents PDF** : Typst, PDFTK
- **Export** : Maatwebsite Excel
- **Monitoring** : Sentry
- **Tests** : PHPUnit
- **Assets** : Vite

## 🚀 Installation

### Prérequis

- PHP 8.4+
- Composer
- MySQL/MariaDB
- Node.js & Yarn (pour les assets)
- Typst (pour génération PDF) : [Installation](https://github.com/typst/typst)
- PDFTK (optionnel, pour certificats de salaire)

### Installation des dépendances

```bash
# Dépendances PHP
composer install

# Configuration
cp .env.example .env

# Clé d'application
php artisan key:generate
```

### Configuration de la base de données

⚠️ **Important** : Créer manuellement les bases de données avant de lancer les migrations. GestSIS_API et GestSIS_Auth doivent avoir des bases de données **séparées**.

Modifier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestsis_api
DB_USERNAME=root
DB_PASSWORD=secret

# Liste des bases multi-tenant (clés SIS séparées par des virgules)
DB_LISTE=sdis1,sdis2,sdis3
```

### Migration et seeders

```bash
# Migration avec étapes
php artisan migrate --step

# Seeding des données de test
php artisan db:seed
```

Pour les environnements multi-tenant :
```bash
# Migrer toutes les bases tenant
php artisan dbs:migrate

# Réinitialiser toutes les bases (fresh + seed)
php artisan dbs:init
```

## ⚙️ Configuration

### Authentification JWT

1. Créer le dossier des clés :
```bash
mkdir -p storage/keys
```

2. Ajouter la clé publique `auth-public.key` dans `storage/keys/`

   ⚠️ Ce fichier doit être généré depuis le projet **GestSIS_Auth** (serveur d'authentification)

Configuration dans `.env` :
```env
JWT_PUBLIC_KEY_PATH=storage/keys/auth-public.key
JWT_ISSUER=GestSIS_Auth
JWT_AUDIENCE=GestSIS_API
```

### Génération de documents

#### Typst (documents principaux)

```env
TYPST_BIN_PATH=/path/to/typst
TYPST_FONT_PATH=/path/to/fonts
```

Templates disponibles dans `resources/typst/` :
- `fiche-sapeur.typ` : Fiche individuelle sapeur
- `rapport-intervention.typ` : Rapport d'intervention
- `common.typ` : Fonctions et styles communs

#### PDFTK (certificats de salaire)

```env
PDFTK_BIN_PATH=/usr/bin/pdftk
PDFTK_LIB_FOLDER=/usr/lib
```

Référence : [lambda-pdftk-example](https://github.com/lob/lambda-pdftk-example)

### Intégrations externes

```env
# API RTA
APP_RTA_API_URL=https://rta.example.com

# Système d'alarme
APP_GESTSIS_ALARM_URL=https://alarm.example.com

# SMS ASPSMS
ASPSMS_USER=your_user
ASPSMS_PASSWORD=your_password
```

### Monitoring

```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=0.1
```

## 💻 Développement

### Serveur de développement

```bash
# Serveur local
php artisan serve

# Accès depuis l'hôte (VM/Container)
php artisan serve --host=0.0.0.0 --port=8000
```

### Assets frontend

```bash
# Installation
yarn install

# Compilation dev (watch mode)
yarn dev

# Build production
yarn build
```

### Commandes utiles

```bash
# Lister les routes
php artisan route:list

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Générer des classes
php artisan make:controller NomController
php artisan make:model Infrastructure/Models/NomModel
```

### Tâches planifiées

Les tâches cron sont définies dans `cron.sh` :
```bash
# Mise à jour du statut actif des sapeurs (toutes les bases)
php artisan dbs:sapeurs-actif-status
```

## 🧪 Tests

### Exécuter les tests

```bash
# Tous les tests
php artisan test

# Tests unitaires uniquement
php artisan test tests/Unit

# Tests fonctionnels uniquement
php artisan test tests/Feature

# Test spécifique
php artisan test tests/Feature/SapeurControllerTest.php

# Avec coverage
php artisan test --coverage-html coverage
```

### Environnement de test

Les tests utilisent une connexion `testing` dédiée (configurée dans `config/database.php`). Le middleware de sélection de base de données est automatiquement contourné quand `APP_ENV=testing`.

Configuration `.env.testing` :
```env
APP_ENV=testing
DB_CONNECTION=testing
DB_DATABASE=gestsis_test
```

### Factories

Utiliser les factories pour générer des données de test :
```php
use App\Infrastructure\Models\Sapeur;

$sapeur = Sapeur::factory()->make(); // Instance non persistée
$sapeur = Sapeur::factory()->create(); // Instance persistée
$sapeurs = Sapeur::factory()->count(10)->create();
```

## 🏢 Architecture Multi-tenant

GestSIS_API gère plusieurs organisations de pompiers (SIS), chacune avec sa propre base de données.

### Fonctionnement

1. **Sélection de la base** : Le middleware `DbSelector` lit le header HTTP :
   - `Sis-Id` : Identifiant numérique du SIS
   - `Sis-Key` : Clé textuelle du SIS (ex: "sdis1")

2. **Switching runtime** : 
```php
Config::set('database.default', 'db_' . $sisKey);
```

3. **Connexions multiples** : Définies dans `config/database.php` à partir de `DB_LISTE`

### Commandes multi-tenant

| Commande | Description |
|----------|-------------|
| `php artisan dbs:migrate` | Migrer toutes les bases tenant |
| `php artisan dbs:init` | Fresh migration + seed pour toutes les bases |
| `php artisan dbs:sapeurs-actif-status` | Job batch de calcul statut actif |

### Exemple d'appel API

```bash
curl -X GET https://api.gestsis.ch/api/sapeurs \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Sis-Key: sdis1" \
  -H "Accept: application/json"
```

## 📄 Génération de documents

### Workflow Typst

1. Créer un répertoire temporaire
2. Copier le template `.typ` + `common.typ` + logo
3. Écrire les données en JSON
4. Compiler : `typst compile template.typ --font-path=$TYPST_FONT_PATH`
5. Retourner le PDF généré

**Classe principale** : `App\Application\Typst\TypstToPdfGenerator`

### Exemple de génération

```php
use App\Application\Typst\TypstToPdfGenerator;

$generator = new TypstToPdfGenerator();
$pdfPath = $generator->generateDocument(
    'fiche-sapeur',
    ['sapeur' => $sapeurData]
);
```

## 📝 Conventions de code

### Nommage

- **Interfaces SPI** : `SapeurRepository` (dans `Domaine/SPI/`)
- **Implémentations** : `SapeurRepositoryEloquent` (dans `Infrastructure/Repositories/`)
- **Business** : `SapeurBusiness` (dans `Domaine/Business/`)
- **Models** : `Sapeur` (dans `Infrastructure/Models/`)

### Dates

- **Format suisse** : `DD.MM.YYYY` (ex: `29.01.2019`)
- Utiliser **Carbon** pour les manipulations
- Dates seules : `setTime(0, 0)` pour les comparaisons

### Constantes métier

Définir dans les classes Business :
```php
class SapeurBusiness {
    const TYPE_SAPEUR = 0;
    const TYPE_CIVIL = 1;
    const STATUT_ACTIF = 'actif';
    const STATUT_INACTIF = 'inactif';
}
```

## 🔧 Débogage

### Logs

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs HTTP (si activé)
tail -f storage/logs/http-logger.log
```

### Debug bar

En développement, Laravel Debugbar est disponible :
```env
DEBUGBAR_ENABLED=true
```

## 🤝 Contribution

### Workflow

1. Créer une branche
2. Implémenter les changements (suivre l'architecture hexagonale)
3. Écrire/mettre à jour les tests
4. Créer une Pull Request vers `develop`

### Checklist avant PR

- [ ] Tests passent (`php artisan test`)
- [ ] Code suit les conventions PSR-12
- [ ] Architecture hexagonale respectée
- [ ] Documentation mise à jour si nécessaire
- [ ] Pas de secrets dans le code

## 📚 Ressources

- [Documentation Laravel 12](https://laravel.com/docs/12.x)
- [Typst Documentation](https://typst.app/docs)
- [PDFTK Documentation](https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/)

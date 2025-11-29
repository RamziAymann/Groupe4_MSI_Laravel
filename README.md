# 📚 Application RESTful Laravel avec ETL

Application RESTful développée avec Laravel permettant la gestion de clients avec un système ETL (Extract, Transform, Load) pour la synchronisation entre deux bases de données.

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [API Endpoints](#api-endpoints)
- [Tests](#tests)
- [Architecture](#architecture)

## ✨ Fonctionnalités

- ✅ API RESTful complète pour la gestion de clients (CRUD)
- ✅ Système ETL pour synchroniser deux bases de données
- ✅ File d'attente Laravel pour le traitement asynchrone
- ✅ Validation des données
- ✅ Recherche de clients
- ✅ Filtrage par statut
- ✅ Pagination des résultats
- ✅ Transformation et nettoyage des données

## 🔧 Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Extension PHP : PDO, mbstring, openssl, json

## 📥 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd laravel-etl-api
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Copier le fichier d'environnement

```bash
cp .env.example .env
```

### 4. Générer la clé d'application

```bash
php artisan key:generate
```

## ⚙️ Configuration

### 1. Configuration des bases de données

Modifiez le fichier `.env` :

```env
# Base de données principale (cible)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_target
DB_USERNAME=root
DB_PASSWORD=

# Base de données source
DB_SOURCE_HOST=127.0.0.1
DB_SOURCE_PORT=3306
DB_SOURCE_DATABASE=laravel_source
DB_SOURCE_USERNAME=root
DB_SOURCE_PASSWORD=

# Configuration Queue
QUEUE_CONNECTION=database
```

### 2. Créer les bases de données

Connectez-vous à MySQL et exécutez :

```sql
CREATE DATABASE IF NOT EXISTS laravel_target CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS laravel_source CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Exécuter les migrations

```bash
# Créer la table des jobs
php artisan queue:table

# Migrer la base cible
php artisan migrate

# Migrer la base source
php artisan migrate --database=source
```

### 4. Nettoyer le cache

```bash
php artisan optimize:clear
```

## 🚀 Utilisation

### Démarrage de l'application

Vous devez lancer **3 terminaux** simultanément :

#### Terminal 1 : Serveur Laravel

```bash
php artisan serve
```

L'application sera accessible sur : `http://localhost:8000`

#### Terminal 2 : Queue Worker

```bash
php artisan queue:work --tries=3
```

Le worker traite les jobs de synchronisation en arrière-plan.

#### Terminal 3 : Commandes et tests

Ce terminal sert pour exécuter les commandes ETL et les tests.

### Insérer des données de test dans la base source

```bash
php artisan tinker
```

Dans Tinker :

```php
DB::connection('source')->table('clients')->insert([
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'email' => 'jean.dupont@test.com',
    'telephone' => '0612345678',
    'ville' => 'Paris',
    'statut' => 'actif',
    'created_at' => now(),
    'updated_at' => now()
]);

DB::connection('source')->table('clients')->insert([
    'nom' => 'Martin',
    'prenom' => 'Sophie',
    'email' => 'sophie.martin@test.com',
    'telephone' => '0698765432',
    'ville' => 'Lyon',
    'statut' => 'actif',
    'created_at' => now(),
    'updated_at' => now()
]);

exit
```

### Exécuter le processus ETL

```bash
php artisan etl:run
```

Cette commande va :
1. Extraire les clients de la base source
2. Transformer les données (nettoyage, formatage)
3. Charger les données dans la base cible via la queue

## 📡 API Endpoints

### Base URL

```
http://localhost:8000/api/v1
```

### Endpoints disponibles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/clients` | Liste tous les clients |
| POST | `/clients` | Crée un nouveau client |
| GET | `/clients/{id}` | Récupère un client spécifique |
| PUT | `/clients/{id}` | Met à jour un client |
| DELETE | `/clients/{id}` | Supprime un client |
| GET | `/clients/search?q={query}` | Recherche des clients |

### Paramètres de requête

- `per_page` : Nombre de résultats par page (défaut: 15)
- `statut` : Filtrer par statut (actif/inactif)
- `q` : Terme de recherche

### Exemples de requêtes

#### 1. Créer un client

```bash
POST /api/v1/clients
Content-Type: application/json

{
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@example.com",
    "telephone": "0612345678",
    "ville": "Paris",
    "code_postal": "75001",
    "date_naissance": "1990-05-15",
    "statut": "actif"
}
```

#### 2. Lister les clients

```bash
GET /api/v1/clients
```

#### 3. Récupérer un client

```bash
GET /api/v1/clients/1
```

#### 4. Mettre à jour un client

```bash
PUT /api/v1/clients/1
Content-Type: application/json

{
    "telephone": "0687654321",
    "statut": "inactif"
}
```

#### 5. Rechercher des clients

```bash
GET /api/v1/clients/search?q=Dupont
```

#### 6. Supprimer un client

```bash
DELETE /api/v1/clients/1
```

## 🧪 Tests

### Tests avec PowerShell

#### Test simple

```powershell
# Créer un client
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/clients" -Method POST -Body '{"nom":"Test","prenom":"User","email":"test@example.com","telephone":"0612345678","ville":"Paris","statut":"actif"}' -ContentType "application/json"

# Lister les clients
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/clients" -Method GET
```

#### Script de test complet

Créez un fichier `test_api.ps1` :

```powershell
# Configuration
$baseUrl = "http://localhost:8000/api/v1"

Write-Host "=== Test de l'API ===" -ForegroundColor Cyan

# 1. Créer un client
Write-Host "`n[1] Création d'un client..." -ForegroundColor Green
$client = @{
    nom = "Dupont"
    prenom = "Jean"
    email = "jean.dupont@test.com"
    telephone = "0612345678"
    ville = "Paris"
    statut = "actif"
} | ConvertTo-Json

try {
    $result = Invoke-RestMethod -Uri "$baseUrl/clients" -Method POST -Body $client -ContentType "application/json"
    Write-Host "✓ Client créé (ID: $($result.data.id))" -ForegroundColor Green
    $clientId = $result.data.id
} catch {
    Write-Host "✗ Erreur: $_" -ForegroundColor Red
    exit
}

# 2. Lister les clients
Write-Host "`n[2] Liste des clients..." -ForegroundColor Green
$clients = Invoke-RestMethod -Uri "$baseUrl/clients" -Method GET
Write-Host "✓ Total: $($clients.data.total) clients" -ForegroundColor Green

# 3. Récupérer le client
Write-Host "`n[3] Récupération du client $clientId..." -ForegroundColor Green
$client = Invoke-RestMethod -Uri "$baseUrl/clients/$clientId" -Method GET
Write-Host "✓ Client: $($client.data.prenom) $($client.data.nom)" -ForegroundColor Green

# 4. Mettre à jour
Write-Host "`n[4] Mise à jour du client..." -ForegroundColor Green
$update = @{
    telephone = "0700000000"
    statut = "inactif"
} | ConvertTo-Json

$updated = Invoke-RestMethod -Uri "$baseUrl/clients/$clientId" -Method PUT -Body $update -ContentType "application/json"
Write-Host "✓ Client mis à jour" -ForegroundColor Green

# 5. Rechercher
Write-Host "`n[5] Recherche..." -ForegroundColor Green
$search = Invoke-RestMethod -Uri "$baseUrl/clients/search?q=Dupont" -Method GET
Write-Host "✓ Résultats: $($search.data.total)" -ForegroundColor Green

Write-Host "`n=== Tests terminés ===" -ForegroundColor Cyan
```

Exécutez :

```powershell
.\test_api.ps1
```

### Tests avec VSCode REST Client

Installez l'extension **REST Client** dans VSCode, puis créez `test_api.http` :

```http
### Variables
@baseUrl = http://localhost:8000/api/v1

### 1. Créer un client
POST {{baseUrl}}/clients
Content-Type: application/json

{
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@example.com",
    "telephone": "0612345678",
    "ville": "Paris",
    "statut": "actif"
}

### 2. Lister tous les clients
GET {{baseUrl}}/clients

### 3. Récupérer un client (ID = 1)
GET {{baseUrl}}/clients/1

### 4. Mettre à jour un client
PUT {{baseUrl}}/clients/1
Content-Type: application/json

{
    "telephone": "0687654321",
    "statut": "inactif"
}

### 5. Rechercher des clients
GET {{baseUrl}}/clients/search?q=Dupont

### 6. Supprimer un client
DELETE {{baseUrl}}/clients/1
```

Cliquez sur **Send Request** pour tester chaque endpoint.

### Tests avec Postman

1. Importez la collection depuis le fichier `postman_collection.json`
2. Ou créez manuellement les requêtes selon les exemples ci-dessus

## 🏗️ Architecture

### Structure du projet

```
laravel-etl-api/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── RunETLProcess.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           └── ClientController.php
│   ├── Jobs/
│   │   └── SyncClientJob.php
│   ├── Models/
│   │   └── Client.php
│   └── Services/
│       ├── ETLService.php
│       └── QueueService.php
├── config/
│   └── database.php
├── database/
│   └── migrations/
│       └── xxxx_create_clients_table.php
├── routes/
│   └── api.php
└── .env
```

### Flux ETL

1. **Extract** : Extraction des données de la base source
2. **Transform** : 
   - Normalisation des noms (majuscules)
   - Normalisation des prénoms (première lettre majuscule)
   - Formatage des numéros de téléphone
   - Validation et nettoyage des données
3. **Load** : Chargement dans la queue pour insertion dans la base cible

### Modèle de données Client

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Identifiant unique |
| nom | string | Nom du client |
| prenom | string | Prénom du client |
| email | string | Email (unique) |
| telephone | string | Numéro de téléphone |
| adresse | string | Adresse postale |
| ville | string | Ville |
| code_postal | string | Code postal |
| date_naissance | date | Date de naissance |
| statut | enum | actif ou inactif |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de mise à jour |

## 📝 Commandes artisan

| Commande | Description |
|----------|-------------|
| `php artisan serve` | Démarre le serveur de développement |
| `php artisan migrate` | Exécute les migrations (base cible) |
| `php artisan migrate --database=source` | Exécute les migrations (base source) |
| `php artisan queue:work` | Démarre le worker de queue |
| `php artisan etl:run` | Exécute le processus ETL |
| `php artisan route:list` | Liste toutes les routes |
| `php artisan optimize:clear` | Nettoie tous les caches |

## 🐛 Dépannage

### Les routes API ne fonctionnent pas (404)

```bash
php artisan optimize:clear
php artisan route:list
```

Vérifiez que les routes API sont bien listées.

### Les jobs ne sont pas traités

Assurez-vous que le queue worker est démarré :

```bash
php artisan queue:work
```

### Erreur de connexion à la base de données

Vérifiez les identifiants dans `.env` et que les bases de données existent :

```sql
SHOW DATABASES;
```

### Les migrations échouent

```bash
php artisan config:clear
php artisan migrate:fresh
```

## 📚 Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)

## 👥 Auteurs

Projet réalisé par le groupe 4 dans le cadre du Master SI - Développement à base de composants
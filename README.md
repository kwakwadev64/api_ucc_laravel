

````md
# UCC API - Backend Laravel

## 📌 Description

Backend API de l'application universitaire UCC.

Cette API est développée avec :

- Laravel
- Laravel Sanctum pour l'authentification API
- SQLite/MySQL pour la base de données
- Filament pour l'administration
- Mailtrap pour les tests d'envoi d'emails

Le backend est prévu pour être consommé par une application mobile React Native.

L'API permet de gérer :

- Authentification des utilisateurs
- Facultés
- Programmes / Filières
- Promotions
- Années académiques
- Cours
- Documents pédagogiques
- Horaires des cours
- Horaires des examens


---

# ⚙️ Installation

## Prérequis

- PHP >= 8.3
- Composer
- SQLite/MySQL


## Installation des dépendances

```bash
composer install
````

## Configuration environnement

Copier le fichier `.env.example`

```bash
cp .env.example .env
```

Générer la clé Laravel :

```bash
php artisan key:generate
```

Configurer la base de données dans `.env` :

Exemple SQLite :

```env
DB_CONNECTION=sqlite
```

Créer la base :

```bash
touch database/database.sqlite
```

Lancer les migrations :

```bash
php artisan migrate
```

Créer le lien storage :

```bash
php artisan storage:link
```

Démarrer le serveur :

```bash
php artisan serve
```

---

# 📧 Configuration Email

Les emails sont utilisés pour :

* Réinitialisation du mot de passe
* Vérification d'adresse email

Configuration Mailtrap :

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password

MAIL_FROM_ADDRESS=noreply@ucc.cd
MAIL_FROM_NAME="UCC"
```

Configuration frontend :

```env
FRONTEND_URL=ucc://auth
```

Cette URL permet de générer des liens compatibles React Native.

---

# 🔐 Authentification API

Toutes les routes API utilisent :

```
/api
```

Le système utilise :

* Laravel Sanctum
* Token Bearer
* Middleware auth:sanctum

Header pour les routes protégées :

```
Authorization: Bearer TOKEN
```

---

# 👤 Gestion des utilisateurs

Les utilisateurs possèdent :

| Champ            | Description      |
| ---------------- | ---------------- |
| first_name       | Prénom           |
| last_name        | Nom              |
| email            | Email            |
| phone            | Téléphone        |
| role             | Rôle             |
| faculty_id       | Faculté          |
| promotion_id     | Promotion        |
| academic_year_id | Année académique |

Rôles disponibles :

```
student
teacher
cp
faculty_admin
super_admin
```

---

# 🔑 Module Authentification

Le système permet :

* Création de compte utilisateur
* Connexion utilisateur
* Déconnexion utilisateur
* Récupération utilisateur connecté
* Réinitialisation du mot de passe
* Vérification email
* Gestion des tokens API avec Sanctum

---

# 1. Inscription

## Endpoint

```
POST /api/register
```

## Body JSON

```json
{
    "first_name": "Franck",
    "last_name": "Kapula",
    "email": "franck@test.com",
    "phone": "0812345678",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "student",
    "faculty_id": 4,
    "promotion_id": 3,
    "academic_year_id": 1
}
```

---

# 2. Connexion

## Endpoint

```
POST /api/login
```

Body :

```json
{
    "email":"franck@test.com",
    "password":"password123"
}
```

Réponse :

```json
{
    "success":true,
    "token":"2|xxxxxxxx",
    "user":{
        "id":1,
        "email":"franck@test.com"
    }
}
```

---

# 3. Utilisateur connecté

```
GET /api/me
```

Header :

```
Authorization: Bearer TOKEN
```

---

# 4. Déconnexion

```
POST /api/logout
```

---

# 5. Mot de passe oublié

```
POST /api/forgot-password
```

Body :

```json
{
    "email":"franck@test.com"
}
```

---

# 6. Réinitialisation mot de passe

```
POST /api/reset-password
```

Body :

```json
{
    "token":"token",
    "email":"franck@test.com",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
}
```

---

# 🎓 Données académiques

## Liste facultés, programmes et promotions

Endpoint :

```
GET /api/register-options
```

Retourne :

* Facultés
* Programmes
* Promotions

---

# 📚 Module Cours

Le module Cours permet de gérer les ressources pédagogiques.

Fonctionnalités :

* Création d'un cours
* Association à un enseignant
* Association à une promotion
* Ajout d'un document
* Publication d'un cours
* Consultation par les étudiants

## Structure d'un cours

| Champ            | Description      |
| ---------------- | ---------------- |
| id               | Identifiant      |
| teacher_id       | Enseignant       |
| promotion_id     | Promotion        |
| academic_year_id | Année académique |
| title            | Titre            |
| description      | Description      |
| file_path        | Document         |
| file_type        | Type fichier     |
| is_published     | Publication      |

---

# API Cours

## Liste des cours

```
GET /api/courses
```

Header :

```
Authorization: Bearer TOKEN
```

---

## Ajouter un cours

```
POST /api/courses
```

Type :

```
multipart/form-data
```

Exemple :

```
teacher_id=5

promotion_id=3

academic_year_id=1

title=Programmation Web

description=Cours Laravel

file=document.pdf
```

---

## Modifier un cours

```
PUT /api/courses/{id}
```

---

## Supprimer un cours

```
DELETE /api/courses/{id}
```

---

# 🕒 Module Horaires

Le module horaire gère les emplois du temps universitaires.

Deux types existent :

* Horaires des cours
* Horaires des examens

Le type est défini automatiquement par le backend.

L'utilisateur ne choisit jamais le type.

---

# Structure d'un horaire

| Champ            | Description      |
| ---------------- | ---------------- |
| id               | Identifiant      |
| faculty_id       | Faculté          |
| promotion_id     | Promotion        |
| program_id       | Filière          |
| academic_year_id | Année académique |
| type             | course ou exam   |
| title            | Titre            |
| file_path        | Document         |
| file_type        | Extension        |
| is_active        | Etat             |
| uploaded_by      | Auteur           |

---

# Règles métier des horaires

Un seul horaire actif existe pour une même combinaison :

* Faculté
* Année académique
* Type
* Promotion
* Programme

Si un nouvel horaire possède les mêmes caractéristiques :

* L'ancien passe à `is_active=false`
* Le nouveau devient actif

---

# Horaires généraux

Un horaire peut concerner toute une faculté.

Dans ce cas :

```
promotion_id = null

program_id = null
```

Exemple :

```
Faculté : Sciences Informatiques

Année : 2025-2026

Type : cours

Promotion : toutes

Programme : toutes
```

---



# 🛡️ Sécurité

Le système utilise :

* Laravel Sanctum
* Hashage sécurisé des mots de passe
* Validation Laravel Request
* Policies Laravel
* Protection auth:sanctum
* Gestion des permissions
* Expiration des liens email

---

# 📂 Structure principale

```
app

├── Http

│   ├── Controllers

│   ├── Requests

│   └── Resources


├── Models


├── Services


├── Policies


└── Filament
```

---

# 🧪 Tests

Tester les routes avec :

* Postman
* Insomnia
* Curl

Ordre conseillé :

1. Register
2. Login
3. Copier le token
4. Tester /me
5. Tester les cours
6. Tester les horaires cours
7. Tester les horaires examens
8. Logout
9. Forgot password
10. Reset password

---

# Version

```
UCC API v1.0

Laravel + Sanctum + Filament
```

```
```

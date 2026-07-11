# UCC API - Backend Laravel

## 📌 Description

Backend API de l'application universitaire UCC.

Cette API est développée avec :

- Laravel
- Laravel Sanctum pour l'authentification API
- SQLite/MySQL pour la base de données
- Mailtrap pour les tests d'envoi d'emails

Le backend est prévu pour être consommé par une application mobile React Native.

---

# 🔐 Module Authentification

Le système d'authentification permet de gérer :

- Création de compte utilisateur
- Connexion utilisateur
- Déconnexion utilisateur
- Récupération de l'utilisateur connecté
- Réinitialisation du mot de passe
- Vérification d'adresse email
- Gestion des tokens API avec Sanctum


---

# ⚙️ Installation

## Prérequis

- PHP >= 8.3
- Composer
- SQLite/MySQL


## Installation des dépendances

```bash
composer install
```


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


Démarrer le serveur :

```bash
php artisan serve
```


---

# 📧 Configuration Email

Les emails sont utilisés pour :

- Réinitialisation du mot de passe
- Vérification d'adresse email


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

# 🔑 Authentification API

Toutes les routes API utilisent :

```
/api
```


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
    "faculty_id": 1,
    "promotion_id": 1
}
```


---

# 2. Connexion

## Endpoint

```
POST /api/login
```


## Body JSON

```json
{
    "email": "franck@test.com",
    "password": "password123"
}
```


## Réponse

```json
{
    "success": true,
    "token": "2|xxxxxxxxxxxx",
    "user": {
        "id":1,
        "email":"franck@test.com"
    }
}
```


Le token obtenu doit être envoyé dans les routes protégées :

```
Authorization: Bearer TOKEN
```


---

# 3. Utilisateur connecté

## Endpoint

```
GET /api/me
```


## Header

```
Authorization: Bearer TOKEN
```


Réponse :

```json
{
    "id":1,
    "first_name":"Franck",
    "last_name":"Kapula",
    "email":"franck@test.com"
}
```


---

# 4. Déconnexion

## Endpoint

```
POST /api/logout
```


Header :

```
Authorization: Bearer TOKEN
```


---

# 5. Mot de passe oublié


## Endpoint

```
POST /api/forgot-password
```


Body :

```json
{
    "email":"franck@test.com"
}
```


Réponse :

```json
{
    "success":true,
    "message":"Un lien de réinitialisation a été envoyé à votre adresse e-mail."
}
```


---

# 6. Réinitialisation du mot de passe


## Endpoint

```
POST /api/reset-password
```


Body :

```json
{
    "token":"token_recu_par_email",
    "email":"franck@test.com",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
}
```


Réponse :

```json
{
    "success":true,
    "message":"Votre mot de passe a été réinitialisé avec succès."
}
```

6. Liste de facultés, filières, et promotion

GET  api/register-options


Réponse :

```json
---

{
    "faculties": [
        {
            "id": 1,
            "name": "Faculté d’Économie et Développement"
        },
        {
            "id": 2,
            "name": "Faculté de Droit"
        },
        {
            "id": 3,
            "name": "Faculté de Médecine"
        },
        {
            "id": 4,
            "name": "Faculté de Sciences Informatiques"
        },
        {
            "id": 5,
            "name": "Faculté des Sciences Politiques"
        },
        {
            "id": 6,
            "name": "Faculté des Communications Sociales"
        },
        {
            "id": 7,
            "name": "Faculté de Théologie"
        },
        {
            "id": 8,
            "name": "Faculté de Droit Canonique"
        },
        {
            "id": 9,
            "name": "Faculté de Philosophie"
        }
    ],
    "programs": [
        {
            "id": 1,
            "name": "Conception des systemes d’information",
            "faculty_id": 4
        },
        {
            "id": 2,
            "name": "Réseaux informatiques",
            "faculty_id": 4
        }
    ],
    "promotions": [
        {
            "id": 1,
            "level": "L1",
            "program_id": null
        },
        {
            "id": 2,
            "level": "L2",
            "program_id": null
        },
        {
            "id": 3,
            "level": "L3",
            "program_id": null
        },
        {
            "id": 4,
            "level": "M1",
            "program_id": 1
        },
        {
            "id": 5,
            "level": "M2",
            "program_id": 1
        },
        {
            "id": 6,
            "level": "M1",
            "program_id": 2
        },
        {
            "id": 7,
            "level": "M2",
            "program_id": 2
        }
    ]
}
# 🛡️ Sécurité

Le système utilise :

- Laravel Sanctum
- Hashage sécurisé des mots de passe
- Validation Laravel
- Tokens API
- Protection des routes avec middleware auth:sanctum
- Expiration des liens email


---

# 📂 Structure Authentification


```
app
├── Http
│   ├── Controllers
│   │   └── Auth
│   │       ├── RegisteredUserController.php
│   │       ├── AuthenticatedSessionController.php
│   │       ├── PasswordResetLinkController.php
│   │       └── NewPasswordController.php
│   │
│   └── Requests
│       └── Auth
│           └── LoginRequest.php
│
└── Providers
    └── AppServiceProvider.php
```


---

# 🧪 Tests

Tester les routes avec :

- Postman
- Insomnia
- Curl


Ordre conseillé :

1. Register
2. Login
3. Copier le token
4. Tester `/me`
5. Logout
6. Forgot password
7. Reset password


---

# Version

```
Authentication API v1.0
Laravel + Sanctum
```

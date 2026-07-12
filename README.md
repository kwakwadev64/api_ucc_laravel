

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
Voici la version complète avec les endpoints **GET liste, GET détail (show), PUT modification et DELETE suppression** ajoutés pour les horaires des cours et des examens.

```md
# 📅 Horaires des cours

Les horaires des cours permettent de publier les emplois du temps liés aux enseignements.

Le système utilise la table :

```

schedules

```

La distinction entre cours et examens est faite avec le champ :

```

type

```

Pour les horaires de cours :

```

type = course

```

---

# Liste des horaires des cours

## Endpoint

```

GET /api/course-schedules

```

## Authentification

Header :

```

Authorization: Bearer TOKEN

````

## Description

Retourne uniquement les horaires ayant :

```json
{
    "type": "course",
    "is_active": true
}
````

Le filtrage dépend du rôle :

### Étudiant

Retourne :

* sa faculté
* son année académique
* sa promotion
* son programme
* les horaires généraux de sa faculté

### Administrateur / CP / Enseignant

Retourne les horaires de cours accessibles.

---

# Création d'un horaire de cours

## Endpoint

```
POST /api/course-schedules
```

## Authentification

```
Authorization: Bearer TOKEN
```

## Type de requête

```
multipart/form-data
```

## Données envoyées

```
faculty_id

academic_year_id

promotion_id (optionnel)

program_id (optionnel)

title

file
```

## Exemple

```
faculty_id = 4

academic_year_id = 1

promotion_id = 3

program_id = null

title = Horaire des cours L3

file = horaire.pdf
```

## Traitement automatique du backend

Le backend ajoute automatiquement :

```json
{
    "type": "course",
    "uploaded_by": "utilisateur connecté",
    "file_type": "extension du fichier",
    "is_active": true
}
```

Le type n'est jamais envoyé par le client.

---

# Afficher un horaire de cours

## Endpoint

```
GET /api/schedules/{schedule}
```

## Exemple

```
GET /api/schedules/2
```

## Réponse

Retourne les informations complètes :

```json
{
    "id": 2,
    "title": "Horaire des cours L3",
    "type": "course",
    "faculty": {},
    "promotion": {},
    "program": {},
    "academic_year": {},
    "file_url": "...",
    "is_active": true
}
```

---

# Modifier un horaire de cours

## Endpoint

```
PUT /api/schedules/{schedule}
```

## Exemple

```
PUT /api/schedules/2
```

## Données modifiables

```json
{
    "title": "Nouvel horaire L3",
    "promotion_id": 3,
    "program_id": 1
}
```

Le backend conserve :

* le type
* l'utilisateur créateur
* l'historique

---

# Supprimer un horaire de cours

## Endpoint

```
DELETE /api/schedules/{schedule}
```

## Exemple

```
DELETE /api/schedules/2
```

Supprime :

* l'enregistrement dans la base de données
* le fichier physique associé

---

# 📝 Horaires des examens

Les horaires d'examens utilisent la même table :

```
schedules
```

avec :

```
type = exam
```

---

# Liste des horaires d'examens

## Endpoint

```
GET /api/exam-schedules
```

## Authentification

```
Authorization: Bearer TOKEN
```

## Description

Retourne uniquement :

```json
{
    "type": "exam",
    "is_active": true
}
```

---

# Création d'un horaire d'examen

## Endpoint

```
POST /api/exam-schedules
```

## Authentification

```
Authorization: Bearer TOKEN
```

## Type de requête

```
multipart/form-data
```

## Données envoyées

```
faculty_id

academic_year_id

promotion_id (optionnel)

program_id (optionnel)

title

file
```

## Exemple

```
faculty_id = 4

academic_year_id = 1

promotion_id = 3

program_id = null

title = Horaire des examens L3

file = examens.pdf
```

## Traitement automatique du backend

Le backend ajoute automatiquement :

```json
{
    "type": "exam",
    "uploaded_by": "utilisateur connecté",
    "file_type": "extension du fichier",
    "is_active": true
}
```

Le type examen est imposé par le backend.

---

# Afficher un horaire d'examen

## Endpoint

```
GET /api/schedules/{schedule}
```

## Exemple

```
GET /api/schedules/5
```

Retourne uniquement l'horaire demandé.

---

# Modifier un horaire d'examen

## Endpoint

```
PUT /api/schedules/{schedule}
```

## Exemple

```
PUT /api/schedules/5
```

## Données possibles

```json
{
    "title": "Horaire examen session principale",
    "promotion_id": 3,
    "program_id": null
}
```

---

# Supprimer un horaire d'examen

## Endpoint

```
DELETE /api/schedules/{schedule}
```

## Exemple

```
DELETE /api/schedules/5
```

Supprime :

* l'horaire
* le fichier associé

---

# 🔄 Gestion des doublons

Le système empêche plusieurs horaires actifs ayant le même contexte.

Un horaire est considéré comme identique si :

```
faculty_id

+

academic_year_id

+

type

+

promotion_id

+

program_id
```

Exemple :

```
Faculté : Sciences Informatiques

Année : 2025-2026

Type : course

Promotion : L3

Programme : CSI
```

Si un nouvel horaire avec les mêmes caractéristiques est ajouté :

Ancien :

```
is_active = false
```

Nouveau :

```
is_active = true
```

Cela permet de conserver l'historique tout en affichant uniquement la dernière version active.

---

# 🛠️ Administration Filament

L'administration utilise deux ressources séparées :

```
Horaires

├── Horaires des cours

└── Horaires des examens
```

Les deux ressources utilisent le même modèle :

```
App\Models\Schedule
```

et la même table :

```
schedules
```

La séparation est faite grâce au champ :

```
type
```

---

## Ressource horaires des cours

```
CourseScheduleResource
```

Filtre automatiquement :

```
type = course
```

---

## Ressource horaires des examens

```
ExamScheduleResource
```

Filtre automatiquement :

```
type = exam
```

Ainsi l'administrateur voit deux menus différents alors que les données restent centralisées.

```

Cette version correspond exactement à tes routes actuelles :

- `GET /api/course-schedules`
- `POST /api/course-schedules`
- `GET /api/exam-schedules`
- `POST /api/exam-schedules`
- `GET /api/schedules/{schedule}`
- `PUT /api/schedules/{schedule}`
- `DELETE /api/schedules/{schedule}`

Elle est prête à remplacer ta section dans le README.
```



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

# Evolyx

Evolyx est une application web de suivi sportif permettant de créer des séances d’entraînement, enregistrer des performances, suivre son évolution physique et centraliser ses activités sportives.

---

## Table des matières

- [Evolyx](#evolyx)
  - [Table des matières](#table-des-matières)
  - [Stack technique](#stack-technique)
    - [Backend](#backend)
    - [Frontend](#frontend)
    - [Base de données](#base-de-données)
    - [Infrastructure](#infrastructure)
  - [Fonctionnalités](#fonctionnalités)
  - [Prérequis](#prérequis)
- [Installation locale (Docker)](#installation-locale-docker)
  - [1. Cloner le projet](#1-cloner-le-projet)
  - [2. Créer le fichier d’environnement](#2-créer-le-fichier-denvironnement)
  - [3. Première installation](#3-première-installation)
- [Développement quotidien](#développement-quotidien)
- [Commandes utiles](#commandes-utiles)
- [Configuration base de données (local)](#configuration-base-de-données-local)
- [Premier déploiement serveur](#premier-déploiement-serveur)
  - [1. Créer le fichier `.env`](#1-créer-le-fichier-env)
  - [2. Générer la clé Laravel](#2-générer-la-clé-laravel)
  - [3. Lancer les migrations](#3-lancer-les-migrations)
  - [4. Optimiser Laravel](#4-optimiser-laravel)
- [Déploiement continu](#déploiement-continu)
- [Licence](#licence)

---

## Stack technique

### Backend

* PHP 8.4
* Laravel 12
* Inertia.js

### Frontend

* Vue 3
* TypeScript
* Vite
* Tailwind CSS

### Base de données

* SQLite (développement)
* MySQL (préproduction / production)

### Infrastructure

* Docker
* GitHub Actions
* FTPS (o2switch)

---

## Fonctionnalités

* Création de séances personnalisées
* Catalogue d’exercices multi-sports
* Enregistrement de performances détaillées
* Suivi de progression
* Objectifs de poids et calories
* Suivi des macronutriments
* Gestion des abonnements
* Système communautaire

---

## Prérequis

Avant de lancer le projet, assurez-vous d’avoir installé :

* Docker
* Docker Compose
* Node.js (version 22 recommandée)
* npm

Vérifier l’installation :

```bash
docker --version
docker compose version
node -v
npm -v
```

---

# Installation locale (Docker)

## 1. Cloner le projet

```bash
git clone <url-du-repo>
cd evolyx-my-digital-project
```

---

## 2. Créer le fichier d’environnement

Windows :

```powershell
Copy-Item .env.example .env
```

Linux / macOS :

```bash
cp .env.example .env
```

---

## 3. Première installation

```bash
npm run docker:install
```

Cette commande :

* construit l’image Docker
* démarre le conteneur
* installe Composer
* installe npm
* génère la clé Laravel
* crée la base SQLite
* lance les migrations
* injecte les seeders

---

# Développement quotidien

> Sur Windows, Docker Desktop doit être lancé avant toute commande Docker.

Lancer l’environnement :

```bash
npm run dev:docker
```

Application :

```text
http://localhost:8000
```

Serveur Vite :

```text
http://localhost:5173
```

Grâce au volume Docker, toutes les modifications locales sont automatiquement synchronisées dans le conteneur.

---

# Commandes utiles

Démarrer Docker :

```bash
npm run docker:up
```

Arrêter Docker :

```bash
npm run docker:down
```

Reconstruire Docker :

```bash
npm run docker:build
```

Entrer dans le conteneur :

```bash
npm run docker:shell
```

Lancer les migrations :

```bash
npm run docker:migrate
```

Lancer les seeders :

```bash
npm run docker:seed
```

---

# Configuration base de données (local)

Variables :

```env
DB_CONNECTION=sqlite
```

Fichier utilisé :

```text
database/database.sqlite
```

---

# Premier déploiement serveur

Lors du premier déploiement sur préproduction ou production :

## 1. Créer le fichier `.env`

```bash
cp .env.example .env
```

Configurer :

```env
APP_NAME=Evolyx
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.fr

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

---

## 2. Générer la clé Laravel

```bash
php artisan key:generate
```

---

## 3. Lancer les migrations

```bash
php artisan migrate --force
```

---

## 4. Optimiser Laravel

```bash
php artisan optimize
```

---

# Déploiement continu

Le projet utilise GitHub Actions.

À chaque push sur :

```text
develop
```

Le pipeline :

* récupère le code
* installe les dépendances PHP
* installe les dépendances frontend
* build les assets Vite
* déploie automatiquement via FTPS sur o2switch

Le fichier `.env` n’est jamais versionné ni écrasé.

---

# Licence

Projet pédagogique réalisé dans le cadre du titre professionnel CDA (Concepteur Développeur d’Applications).

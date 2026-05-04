# evolyx-my-digital-project

Projet fictif MyDigitalProject - Dashboard sportif - Creation de seance de sport, d'exercices et de performances.

## Lancement avec Docker

Le projet a deux configurations Docker :

- `docker-compose.yml` pour le local, avec SQLite.
- `docker-compose.prod.yml` pour la production, avec MySQL.

Les deux configurations chargent les variables depuis un fichier `.env`.

### Local

Creer le fichier d'environnement si besoin :

```powershell
Copy-Item .env.example .env
```

Construire l'image Docker :

```bash
docker compose build
```

Installer les dependances dans le conteneur :

```bash
docker compose run --rm --no-deps app composer install
docker compose run --rm --no-deps app npm install
docker compose run --rm --no-deps app npm run build
```

Lancer les conteneurs :

```bash
docker compose up -d
```

Initialiser Laravel et la base SQLite :

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed # Optionnel : a supprimer si tu veux une base vide
```

L'application est disponible sur :

```text
http://localhost:8000
```

Pour lancer Vite en developpement :

```bash
docker compose exec app npm run dev -- --host 0.0.0.0
```

Si le navigateur affiche une page blanche avec une erreur sur `localhost:5173` ou `[::1]:5173`, cela veut dire que Laravel cherche le serveur Vite de developpement. Relancer simplement l'application avec Docker supprime `public/hot` au demarrage et utilise le build genere dans `public/build`.

Variables de base de donnees utilisees par Docker en local :

```env
DB_CONNECTION=sqlite
# DB_DATABASE peut rester vide : Laravel utilisera database/database.sqlite
```

### Production

Creer ou generer le fichier d'environnement de production sur le serveur :

```bash
cp .env.production.example .env.production
```

Adapter au minimum :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ton-domaine.fr
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=evolyx
DB_USERNAME=evolyx
DB_PASSWORD=mot-de-passe-solide
MYSQL_ROOT_PASSWORD=autre-mot-de-passe-solide
```

Construire et lancer la production :

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml build
docker compose --env-file .env.production -f docker-compose.prod.yml up -d
```

Initialiser Laravel et MySQL en production :

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan key:generate
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate --force
```

En production, le code est copie dans l'image via `Dockerfile.prod`. Il n'y a pas de volume `.:/var/www/html`, pas de serveur Vite expose, et la base MySQL est stockee dans un volume Docker persistant.

Si le deploiement passe par GitHub Actions, le workflow peut generer `.env.production` a partir des GitHub Secrets avant d'executer les commandes Docker Compose.

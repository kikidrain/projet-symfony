# Crypto Backoffice - Interface d'Administration

### 1. Cloner ou télécharger le projet
git clone <url-du-projet> crypto-backoffice
cd crypto-backoffice

### 2. Installer les dépendances
composer install

# Modifier les paramètres dans .env si besoin

### 4. Initialiser la base de données

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de démonstration
php bin/console app:init-data

### 5. Lancer le serveur de développement
php -S localhost:8000 -t public
```

### Accéder à l'application
Ouvrez votre navigateur et allez sur : **http://localhost:8000**

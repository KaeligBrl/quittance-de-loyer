# QuittanceDeLoyer — Projet Symfony 7

Configuration initiale et commandes utiles :

- Installer les dépendances :

```bash
composer install
```

- Configurer la base de données : modifiez le fichier `.env.local` avec vos identifiants MySQL.

- Créer la base de données :

```bash
php bin/console doctrine:database:create
```

- Générer puis exécuter les migrations :

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

- Lancer le serveur de développement (avec Symfony CLI) :

```bash
symfony server:start
```

Si vous n'avez pas la Symfony CLI :

```bash
php -S 127.0.0.1:8000 -t public
```

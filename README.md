# AFTER Travel

SPA de gestion de voyages et paiements en Symfony + PHP avec rendu Twig.

## Démarrage

Installez PHP 8.2+ et Composer, puis lancez :

```bash
composer install
symfony server:start
```

Alternative simple :

```bash
php -S 127.0.0.1:8000 -t public
```

## Structure

- `src/Controller/DashboardController.php`
- `templates/dashboard/index.html.twig`
- `templates/dashboard/partials/`
- `public/assets/styles/app.css`
- `config/routes.yaml`

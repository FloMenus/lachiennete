#!/bin/sh
set -e

echo "==> Warm-up du cache..."
php bin/console cache:warmup

echo "==> Migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> Chargement des fixtures..."
php bin/console doctrine:fixtures:load --no-interaction

echo "==> Démarrage du serveur sur le port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} -t public

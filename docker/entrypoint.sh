#!/bin/bash
set -e

# Script d'entrée pour le container PHP

# Vérification de l'environnement
if [ "$APP_ENV" = "prod" ]; then
    echo "🚀 Démarrage en mode PRODUCTION"
    
    # Optimisations pour la production
    php bin/console cache:clear --env=prod --no-debug
    php bin/console cache:warmup --env=prod --no-debug
    
    # Vérification de la base de données
    php bin/console doctrine:migrations:status --no-interaction
    
else
    echo "🛠️ Démarrage en mode DÉVELOPPEMENT"
    
    # Installation automatique des dépendances si nécessaire
    if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
        echo "📦 Installation des dépendances Composer..."
        composer install --optimize-autoloader
    fi
    
    # Vérification des migrations
    php bin/console doctrine:migrations:status --no-interaction
fi

# Permissions finales
chown -R www-data:www-data /var/www/html/var /var/www/html/public/uploads 2>/dev/null || true

echo "✅ Initialisation terminée"

# Exécution de la commande fournie ou php-fpm par défaut
exec "${@:-php-fpm}"

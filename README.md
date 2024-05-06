# Session -  appli Symfony

Projet qui gère des sessions de formations pour les admin d'un centre de formation


## Mise en place 
Lors du téléchargement, en plus d'installer composer, scoop et symfony, mettre cette ligne dans l'invite de commande pour avoir le bon dossier 'vendor'

``` php
composer install
```

Pour l'installation **'authenticated_api'** le composant Lock doit être installé:

``` php
composer require symfony/lock
```

Pour plus d'info  
[rate-limiter-doc](https://symfony.com/doc/current/rate_limiter.html "rate-limiter-doc")
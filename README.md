# Session -  appli Symfony

Projet qui gère des sessions de formations pour les admin d'un centre de formation


## Mise en place 


1. **composer** : [composer](https://getcomposer.org/download/) 

2. **scoop** : dans le powershell
 
```
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser  
```

```
Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression
```

3. **symfony**

```
scoop install symfony-cli
```

Une fois le projet téléchargé, mettre cette ligne dans l'invité de commande pour avoir le bon dossier 'vendor'

``` php
composer install
```

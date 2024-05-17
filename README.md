# Session -  appli Symfony

Projet qui gère des sessions de formations pour les admin d'un centre de formation

## Rendu

![Liste des sessions](public\img\screen1.png)


![Détail d'une session](public\img\screen2.png)


### Mise en place 


1. **composer** : [composer](https://getcomposer.org/download/) 

2. **scoop** : dans le powershell
 
``` sh
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser  
```

``` sh
Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression
```

3. **symfony**

``` sh
scoop install symfony-cli
```

Une fois le projet téléchargé, mettre cette ligne dans l'invité de commande pour avoir le bon dossier 'vendor'

``` sh
composer install
```

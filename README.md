### Projet 7 - présenter des API pour le client BileMO

### INSTALLATION
Pour installer le projet, vous devez utiliser composer en exécutant la commande suivante :
    * composer install

### CREATION BASE DE DONNEES
Pour créer la base de données, il faut exécuter les commandes suivantes :
    * php bin/console doctrine:database:create
    * php bin/console doctrine:migrations:migrate

Les informations relatives à la base de données sont dans le fichier .env à la racine du projet.

Vous pouvez ajouter un faux jeu de données avec la commande suivante :
    * php bin/console doctrine:fixtures:load

### DOCUMENTATION DE L'API
La documentation est générée par la librairie Nelmio. Vous y avez accès :
https://127.0.0.1:8000/api/doc

#### QUALITE DU CODE
Vous pouvez retrouver une analyse de la qualité du code sur Symfony Insight : 
https://insight.symfony.com/projects/257d8db1-c284-4446-82ad-61ae649ce402


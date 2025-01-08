# php_project
### Voici notre site web qui permet à des patients de prendre rendez-vous avec des médecins. Les médecins peuvent également gérer leurs rendez-vous sur cette plateforme.
## Installation :
Il faut que vous installiez sur votre wsl (linux) postgresql, apache2 et le module php.
Mettez à jour les paquets sur votre ordinateur.
`sudo apt update`
`sudo apt upgrade`

**APACHE2**
`sudo apt install apache2`
Démarrer apache2 : `sudo service apache2 start`

**Module PHP**
`sudo apt install php libapache2-mod-php`

**Postgresql**

Téléchargement :
`sudo apt install postgresql`
`sudo service postgresql start`

Modification du mot de passe :
`sudo passwd postgres`
`su - postgres`
`psql -c "alter user postgres with password 'StrongAdminP@ssw0rd'"`
`exit`

Redémarrer :
`sudo service postgresql restart`

## Utilisation :

Se connecter à la base de données en tant qu'administrateur :

`sudo -u postgres psql`
`create database projet_doct`
`\c projet_doct`

Dézippez le dossier dans le dossier html dans le dossier debian de votre explorateur de fichiers. (\\wsl.localhost\Debian\var\www\html\)
Maintenant vous pouvez exécuter à l'aide de VS Code le fichier tables.sql dans la BDD projet_doct.
Puis exécuter le fichier data.sql pour ajouter des données dans la BDD.

Vous pouvez regarder les données dans la BDD si vous le souhaitez.`\d` pour voir toutes les tables dans le terminal.
Puis `SELECT * from table;` avec table le nom de la table que vous souhaitez explorer.

Ensuite ouvrez votre navigateur et tapez l'url *http://localhost/nomdudossier/html/accueil.html* (nomdudossier étant le dossier dans lequel vous avez mis le projet après le dossier html)

Vous voilà sur notre site web !

Vous pouvez vous inscrire, en tant que patient ou médecin puis vous connecter pour accéder à toutes les fonctionnalités.


Voici le lien de notre répertoire GitHub : https://github.com/TallulahDrn/php_project.git



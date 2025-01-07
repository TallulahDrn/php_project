# php_project
### Voici notre site web qui permet à des patients de prendre rendez-vous avec des médecins. Les médecins peuvent également gérer leurs rendez-vous sur cette plateforme.
## Installation :
Il faut que vous installiez sur votre wsl (linux) postgresql, apache2 et le module php.
Mettez à jour les paquets sur votre ordinateur.
`sudo apt update`
`sudo apt upgrade`

**APACHE2**&
`sudo apt install apache2`

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

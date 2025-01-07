# php_project
### Voici notre site web qui permet à des patients de prendre rendez-vous avec des médecins. Les médecins peuvent également gérer leurs rendez-vous sur cette plateforme.
## Installation :
Il faut que vous installiez sur votre wsl (linux) postgresql, apache2 et le module php.
Mettez à jour les paquets sur votre ordinateur.
`sudo apt update`
`sudo apt upgrade`

**APACHE2**&nbsp;
`sudo apt install apache2`
&nbsp;
**Module PHP**&nbsp;
`sudo apt install php libapache2-mod-php`
&nbsp;
**Postgresql**&nbsp;
Téléchargement :&nbsp;
`sudo apt install postgresql`
`sudo service postgresql start`
&nbsp;
Modification du mot de passe :&nbsp;
`sudo passwd postgres`
`su - postgres`
`psql -c "alter user postgres with password 'StrongAdminP@ssw0rd'"`
`exit`
&nbsp;
Redémarrer :&nbsp;
`sudo service postgresql restart`

## Utilisation :

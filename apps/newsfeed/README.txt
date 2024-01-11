Please, check if sendmail is already installed, if not:
- sudo apt-get install sendmail
- sudo /etc/init.d/apache2 restart

Please, check fully qualified domain (FQDN) name in /etc/hosts (required by sendmail), ex.:
127.0.0.1       localhost mycomputername localhost.localdomain
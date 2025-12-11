# Commandes utiles pour Congometal

## 🐘 Commandes PHP/MySQL

### Vérifier la version PHP
```bash
php -v
```

### Vérifier les extensions PHP
```bash
php -m
```

### Démarrer un serveur PHP local
```bash
php -S localhost:8000
```

### Exécuter un script PHP
```bash
php script.php
```

## 🗄️ Commandes MySQL

### Se connecter à MySQL
```bash
mysql -u root -p
```

### Créer une base de données
```sql
CREATE DATABASE congometal;
CREATE USER 'congometal_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON congometal.* TO 'congometal_user'@'localhost';
FLUSH PRIVILEGES;
```

### Sauvegarder une base de données
```bash
mysqldump -u root -p congometal > backup.sql
```

### Restaurer une base de données
```bash
mysql -u root -p congometal < backup.sql
```

### Voir toutes les bases de données
```sql
SHOW DATABASES;
```

### Voir toutes les tables
```sql
USE congometal;
SHOW TABLES;
```

### Voir la structure d'une table
```sql
DESCRIBE users;
DESCRIBE services;
DESCRIBE portfolio;
DESCRIBE testimonials;
DESCRIBE contacts;
DESCRIBE settings;
```

### Vider une table (attention!)
```sql
TRUNCATE TABLE contacts;
TRUNCATE TABLE services;
```

### Supprimer une table
```sql
DROP TABLE contacts;
```

### Supprimer une base de données
```sql
DROP DATABASE congometal;
```

## 📁 Commandes système (Windows PowerShell)

### Créer un dossier
```powershell
New-Item -ItemType Directory -Path "C:\xampp\htdocs\congometal\test"
```

### Supprimer un dossier
```powershell
Remove-Item -Recurse -Force "C:\xampp\htdocs\congometal\test"
```

### Copier un fichier
```powershell
Copy-Item "source.txt" "destination.txt"
```

### Supprimer un fichier
```powershell
Remove-Item "file.txt"
```

### Lister les fichiers
```powershell
Get-ChildItem -Path "C:\xampp\htdocs\congometal"
```

### Rechercher un fichier
```powershell
Get-ChildItem -Path "C:\xampp\htdocs\congometal" -Filter "*.php" -Recurse
```

### Modifier les permissions
```powershell
# Linux/Mac seulement
chmod 755 folder
chmod 644 file.php
```

## 🔐 Commandes Git

### Initialiser un dépôt
```bash
git init
```

### Ajouter les fichiers
```bash
git add .
```

### Commiter les changements
```bash
git commit -m "Message du commit"
```

### Voir l'historique
```bash
git log
```

### Voir les différences
```bash
git diff
```

### Voir le statut
```bash
git status
```

### Cloner un dépôt
```bash
git clone https://github.com/user/repo.git
```

### Créer une branche
```bash
git branch feature/nouvelle-fonctionalite
git checkout feature/nouvelle-fonctionalite
```

### Fusionner une branche
```bash
git merge feature/nouvelle-fonctionalite
```

## 🔍 Commandes de développement

### Voir les erreurs PHP
```bash
# Depuis le terminal
php -d display_errors=1 -r "echo 'test';"

# Dans le navigateur, accédez à:
http://localhost/congometal/health_check.php
```

### Tester la connexion à la base de données
```php
<?php
require_once 'includes/config.php';
echo "Connexion OK: " . ($conn ? "OUI" : "NON");
?>
```

### Vérifier le protocole HTTPS
```bash
curl -I https://votredomaine.com
```

### Voir les logs Apache
```bash
# Windows
type "C:\xampp\apache\logs\error.log"

# Linux
tail -f /var/log/apache2/error.log
```

### Voir les logs MySQL
```bash
# Windows
type "C:\xampp\mysql\data\mysql.log"

# Linux
tail -f /var/log/mysql/error.log
```

## 🧹 Nettoyage et maintenance

### Supprimer le cache
```bash
# Windows
Remove-Item -Recurse -Force "admin/uploads/*"

# Linux
rm -rf admin/uploads/*
```

### Vider les logs
```bash
# Windows
Clear-Content "logs/*.log"

# Linux
> logs/app.log
```

### Vérifier l'espace disque
```bash
# Windows
Get-Volume

# Linux
df -h
```

### Tester la performance du serveur
```bash
# Apache Bench
ab -n 100 -c 10 http://localhost/congometal/

# Avec curl
time curl http://localhost/congometal/
```

## 📊 Statistiques du site

### Compter les fichiers PHP
```bash
Get-ChildItem -Path "C:\xampp\htdocs\congometal" -Filter "*.php" -Recurse | Measure-Object
```

### Taille totale du projet
```bash
Get-ChildItem -Path "C:\xampp\htdocs\congometal" -Recurse | Measure-Object -Sum -Property Length
```

### Voir les fichiers les plus volumineux
```bash
Get-ChildItem -Path "C:\xampp\htdocs\congometal" -Recurse | Sort-Object Length -Descending | Select-Object Name, @{Name='Size (MB)';Expression={$_.Length/1MB}}
```

## 🔗 Liens utiles

- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Apache Documentation: https://httpd.apache.org/docs/
- Git Documentation: https://git-scm.com/doc
- HTML5 Reference: https://html.spec.whatwg.org/
- CSS Reference: https://developer.mozilla.org/en-US/docs/Web/CSS

## ⚙️ Configuration d'erreurs

### Activer l'affichage des erreurs (développement)
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Désactiver l'affichage des erreurs (production)
```php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');
```

## 🎯 Commandes fréquentes

### Redémarrer XAMPP
```bash
# Windows: Utilisez le control panel de XAMPP ou:
net stop Apache2.4
net stop MySQL
net start Apache2.4
net start MySQL

# Linux
sudo systemctl restart apache2
sudo systemctl restart mysql
```

### Vérifier l'état des services
```bash
# Linux
sudo systemctl status apache2
sudo systemctl status mysql
```

### Démarrer les services au boot
```bash
# Linux
sudo systemctl enable apache2
sudo systemctl enable mysql
```

---

💡 **Conseil**: Consultez la documentation officielle pour plus d'informations.

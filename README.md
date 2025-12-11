# Congometal - Site Corporate avec Admin Panel

Site web corporate complet avec panel d'administration pour gérer le contenu, les images, les services, le portfolio et les témoignages.

## 🚀 Technologies utilisées

- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Backend**: PHP 7.4+
- **Base de données**: MySQL/MariaDB
- **Serveur**: Apache (XAMPP)

## 📋 Prérequis

- XAMPP installé sur votre ordinateur
- PHP 7.4 ou supérieur
- MySQL/MariaDB

## 🔧 Installation

### 1. Initialiser la base de données

1. Accédez à [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Créez une nouvelle base de données nommée `congometal`
3. Ou exécutez le script d'initialisation en visitant :
   ```
   http://localhost/congometal/includes/db_init.php
   ```

### 2. Créer un utilisateur administrateur

Connectez-vous à phpMyAdmin et insérez un utilisateur dans la table `users` :

```sql
INSERT INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin@congometal.com', 'admin');
```

Ou utilisez ce script PHP pour créer un utilisateur (temporaire) :

```php
<?php
require_once 'includes/config.php';
$username = 'admin';
$password = password_hash('admin', PASSWORD_DEFAULT);
$email = 'admin@congometal.com';

$sql = "INSERT INTO users (username, password, email, role) VALUES ('$username', '$password', '$email', 'admin')";
$conn->query($sql);
echo "Utilisateur créé: admin / admin";
?>
```

## 📁 Structure des fichiers

```
congometal/
├── index.php                 # Page d'accueil
├── includes/
│   ├── config.php           # Configuration DB
│   ├── functions.php        # Fonctions utiles
│   ├── db_init.php          # Initialisation DB
│   └── handle_contact.php   # Traitement formulaire contact
├── admin/
│   ├── dashboard.php        # Tableau de bord
│   ├── login.php            # Connexion
│   ├── logout.php           # Déconnexion
│   ├── add_service.php      # Ajouter/éditer service
│   ├── add_portfolio.php    # Ajouter/éditer projet
│   ├── add_testimonial.php  # Ajouter/éditer témoignage
│   ├── add_user.php         # Ajouter/éditer utilisateur
│   ├── save_settings.php    # Enregistrer paramètres
│   └── uploads/             # Dossier pour les images
├── assets/
│   ├── css/
│   │   └── style.css        # Styles principaux
│   ├── js/
│   │   └── main.js          # Scripts JavaScript
│   └── images/              # Images du site
└── README.md                # Ce fichier
```

## 🌐 Accès au site

- **Site public**: [http://localhost/congometal/](http://localhost/congometal/)
- **Panel admin**: [http://localhost/congometal/admin/login.php](http://localhost/congometal/admin/login.php)
- **Identifiants par défaut**: 
  - Username: `admin`
  - Password: `admin`

## 📊 Fonctionnalités

### Site Public
- ✅ Page d'accueil avec section héros
- ✅ Section À propos
- ✅ Liste des services avec icônes
- ✅ Portfolio/Galerie de projets
- ✅ Témoignages de clients
- ✅ Formulaire de contact
- ✅ Navigation fluide et responsive
- ✅ Footer avec informations

### Panel Admin
- ✅ Tableau de bord avec statistiques
- ✅ Gestion des paramètres du site
- ✅ Gestion des services (CRUD)
- ✅ Gestion du portfolio avec images
- ✅ Gestion des témoignages avec photos
- ✅ Consultation des messages de contact
- ✅ Gestion des utilisateurs admin
- ✅ Upload et gestion d'images

## 🔐 Sécurité

- Mots de passe hashés avec `PASSWORD_DEFAULT` (bcrypt)
- Échappement des entrées utilisateur
- Vérification d'authentification sur les pages admin
- Vérification des rôles (admin/user)

## 📝 Utilisation du Panel Admin

### Ajouter un service
1. Connexion au panel admin
2. Cliquez sur "Services"
3. Bouton "+ Ajouter un service"
4. Remplissez le formulaire avec :
   - Titre
   - Description
   - Icône (emoji ou unicode)
   - Position d'affichage
   - Statut

### Ajouter un projet au portfolio
1. Cliquez sur "Portfolio"
2. Bouton "+ Ajouter un projet"
3. Remplissez les informations :
   - Titre du projet
   - Description
   - Catégorie
   - Client
   - Image (JPG, PNG, GIF, WebP)
   - URL du projet (optionnelle)

### Ajouter un témoignage
1. Cliquez sur "Témoignages"
2. Bouton "+ Ajouter un témoignage"
3. Ajoutez :
   - Nom du client
   - Poste et entreprise
   - Message/Avis
   - Note (1-5 étoiles)
   - Photo du client

### Gérer les paramètres du site
1. Cliquez sur "Paramètres"
2. Modifiez :
   - Nom de l'entreprise
   - Titre et description du site
   - Informations de contact
   - Titre et sous-titre du héros
   - Liens réseaux sociaux

## 🎨 Personnalisation

### Modifier les couleurs
Éditez `assets/css/style.css` et changez les couleurs du gradient :
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Modifier le logo
Changez le texte du logo dans `index.php` :
```php
<a href="#" class="logo">Votre nom</a>
```

## 📸 Gestion des images

Les images téléchargées sont stockées dans :
- Services: `admin/uploads/general/`
- Portfolio: `admin/uploads/portfolio/`
- Témoignages: `admin/uploads/testimonials/`

Limite de taille: 5MB par fichier
Formats acceptés: JPEG, PNG, GIF, WebP

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL/MariaDB est en cours d'exécution
- Vérifiez les paramètres dans `includes/config.php`
- Assurez-vous que la base `congometal` existe

### Les images ne s'affichent pas
- Vérifiez que le dossier `admin/uploads/` existe
- Vérifiez les permissions du dossier (755)
- Vérifiez que les images sont correctement téléchargées

### Impossible de se connecter au panel admin
- Vérifiez que l'utilisateur existe dans la table `users`
- Vérifiez le mot de passe (attention à la casse)
- Vérifiez que les sessions PHP sont activées

## 📧 Support

Pour toute question ou problème, consultez la documentation ou contactez l'équipe de support.

## 📄 Licence

Ce projet est fourni tel quel à titre de template.

---

**Dernier mise à jour**: 17 novembre 2025

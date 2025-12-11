# Congometal - Guide de démarrage rapide

## 🚀 Première utilisation

### Étape 1: Initialiser le site

1. Assurez-vous que **XAMPP** est démarré (Apache + MySQL)
2. Accédez à: **http://localhost/congometal/install.php**
3. Attendez que l'installation se termine
4. Notez vos identifiants admin

### Étape 2: Se connecter au panel admin

1. Allez à: **http://localhost/congometal/admin/login.php**
2. Identifiants:
   - Username: `admin`
   - Password: `admin`
3. **⚠️ Changez le mot de passe immédiatement!**

### Étape 3: Configurer votre entreprise

1. Cliquez sur **"Paramètres"** dans le menu
2. Remplissez:
   - ✏️ Nom de l'entreprise
   - ✏️ Email et téléphone
   - ✏️ Adresse
   - ✏️ Texte de la page d'accueil
3. Cliquez sur **"Enregistrer les paramètres"**

### Étape 4: Ajouter vos services

1. Cliquez sur **"Services"**
2. Cliquez sur **"+ Ajouter un service"**
3. Remplissez:
   - Titre du service
   - Description
   - Icône (emoji)
   - Statut (Actif/Inactif)
4. Cliquez sur **"Enregistrer"**
5. Répétez pour chaque service

### Étape 5: Ajouter des projets

1. Cliquez sur **"Portfolio"**
2. Cliquez sur **"+ Ajouter un projet"**
3. Remplissez:
   - Titre du projet
   - Description complète
   - Catégorie
   - Nom du client
   - Image du projet (JPG, PNG, GIF)
4. Cliquez sur **"Enregistrer"**

### Étape 6: Ajouter des témoignages

1. Cliquez sur **"Témoignages"**
2. Cliquez sur **"+ Ajouter un témoignage"**
3. Remplissez:
   - Nom du client
   - Poste et entreprise
   - Message/Avis
   - Note (1-5 étoiles)
   - Photo du client
4. Cliquez sur **"Enregistrer"**

## 📱 Utilisation du site public

### Accès
- **URL**: http://localhost/congometal/

### Sections disponibles
- ✨ **Accueil**: Section héros avec CTA
- ℹ️ **À propos**: Informations sur l'entreprise
- 🛠️ **Services**: Liste vos services
- 🎨 **Portfolio**: Galerie de vos projets
- ⭐ **Témoignages**: Avis de vos clients
- 📧 **Contact**: Formulaire et informations

## 🔑 Gestion des utilisateurs

### Créer un autre utilisateur admin

1. Cliquez sur **"Utilisateurs"** dans le panel admin
2. Cliquez sur **"+ Ajouter un utilisateur"**
3. Remplissez:
   - Nom d'utilisateur
   - Email
   - Mot de passe sécurisé
   - Rôle (Admin ou Utilisateur)
4. Cliquez sur **"Enregistrer"**

### Modifier un profil

1. Cliquez sur **"Utilisateurs"**
2. Cliquez sur **"Éditer"** pour l'utilisateur
3. Modifiez les informations
4. Cliquez sur **"Enregistrer"**

## 📊 Gestion des messages

### Consulter les messages de contact

1. Cliquez sur **"Contacts"**
2. Vous verrez tous les messages reçus
3. Cliquez sur **"Voir"** pour lire le message complet
4. Vous pouvez répondre directement via email

## 🎨 Personnalisation du design

### Modifier les couleurs

1. Ouvrez `assets/css/style.css`
2. Cherchez: `#667eea` et `#764ba2`
3. Remplacez par vos couleurs préférées

### Modifier le logo

Dans `index.php`, ligne 45:
```php
<a href="#" class="logo">Votre nom</a>
```

### Modifier le favicon

Ajoutez dans `index.php` dans la section `<head>`:
```html
<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
```

## 💾 Sauvegarder votre site

### Base de données
1. Ouvrez phpMyAdmin: http://localhost/phpmyadmin
2. Sélectionnez la base `congometal`
3. Cliquez sur **"Exporter"**
4. Cliquez sur **"Exécuter"**

### Fichiers
1. Copiez le dossier `c:\xampp\htdocs\congometal\`
2. Conservez-le en lieu sûr

## 🆘 Problèmes courants

### Le site ne s'affiche pas
- ✓ Vérifiez que XAMPP est démarré
- ✓ Vérifiez l'URL: http://localhost/congometal/
- ✓ Vérifiez la console du navigateur (F12) pour les erreurs

### Impossible de se connecter au panel admin
- ✓ Vérifiez le nom d'utilisateur et mot de passe
- ✓ Attention à la casse du mot de passe
- ✓ Videz le cache du navigateur
- ✓ Essayez un autre navigateur

### Les images ne s'affichent pas
- ✓ Vérifiez que le dossier `admin/uploads/` existe
- ✓ Vérifiez les permissions du dossier (755)
- ✓ Retéléchargez les images

### Erreur "Base de données"
- ✓ Vérifiez que MySQL est en cours d'exécution
- ✓ Vérifiez les paramètres dans `includes/config.php`
- ✓ Relancez install.php

## 📧 Support et aide

Pour plus d'informations, consultez:
- README.md: Documentation complète
- Contactez votre équipe de développement

---

**Bon travail! 🎉 Votre site est maintenant prêt à fonctionner.**

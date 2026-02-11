# Documentation du projet Congometal

Ce document donne une description courte de chaque fichier qui contient du code ou de la configuration dans le depot.

**Apercu**
- `frontend/index.php` : page d'accueil (site public), sections, formulaires et affichage des donnees.
- `frontend/assets/css/style.css` : styles du site public.
- `frontend/assets/js/main.js` : interactions front (menu, formulaire, animations, mises a jour).
- `backend/admin/*.php` : interface d'administration (dashboard, CRUD, authentification).
- `backend/includes/*.php` : config, fonctions communes, initialisation base de donnees.
- `backend/api/*` : endpoints API (services, portfolio).

**Fichiers racine**
- `setup.php` : installation initiale et mise en place du site.
- `install.php` : installeur (configuration et base).
- `sample_data.php` : insertion d'exemples de donnees.
- `health_check.php` : verification simple du fonctionnement.
- `verification.php` : verification d'etat / configuration.
- `PERSONNALISATION.html` : guide statique de personnalisation.
- `README.md` : documentation generale (si remplie).
- `GUIDE_RAPIDE.md` : guide rapide d'utilisation.
- `COMMANDES_UTILES.md` : commandes utiles pour dev/maintenance.
- `DEPLOYMENT.md` : instructions de deploiement.

**Frontend**
- `frontend/includes/config.php` : configuration front (connexion DB, constantes).
- `frontend/includes/functions.php` : fonctions utilitaires front (services, settings, etc.).
- `frontend/includes/db_init.php` : creation des tables cote front.
- `frontend/includes/handle_contact.php` : traitement du formulaire de contact.
- `frontend/includes/handle_testimonial.php` : traitement du formulaire de temoignage.
- `frontend/includes/fetch_testimonials.php` : endpoint JSON pour temoignages valides.

**Backend - includes**
- `backend/includes/config.php` : configuration DB, URLs, constantes, session.
- `backend/includes/functions.php` : fonctions communes (auth, CRUD helpers, upload, visites).
- `backend/includes/db_init.php` : creation des tables cote back.
- `backend/includes/handle_contact.php` : traitement du contact cote admin/back.

**Backend - admin (interface)**
- `backend/admin/login.php` : page de connexion admin.
- `backend/admin/logout.php` : deconnexion admin.
- `backend/admin/dashboard.php` : tableau de bord admin et stats.
- `backend/admin/view_contact.php` : detail d'un message de contact.
- `backend/admin/add_service.php` : ajout d'un service.
- `backend/admin/edit_service.php` : edition d'un service.
- `backend/admin/delete_service.php` : suppression d'un service.
- `backend/admin/add_portfolio.php` : ajout d'un projet portfolio.
- `backend/admin/edit_portfolio.php` : edition d'un projet.
- `backend/admin/delete_portfolio.php` : suppression d'un projet.
- `backend/admin/manage_portfolio.php` : gestion liste portfolio.
- `backend/admin/add_event.php` : ajout d'un evenement.
- `backend/admin/edit_event.php` : edition d'un evenement.
- `backend/admin/delete_event.php` : suppression d'un evenement.
- `backend/admin/delete_event_media.php` : suppression media d'evenement.
- `backend/admin/save_event_poster.php` : ajout/modif affiche evenement.
- `backend/admin/delete_event_poster.php` : suppression affiche evenement.
- `backend/admin/add_about_gallery.php` : ajout image galerie "A propos".
- `backend/admin/approve_testimonial.php` : validation d'un temoignage.
- `backend/admin/reject_testimonial.php` : rejet d'un temoignage.
- `backend/admin/delete_testimonial.php` : suppression d'un temoignage.
- `backend/admin/add_user.php` : creation utilisateur admin.
- `backend/admin/edit_user.php` : edition utilisateur admin.
- `backend/admin/delete_user.php` : suppression utilisateur admin.
- `backend/admin/manage_team.php` : gestion equipe (membres).
- `backend/admin/save_member.php` : sauvegarde membre equipe.
- `backend/admin/save_category.php` : sauvegarde categorie equipe.
- `backend/admin/update_category_order.php` : ordre des categories equipe.
- `backend/admin/create_team_table.php` : creation table equipe (outil).
- `backend/admin/seed_team.php` : donnees exemple equipe.
- `backend/admin/test_members.php` : test affichage membres.
- `backend/admin/check_db.php` : verification base (outil).
- `backend/admin/check_tables.php` : verification tables (outil).
- `backend/admin/db_diagnostic.php` : diagnostic DB (outil).
- `backend/admin/fix_team_schema.php` : correction schema equipe (outil).
- `backend/admin/setup_database.php` : initialisation DB (outil).
- `backend/admin/google_callback.php` : callback OAuth Google.

**Backend - API**
- `backend/api/index.php` : point d'entree API.
- `backend/api/routes/api.php` : definition des routes API.
- `backend/api/config/api_config.php` : config API.
- `backend/api/controllers/BaseController.php` : base controller API.
- `backend/api/controllers/ServiceController.php` : endpoints services.
- `backend/api/controllers/PortfolioController.php` : endpoints portfolio.

**Assets globaux**
- `assets/css/style.css` : styles CSS globaux (si utilises ailleurs).

**Notes**
- Les fichiers `db_init.php` existent cote front et back pour creer les tables.
- Les pages admin utilisent `backend/includes/config.php` et `backend/includes/functions.php`.

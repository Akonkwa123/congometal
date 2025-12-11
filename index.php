<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSetting('site_title', 'Congometal - Entreprise'); ?></title>
    <meta name="description" content="<?php echo getSetting('site_description', 'Entreprise spécialisée dans les services de qualité'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body data-theme="light">
    <!-- Header & Navigation -->
    <header>
        <nav class="navbar navbar-expand-lg main-navbar">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="#home" class="navbar-brand logo">
                    <?php $logo = getSetting('company_logo', ''); ?>
                    <?php $logo_alt = getSetting('company_logo_alt', getSetting('company_name', 'Congometal')); ?>
                    <?php if ($logo): ?>
                        <img src="<?php echo SITE_URL . '/admin/uploads/' . $logo; ?>" alt="<?php echo htmlspecialchars($logo_alt); ?>" style="max-height:30px;vertical-align:middle;">
                    <?php else: ?>
                        <?php echo getSetting('company_name', 'Congometal'); ?>
                    <?php endif; ?>
                </a>

                

                <!-- Bouton burger mobile -->
                <button class="navbar-toggler" type="button" aria-controls="mainNav" aria-expanded="false" aria-label="Basculer la navigation" id="navbarToggle">
                    <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-lg-auto nav-links mb-2 mb-lg-0">
                        <li class="nav-item"><a href="#home" class="nav-link active"><i class="bi bi-house-door-fill nav-icon"></i><span>Accueil</span></a></li>
                        <li class="nav-item"><a href="#about" class="nav-link"><i class="bi bi-info-circle nav-icon"></i><span>À propos</span></a></li>
                        <li class="nav-item"><a href="#services" class="nav-link"><i class="bi bi-grid nav-icon"></i><span>Services</span></a></li>
                        <li class="nav-item"><a href="#portfolio" class="nav-link"><i class="bi bi-briefcase nav-icon"></i><span>Portfolio</span></a></li>
                        <li class="nav-item"><a href="#testimonials" class="nav-link"><i class="bi bi-chat-quote nav-icon"></i><span>Témoignages</span></a></li>
                        <li class="nav-item"><a href="#contact" class="nav-link"><i class="bi bi-envelope-open nav-icon"></i><span>Contact</span></a></li>
                        <li class="nav-item"><a href="admin/login.php" class="nav-link"><i class="bi bi-shield-lock nav-icon"></i><span>se connecter</span></a></li>
                    </ul>
                </div>

                <!-- Social icons & thème -->
                <div class="header-social d-flex align-items-center ms-3">
                    <?php $sf = getSetting('social_facebook', ''); $st = getSetting('social_twitter', ''); $sl = getSetting('social_linkedin', ''); $si = getSetting('social_instagram', ''); ?>
                    <?php if ($sf): ?>
                        <a href="<?php echo htmlspecialchars($sf); ?>" target="_blank" aria-label="Facebook" class="header-social-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="#1877F2" xmlns="http://www.w3.org/2000/svg"><path d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2v-3h2v-2.3c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.3l-.4 3h-1.9v7A10 10 0 0 0 22 12z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($st): ?>
                        <a href="<?php echo htmlspecialchars($st); ?>" target="_blank" aria-label="Twitter" class="header-social-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="#1DA1F2" xmlns="http://www.w3.org/2000/svg"><path d="M22 5.8c-.6.3-1.2.5-1.9.6.7-.4 1.2-1 1.4-1.8-.6.4-1.3.7-2 .9C18.7 4.7 17.9 4 17 4c-1.6 0-2.8 1.5-2.4 3.1C12.4 7 10 5.9 8.3 4c-.9 1.6-.2 3.7 1.4 4.7-.5 0-1-.2-1.4-.4 0 1.4 1 2.6 2.5 2.9-.5.1-1 .1-1.5 0 .4 1.3 1.6 2.2 3 2.2-1 1-2.2 1.5-3.6 1.5h-.7c1.3.9 2.8 1.4 4.4 1.4 5.3 0 8.2-4.4 8.2-8.2v-.4c.6-.4 1.1-1 1.5-1.6-.6.3-1.2.6-1.9.7z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($sl): ?>
                        <a href="<?php echo htmlspecialchars($sl); ?>" target="_blank" aria-label="LinkedIn" class="header-social-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="#0A66C2" xmlns="http://www.w3.org/2000/svg"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.6h.1c.5-.9 1.7-1.8 3.4-1.8 3.6 0 4.2 2.4 4.2 5.6V21h-4v-5.1c0-1.2 0-2.8-1.7-2.8-1.7 0-2 1.4-2 2.7V21H9z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($si): ?>
                        <a href="<?php echo htmlspecialchars($si); ?>" target="_blank" aria-label="Instagram" class="header-social-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="#C13584" xmlns="http://www.w3.org/2000/svg"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 6.2A4.8 4.8 0 1 0 16.8 13 4.8 4.8 0 0 0 12 8.2zm6.4-2.6a1.1 1.1 0 1 1-1.1-1.1 1.1 1.1 0 0 1 1.1 1.1z"/></svg>
                        </a>
                    <?php endif; ?>
                    <button id="themeToggle" class="theme-toggle" type="button" aria-label="Changer le thème">
                        <span class="theme-toggle-icon theme-toggle-icon-sun">☀</span>
                        <span class="theme-toggle-icon theme-toggle-icon-moon">🌙</span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero" style="<?php 
        $hero_bg = getSetting('hero_background_image', '');
        if ($hero_bg) {
            echo 'background-image: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.9)), url(' . htmlspecialchars(SITE_URL . '/admin/uploads/' . $hero_bg) . ');';
            echo ' background-size: cover;';
            echo ' background-position: center;';
            echo ' background-attachment: fixed;';
        }
        ?>">
        <div class="container hero-container">
            <div class="row align-items-center gy-4 hero-row">
                <div class="col-lg-7">
                    <span class="hero-kicker">
                        <i class="bi bi-stars hero-kicker-icon"></i>
                        <?php echo getSetting('hero_kicker', 'Partenaire industriel de confiance'); ?>
                    </span>
                    <h1><?php echo getSetting('hero_title', 'Bienvenue dans notre entreprise'); ?></h1>
                    <p class="hero-lead"><?php echo getSetting('hero_subtitle', 'Nous offrons les meilleures solutions pour vos besoins'); ?></p>
                    <div class="hero-actions">
                        <a href="#services" class="cta-button">
                            Découvrir nos services <i class="bi bi-arrow-right-short cta-icon"></i>
                        </a>
                        <a href="#portfolio" class="hero-secondary-btn">
                            Voir nos réalisations <i class="bi bi-play-circle-fill hero-secondary-icon"></i>
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="hero-stat-number">10+</span>
                            <span class="hero-stat-label">Années d'expérience</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">100+</span>
                            <span class="hero-stat-label">Projets livrés</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">24/7</span>
                            <span class="hero-stat-label">Support & assistance</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-card">
                        <div class="hero-card-icon">
                            <i class="bi bi-building-gear"></i>
                        </div>
                        <h3>Ingénierie, fabrication et maintenance</h3>
                        <p>Congometal vous accompagne sur vos projets industriels, de la conception à la mise en service, avec un haut niveau d'exigence qualité et sécurité.</p>
                        <ul class="hero-badges">
                            <li><i class="bi bi-shield-check"></i> Processus qualité maîtrisés</li>
                            <li><i class="bi bi-lightning-charge"></i> Interventions rapides sur site</li>
                            <li><i class="bi bi-people"></i> Équipe d'experts dédiée</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section with Tabs Menu -->
    <section id="about">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker"><i class="bi bi-info-circle"></i> À propos</span>
                <h2 class="about-title">À propos de nous</h2>
                <p class="section-subtitle"><?php echo getSetting('about_subtitle', 'Découvrez qui nous sommes, notre histoire et notre mission.'); ?></p>
            </div>
            
            <!-- Logo + About Content Layout -->
            <div class="row align-items-center gy-4 about-top-row">
                <!-- Logo Left Side -->
                <div class="col-md-4 col-lg-3 text-center about-logo-wrap">
                    <?php $logo = getSetting('company_logo', ''); ?>
                    <?php if ($logo): ?>
                        <div class="about-logo-card">
                            <img src="<?php echo SITE_URL . '/admin/uploads/' . $logo; ?>" alt="<?php echo htmlspecialchars(getSetting('company_logo_alt', 'Logo')); ?>" class="about-logo-img">
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- About Content Right Side -->
                <div class="col-md-8 col-lg-9">
                    <div class="about-hero-card">
                        <div class="about-hero-media">
                            <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo getSetting('about_image', 'about.jpg'); ?>" alt="<?php echo htmlspecialchars(getSetting('about_image_alt', 'À propos')); ?>" class="about-image">
                        </div>
                        <div class="about-text">
                            <h3><?php echo getSetting('about_title', 'Qui sommes-nous ?'); ?></h3>
                            <p><?php echo getSetting('about_description', 'Description de votre entreprise'); ?></p>
                            <div class="about-highlight">
                                <i class="bi bi-patch-check-fill"></i>
                                <span><?php echo getSetting('about_highlight', 'Expertise, fiabilité et proximité au service de vos projets.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Menu -->
            <div class="about-tabs-wrapper">
                <div class="about-tabs-nav">
                    <button class="about-tab-btn active" data-tab="who-we-are">
                        <i class="bi bi-people-fill"></i>
                        <span>Qui sommes-nous</span>
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="about-tab-panels">
                    <div class="about-tab-content active" id="tab-who-we-are">
                        <div class="about-tab-card about-tab-primary">
                            <p><?php echo nl2br(htmlspecialchars(getSetting('tab_who_are_we', 'Contenu de la section « Qui sommes-nous » à remplir.'))); ?></p>
                        </div>
                    </div>

                    <div class="about-tab-content" id="tab-policies">
                        <div class="about-tab-card about-tab-policies">
                            <p><?php echo nl2br(htmlspecialchars(getSetting('tab_policies', 'Contenu de la section « Nos Politiques » à remplir.'))); ?></p>
                        </div>
                    </div>

                    <div class="about-tab-content" id="tab-history">
                        <div class="about-tab-card about-tab-history">
                            <p><?php echo nl2br(htmlspecialchars(getSetting('tab_history', 'Contenu de la section « Historique » à remplir.'))); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Details: Story, Objectives, Vision, Mission -->
            <div class="about-details-grid">
                <?php
                $story = getSetting('about_story', '');
                $objectives = getSetting('about_objectives', '');
                $vision = getSetting('about_vision', '');
                $mission = getSetting('about_mission', '');
                ?>

                <?php if ($story): ?>
                    <div class="about-detail-card about-detail-story">
                        <h4><i class="bi bi-journal-bookmark about-icon"></i> Notre Histoire</h4>
                        <p><?php echo nl2br(htmlspecialchars($story)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($objectives): ?>
                    <div class="about-detail-card about-detail-objectives">
                        <h4><i class="bi bi-bullseye about-icon"></i> Nos Objectifs</h4>
                        <p><?php echo nl2br(htmlspecialchars($objectives)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($vision): ?>
                    <div class="about-detail-card about-detail-vision">
                        <h4><i class="bi bi-stars about-icon"></i> Notre Vision</h4>
                        <p><?php echo nl2br(htmlspecialchars($vision)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($mission): ?>
                    <div class="about-detail-card about-detail-mission">
                        <h4><i class="bi bi-briefcase-fill about-icon"></i> Notre Mission</h4>
                        <p><?php echo nl2br(htmlspecialchars($mission)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services">
        <div class="container">
            <div class="services-header">
                <span class="services-kicker"><i class="bi bi-grid-3x3-gap-fill"></i> Expertises & solutions</span>
                <h2>Nos Services</h2>
                <p class="services-subtitle">Des prestations compl&egrave;tes, de la conception &agrave; la maintenance pour accompagner vos projets industriels.</p>
            </div>
            <div class="services-grid row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php
                // Tableau de correspondance entre les titres de services et leurs icônes (Bootstrap Icons)
                $service_icons = [
                    'conception'    => 'bi-pencil-ruler',
                    'fabrication'   => 'bi-gear-fill',
                    'installation'  => 'bi-nut-fill',
                    'maintenance'   => 'bi-tools',
                    'réparation'    => 'bi-wrench',
                    'dépannage'     => 'bi-wrench-adjustable',
                    'ingénierie'    => 'bi-diagram-3',
                    'conseil'       => 'bi-lightbulb',
                    'formation'     => 'bi-mortarboard',
                    'audit'         => 'bi-search',
                    'développement' => 'bi-code-slash',
                    'soudure'       => 'bi-fire',
                    'usinage'       => 'bi-cpu',
                    'chaudronnerie' => 'bi-hammer',
                    'mécanique'     => 'bi-nut',
                    'électricité'   => 'bi-lightning-charge',
                    'automatisme'   => 'bi-robot',
                    'contrôle'      => 'bi-check2-circle',
                    'qualité'       => 'bi-award',
                    'sécurité'      => 'bi-shield-lock'
                ];

                $services = getServices('active');
                if ($services && $services->num_rows > 0) {
                    while ($service = $services->fetch_assoc()) {
                        // Trouver l'icône appropriée en fonction du titre du service
                        $icon = 'bi-dot'; // Icône par défaut
                        $title_lower = mb_strtolower($service['title']);
                        
                        foreach ($service_icons as $keyword => $service_icon) {
                            if (strpos($title_lower, $keyword) !== false) {
                                $icon = $service_icon;
                                break;
                            }
                        }
                        ?>
                        <div class="col service-card">
                            <div class="service-icon">
                                <i class="bi <?php echo $icon; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
    <!-- About Gallery -->
    <?php
    $gallery_result = $conn->query("SELECT * FROM about_gallery ORDER BY order_position ASC");
    $gallery_count = $gallery_result ? $gallery_result->num_rows : 0;
    ?>
    <?php if ($gallery_count > 0): ?>
        <section id="gallery" class="gallery-section">
            <div class="container">
                <div class="section-header gallery-header">
                    <span class="section-kicker"><i class="bi bi-images"></i> Notre Galerie</span>
                    <h2>Notre Galerie</h2>
                    <p class="section-subtitle">Un aper&ccedil;u en images de nos r&eacute;alisations et de notre environnement de travail.</p>
                </div>
                <div class="gallery-grid">
                    <?php while ($img = $gallery_result->fetch_assoc()): ?>
                        <?php $full = SITE_URL . '/admin/uploads/' . htmlspecialchars($img['image_path']); ?>
                        <article class="gallery-item">
                            <div class="gallery-image-wrap">
                                <img src="<?php echo $full; ?>" data-full="<?php echo $full; ?>" alt="<?php echo htmlspecialchars($img['image_alt'] ?? 'Photo galerie'); ?>" class="gallery-image">
                                <div class="gallery-image-overlay">
                                    <span class="gallery-zoom-icon"><i class="bi bi-arrows-fullscreen"></i></span>
                                </div>
                            </div>
                            <?php if (!empty($img['description'])): ?>
                                <div class="gallery-caption">
                                    <p><?php echo htmlspecialchars($img['description']); ?></p>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <!-- Lightbox overlay (gallery) -->
    <div id="lightbox-overlay" class="lightbox-overlay" aria-hidden="true">
        <div class="lightbox-inner" role="dialog" aria-modal="true">
            <button id="lightbox-close" class="lightbox-close" aria-label="Fermer">✕</button>
            <button id="lightbox-prev" class="lightbox-prev" aria-label="Précédent">❮</button>
            <div style="text-align:center;">
                <img id="lightbox-image" class="lightbox-img" src="" alt="">
                <div id="lightbox-caption" class="lightbox-caption"></div>
            </div>
            <button id="lightbox-next" class="lightbox-next" aria-label="Suivant">❯</button>
        </div>
    </div>
    <!-- Portfolio Section -->
    <section id="portfolio">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker"><i class="bi bi-briefcase"></i> Réalisations</span>
                <h2>Nos réalisations</h2>
                <p class="section-subtitle">Une sélection de projets réalisés pour nos clients industriels.</p>
            </div>
            <div class="portfolio-wrapper">
                <?php
                $portfolio = getPortfolio();
                if ($portfolio && $portfolio->num_rows > 0) {
                    $portfolio_count = $portfolio->num_rows;
                    $portfolio->data_seek(0);
                    $index = 0;
                    while ($project = $portfolio->fetch_assoc()) {
                        $active_class = ($index === 0) ? 'active' : '';
                        ?>
                        <div class="portfolio-slide <?php echo $active_class; ?>" data-index="<?php echo $index; ?>">
                            <?php if (!empty($project['image'])): ?>
                                <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="portfolio-image">
                            <?php else: ?>
                                <div style="width: 100%; height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">Pas d'image</div>
                            <?php endif; ?>
                            <div class="portfolio-overlay">
                                <div class="portfolio-content">
                                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <p class="portfolio-desc"><?php echo htmlspecialchars($project['description']); ?></p>
                                    <?php if (!empty($project['client'])): ?>
                                        <p class="portfolio-client"><strong>Client:</strong> <?php echo htmlspecialchars($project['client']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($project['category'])): ?>
                                        <span class="portfolio-category"><?php echo htmlspecialchars($project['category']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        $index++;
                    }
                    
                    if ($portfolio_count > 1):
                    ?>
                        <button class="portfolio-control prev" onclick="portfolioSlide(-1)">❮</button>
                        <button class="portfolio-control next" onclick="portfolioSlide(1)">❯</button>
                        <div class="portfolio-dots">
                            <?php for ($i = 0; $i < $portfolio_count; $i++): ?>
                                <span class="portfolio-dot <?php echo ($i === 0) ? 'active' : ''; ?>" onclick="portfolioGoTo(<?php echo $i; ?>)"></span>
                            <?php endfor; ?>
                        </div>
                    <?php
                    endif;
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker"><i class="bi bi-chat-quote"></i> Avis & retours</span>
                <h2>Témoignages de nos clients</h2>
                <p class="section-subtitle">Ils nous font confiance pour leurs projets industriels&nbsp;: découvrez leurs retours d&apos;expérience.</p>
            </div>
            <div class="testimonials-grid">
                <?php
                $testimonials = getTestimonials();
                if ($testimonials && $testimonials->num_rows > 0) {
                    while ($testimonial = $testimonials->fetch_assoc()) {
                        $stars = str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']);
                        ?>
                        <div class="testimonial-card">
                            <div class="testimonial-header-row">
                                <?php if (!empty($testimonial['image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo $testimonial['image']; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="testimonial-avatar">
                                <?php else: ?>
                                    <div class="testimonial-avatar-placeholder"></div>
                                <?php endif; ?>
                                <div class="testimonial-header-text">
                                    <div class="testimonial-name"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                                    <div class="testimonial-position"><?php echo htmlspecialchars($testimonial['position']) . ' - ' . htmlspecialchars($testimonial['company']); ?></div>
                                    <div class="testimonial-stars-row">
                                        <div class="stars"><?php echo $stars; ?></div>
                                    </div>
                                </div>
                            </div>

                            <p class="testimonial-message"><?php echo htmlspecialchars($testimonial['message']); ?></p>

                            <?php if (!empty($testimonial['media_type']) && !empty($testimonial['media_path'])): ?>
                                <div class="testimonial-media-block">
                                    <?php if ($testimonial['media_type'] === 'image'): ?>
                                        <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" alt="<?php echo htmlspecialchars($testimonial['media_title'] ?? $testimonial['name']); ?>" class="testimonial-media-img">
                                    <?php elseif ($testimonial['media_type'] === 'video'): ?>
                                        <div class="testimonial-media-video-wrap">
                                            <video controls class="testimonial-media-video">
                                                <source src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" type="video/mp4">
                                                Votre navigateur ne supporte pas la lecture vidéo.
                                            </video>
                                        </div>
                                    <?php elseif ($testimonial['media_type'] === 'audio'): ?>
                                        <audio controls class="testimonial-media-audio">
                                            <source src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" type="audio/mpeg">
                                            Votre navigateur ne supporte pas la lecture audio.
                                        </audio>
                                    <?php elseif ($testimonial['media_type'] === 'document'): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" target="_blank" class="testimonial-media-link">Télécharger le document</a>
                                    <?php endif; ?>

                                    <?php if (!empty($testimonial['media_title'])): ?>
                                        <div class="testimonial-media-title"><?php echo htmlspecialchars($testimonial['media_title']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($testimonial['media_caption'])): ?>
                                        <div class="testimonial-media-caption"><?php echo htmlspecialchars($testimonial['media_caption']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <!-- Notre Équipe -->
    <section id="team" class="team-section">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker"><i class="bi bi-people"></i> Notre équipe</span>
                <h2>Notre Équipe</h2>
                <p class="section-subtitle">Découvrez les femmes et les hommes qui portent vos projets au quotidien.</p>
                <a href="#contact" class="team-cta-button">Rencontrer l'équipe</a>
            </div>
            <div class="team-grid">
                <?php
                $team_query = $conn->query("SELECT * FROM team_members ORDER BY display_order, name");
                if ($team_query && $team_query->num_rows > 0) {
                    while ($member = $team_query->fetch_assoc()) {
                        $image_path = !empty($member['image_path']) ? 
                            SITE_URL . '/admin/uploads/team/' . basename($member['image_path']) : 
                            'https://via.placeholder.com/300x300?text=' . urlencode($member['name'][0]);
                        ?>
                        <div class="team-card">
                            <div class="team-image-wrap">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="team-image">
                            </div>
                            <div class="team-card-body">
                                <h3 class="team-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                                <p class="team-role"><?php echo htmlspecialchars($member['position']); ?></p>
                                <?php if (!empty($member['description'])): ?>
                                    <p class="team-description"><?php echo htmlspecialchars($member['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="team-empty">Notre équipe sera bientôt présentée ici.</p>';
                }
                ?>
            </div>

            <!-- Section Valeurs -->
            <div class="values-section">
                <h3 class="values-title">
                    <i class="bi bi-gem about-icon"></i> Nos Valeurs
                </h3>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-building-gear values-icon"></i>
                        </div>
                        <h4>Expertise</h4>
                        <p>Une équipe qualifiée avec des années d'expérience dans le secteur industriel</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-people-fill values-icon"></i>
                        </div>
                        <h4>Engagement</h4>
                        <p>Un engagement total envers la satisfaction de nos clients</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-lightning-charge-fill values-icon"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p>Toujours à la pointe des dernières technologies</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <div class="section-header">
                <span class="section-kicker"><i class="bi bi-envelope-open"></i> Restons en contact</span>
                <h2>Nous contacter</h2>
                <p class="section-subtitle">Un projet, une question, un besoin d&apos;accompagnement&nbsp;? Envoyez-nous un message, notre &eacute;quipe vous r&eacute;pond rapidement.</p>
            </div>
            <div class="contact-container row g-4 g-lg-5 align-items-start">
                <div class="contact-info col-12 col-lg-5">
                    <h3>Informations de contact</h3>
                    <div class="contact-item">
                        <h4><i class="bi bi-geo-alt-fill contact-icon"></i> Adresse</h4>
                        <p><?php echo getSetting('company_address', '123 Rue de l\'Entreprise'); ?></p>
                    </div>
                    <div class="contact-item">
                        <h4><i class="bi bi-telephone-outbound-fill contact-icon"></i> Téléphone</h4>
                        <p><?php echo getSetting('company_phone', '+243 (0) 1 23 45 67 89'); ?></p>
                    </div>
                    <div class="contact-item">
                        <h4><i class="bi bi-envelope-at-fill contact-icon"></i> Email</h4>
                        <p><?php echo getSetting('company_email', 'contact@congometal.com'); ?></p>
                    </div>
                    <div class="contact-item">
                        <h4><i class="bi bi-clock-history contact-icon"></i> Horaires</h4>
                        <p><?php echo getSetting('company_hours', 'Lun-Ven: 8h-17h<br>Sam: 9h-13h'); ?></p>
                    </div>
                </div>
                <form id="contactForm" class="contact-form col-12 col-lg-7">
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="submit-button">Envoyer le message</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>À propos</h4>
                    <p><?php echo getSetting('company_description', getSetting('company_name', 'Congometal') . ' est une entreprise spécialisée dans les services de qualité.'); ?></p>
                </div>
                <div class="footer-section">
                    <h4>Liens rapides</h4>
                    <ul>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#portfolio">Portfolio</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Nous suivre</h4>
                    <div class="footer-social-links">
                    <?php if (getSetting('social_facebook', '')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('social_facebook')); ?>" target="_blank" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="#1877F2" xmlns="http://www.w3.org/2000/svg"><path d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2v-3h2v-2.3c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.3l-.4 3h-1.9v7A10 10 0 0 0 22 12z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (getSetting('social_twitter', '')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('social_twitter')); ?>" target="_blank" aria-label="Twitter">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="#1DA1F2" xmlns="http://www.w3.org/2000/svg"><path d="M22 5.8c-.6.3-1.2.5-1.9.6.7-.4 1.2-1 1.4-1.8-.6.4-1.3.7-2 .9C18.7 4.7 17.9 4 17 4c-1.6 0-2.8 1.5-2.4 3.1C12.4 7 10 5.9 8.3 4c-.9 1.6-.2 3.7 1.4 4.7-.5 0-1-.2-1.4-.4 0 1.4 1 2.6 2.5 2.9-.5.1-1 .1-1.5 0 .4 1.3 1.6 2.2 3 2.2-1 1-2.2 1.5-3.6 1.5h-.7c1.3.9 2.8 1.4 4.4 1.4 5.3 0 8.2-4.4 8.2-8.2v-.4c.6-.4 1.1-1 1.5-1.6-.6.3-1.2.6-1.9.7z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (getSetting('social_linkedin', '')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('social_linkedin')); ?>" target="_blank" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="#0A66C2" xmlns="http://www.w3.org/2000/svg"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.6h.1c.5-.9 1.7-1.8 3.4-1.8 3.6 0 4.2 2.4 4.2 5.6V21h-4v-5.1c0-1.2 0-2.8-1.7-2.8-1.7 0-2 1.4-2 2.7V21H9z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (getSetting('social_instagram', '')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('social_instagram')); ?>" target="_blank" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="#C13584" xmlns="http://www.w3.org/2000/svg"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 6.2A4.8 4.8 0 1 0 16.8 13 4.8 4.8 0 0 0 12 8.2zm6.4-2.6a1.1 1.1 0 1 1-1.1-1.1 1.1 1.1 0 0 1 1.1 1.1z"/></svg>
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 <?php echo getSetting('company_name', 'Congometal'); ?>. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    
    <!-- About Tabs JavaScript -->
    <script>
        document.querySelectorAll('.about-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active state from all buttons
                document.querySelectorAll('.about-tab-btn').forEach(b => {
                    b.style.borderBottom = '3px solid transparent';
                    b.style.color = '#999';
                });
                
                // Add active state to clicked button
                this.style.borderBottom = '3px solid #667eea';
                this.style.color = '#667eea';
                
                // Hide all tab contents
                document.querySelectorAll('.about-tab-content').forEach(content => {
                    content.style.display = 'none';
                    content.classList.remove('active');
                });
                
                // Show selected tab content
                const tabContent = document.getElementById(`tab-${tabId}`);
                if (tabContent) {
                    tabContent.style.display = 'block';
                    tabContent.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

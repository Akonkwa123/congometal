// Script pour les animations et interactions
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du thème (light / dark)
    const body = document.body;
    const themeToggleBtn = document.getElementById('themeToggle');

    // Déterminer le thème initial
    const savedTheme = window.localStorage ? localStorage.getItem('theme') : null;
    if (savedTheme === 'dark' || savedTheme === 'light') {
        body.setAttribute('data-theme', savedTheme);
    } else {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        body.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            const current = body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', next);
            if (window.localStorage) {
                localStorage.setItem('theme', next);
            }
        });
    }

    // Navbar mobile toggle
    const navToggle = document.getElementById('navbarToggle');
    const mainNav = document.getElementById('mainNav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.toggle('show');
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        });
    }

    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Si c'est un lien interne (#)
            if (href.startsWith('#')) {
                e.preventDefault();
                const section = document.querySelector(href);
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth' });
                    
                    // Marquer le lien comme actif
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            }
        });
    });

    // Marquer le lien actif au scroll
    window.addEventListener('scroll', function() {
        let current = '';
        const sections = document.querySelectorAll('section[id]');
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });

    // Animation des éléments au défilement
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    let observer = null;
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideUp 0.6s ease forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
    }

    if (observer) {
        document.querySelectorAll('.service-card, .portfolio-item, .event-card').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    }

    // Formulaire de contact
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('includes/handle_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert ' + (data.success ? 'alert-success' : 'alert-error');
                alertDiv.textContent = data.message;
                
                contactForm.parentNode.insertBefore(alertDiv, contactForm);
                
                if (data.success) {
                    contactForm.reset();
                    setTimeout(() => alertDiv.remove(), 5000);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // Portfolio Slideshow
    let currentPortfolioIndex = 0;
    const portfolioSlides = document.querySelectorAll('.portfolio-slide');
    
    if (portfolioSlides.length > 1) {
        // Auto-play slideshow every 6 seconds
        setInterval(() => {
            portfolioSlide(1);
        }, 300000);
    }

    // Lightbox for gallery images
    const lightboxOverlay = document.getElementById('lightbox-overlay');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCaption = document.getElementById('lightbox-caption');
    let galleryItems = [];
    let currentLightboxIndex = 0;

    document.querySelectorAll('.gallery-item img').forEach((img, idx) => {
        galleryItems.push({ src: img.getAttribute('data-full') || img.src, alt: img.alt || '' });
        img.closest('.gallery-item').setAttribute('data-gidx', idx);
        img.closest('.gallery-item').addEventListener('click', (e) => {
            e.preventDefault();
            openLightbox(idx);
        });
    });

    function openLightbox(index) {
        if (!galleryItems[index]) return;
        currentLightboxIndex = index;
        lightboxImage.src = galleryItems[index].src;
        lightboxImage.alt = galleryItems[index].alt || '';
        lightboxCaption.textContent = galleryItems[index].alt || '';
        lightboxOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightboxOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function showNext() { openLightbox((currentLightboxIndex + 1) % galleryItems.length); }
    function showPrev() { openLightbox((currentLightboxIndex - 1 + galleryItems.length) % galleryItems.length); }

    // Overlay / controls
    const lbClose = document.getElementById('lightbox-close');
    const lbNext = document.getElementById('lightbox-next');
    const lbPrev = document.getElementById('lightbox-prev');
    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbNext) lbNext.addEventListener('click', (e) => { e.stopPropagation(); showNext(); });
    if (lbPrev) lbPrev.addEventListener('click', (e) => { e.stopPropagation(); showPrev(); });

    if (lightboxOverlay) {
        lightboxOverlay.addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (!lightboxOverlay || !lightboxOverlay.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });

    // Lightbox for event photos only
    const eventLightbox = document.getElementById('event-lightbox');
    const eventLightboxMedia = document.getElementById('event-lightbox-media');
    const eventLightboxClose = document.getElementById('event-lightbox-close');

    function openEventLightbox(mediaEl) {
        if (!eventLightbox || !eventLightboxMedia || !mediaEl) return;
        eventLightboxMedia.innerHTML = '';
        let node = null;
        const tag = mediaEl.tagName.toLowerCase();

        if (tag === 'img') {
            node = document.createElement('img');
            node.src = mediaEl.src;
            node.alt = mediaEl.alt || '';
        }

        if (node) {
            eventLightboxMedia.appendChild(node);
            eventLightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEventLightbox() {
        if (!eventLightbox || !eventLightboxMedia) return;
        eventLightbox.classList.remove('active');
        eventLightboxMedia.innerHTML = '';
        document.body.style.overflow = '';
    }

    function matchesSelector(el, selector) {
        if (!el) return false;
        const fn = el.matches || el.msMatchesSelector || el.webkitMatchesSelector;
        if (!fn) return false;
        return fn.call(el, selector);
    }

    document.addEventListener('click', function(e) {
        let node = e.target;
        let img = null;
        while (node && node !== document) {
            if (matchesSelector(node, '.event-media-item img')) {
                img = node;
                break;
            }
            node = node.parentElement;
        }
        if (!img) return;
        e.preventDefault();
        openEventLightbox(img);
    });

    if (eventLightboxClose) {
        eventLightboxClose.addEventListener('click', closeEventLightbox);
    }

    if (eventLightbox) {
        eventLightbox.addEventListener('click', function(e) {
            if (e.target === eventLightbox) closeEventLightbox();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (!eventLightbox || !eventLightbox.classList.contains('active')) return;
        if (e.key === 'Escape') closeEventLightbox();
    });
});

// Portfolio slide functions
function portfolioSlide(n) {
    const slides = document.querySelectorAll('.portfolio-slide');
    if (slides.length === 0) return;
    
    let index = 0;
    slides.forEach(slide => {
        if (slide.classList.contains('active')) {
            index = parseInt(slide.getAttribute('data-index'));
        }
    });
    
    index = (index + n + slides.length) % slides.length;
    
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
    
    const dots = document.querySelectorAll('.portfolio-dot');
    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[index]) dots[index].classList.add('active');
}

function portfolioGoTo(n) {
    const slides = document.querySelectorAll('.portfolio-slide');
    slides.forEach(slide => slide.classList.remove('active'));
    slides[n].classList.add('active');
    
    const dots = document.querySelectorAll('.portfolio-dot');
    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[n]) dots[n].classList.add('active');
}

// Global lightbox helpers for event images (fallback when inline onclick is used)
function openEventLightboxFromImg(img) {
    if (!img) return;
    const overlay = document.getElementById('event-lightbox');
    const media = document.getElementById('event-lightbox-media');
    if (!overlay || !media) return;
    media.innerHTML = '';
    const node = document.createElement('img');
    node.src = img.src;
    node.alt = img.alt || '';
    media.appendChild(node);
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEventLightbox() {
    const overlay = document.getElementById('event-lightbox');
    const media = document.getElementById('event-lightbox-media');
    if (!overlay || !media) return;
    overlay.classList.remove('active');
    media.innerHTML = '';
    document.body.style.overflow = '';
}


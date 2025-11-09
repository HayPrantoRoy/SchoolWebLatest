

// Toggle mobile menu
function toggleMenu() {
    const navMenu = document.getElementById('navMenu');
    const mobileToggle = document.querySelector('.mobile-toggle');
    
    if (navMenu.classList.contains('active')) {
        navMenu.classList.remove('active');
        mobileToggle.innerHTML = '☰';
    } else {
        navMenu.classList.add('active');
        mobileToggle.innerHTML = '✕';
    }
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const navMenu = document.getElementById('navMenu');
    const mobileToggle = document.querySelector('.mobile-toggle');
    
    if (window.innerWidth <= 768) {
        if (!navMenu.contains(event.target) && !mobileToggle.contains(event.target)) {
            if (navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                mobileToggle.innerHTML = '☰';
            }
        }
    }
});

// Handle dropdown clicks on mobile
document.querySelectorAll('.dropdown > a.dropbtn').forEach(function(dropbtn) {
    dropbtn.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            const dropdown = this.parentElement;
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown').forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active');
        }
    });
});

// Close menu when clicking on a link (mobile only)
document.querySelectorAll('.nav-container a:not(.dropbtn)').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            const navMenu = document.getElementById('navMenu');
            const mobileToggle = document.querySelector('.mobile-toggle');
            navMenu.classList.remove('active');
            mobileToggle.innerHTML = '☰';
        }
    });
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const navMenu = document.getElementById('navMenu');
        const mobileToggle = document.querySelector('.mobile-toggle');
        
        // Reset menu state when resizing to desktop
        if (window.innerWidth > 768) {
            navMenu.classList.remove('active');
            mobileToggle.innerHTML = '☰';
            document.querySelectorAll('.dropdown').forEach(function(dropdown) {
                dropdown.classList.remove('active');
            });
        }
    }, 250);
});

// Prevent scroll when mobile menu is open
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        const navMenu = document.getElementById('navMenu');
        if (navMenu.classList.contains('active') && window.innerWidth <= 768) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
});

// Observe changes to nav menu class
const navMenu = document.getElementById('navMenu');
if (navMenu) {
    observer.observe(navMenu, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== 'javascript:void(0)') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Add loading animation
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

// Optimize images for mobile
function optimizeImagesForMobile() {
    if (window.innerWidth <= 768) {
        const images = document.querySelectorAll('img[data-mobile-src]');
        images.forEach(img => {
            if (img.dataset.mobileSrc) {
                img.src = img.dataset.mobileSrc;
            }
        });
    }
}

// Call on load and resize
optimizeImagesForMobile();
window.addEventListener('resize', optimizeImagesForMobile);

// Touch swipe support for slider (if you have a slider)
let touchStartX = 0;
let touchEndX = 0;

const sliderElement = document.querySelector('.image-slider');
if (sliderElement) {
    sliderElement.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    sliderElement.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
}

function handleSwipe() {
    if (touchEndX < touchStartX - 50) {
        // Swipe left
        if (typeof sliderController !== 'undefined') {
            sliderController.changeSlide(1);
        }
    }
    if (touchEndX > touchStartX + 50) {
        // Swipe right
        if (typeof sliderController !== 'undefined') {
            sliderController.changeSlide(-1);
        }
    }
}

// Lazy loading for images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            }
        });
    });

    // Observe all images with data-src attribute
    document.querySelectorAll('img[data-src]').forEach(function(img) {
        imageObserver.observe(img);
    });
}

// Add viewport height CSS variable for mobile browsers
function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

setViewportHeight();
window.addEventListener('resize', setViewportHeight);

// Prevent zoom on double tap for iOS
let lastTouchEnd = 0;
document.addEventListener('touchend', function(event) {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault();
    }
    lastTouchEnd = now;
}, false);

console.log('Mobile responsive scripts loaded successfully');
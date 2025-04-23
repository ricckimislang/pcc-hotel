// Main JavaScript for StayScope landing page

document.addEventListener('DOMContentLoaded', function() {
    // Handle header transparency on scroll
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            document.querySelector('.logo').style.color = '#1a1a1a';
            document.querySelectorAll('.menu-button button, .login-button button').forEach(button => {
                button.style.color = '#1a1a1a';
                button.style.borderColor = 'rgba(0, 0, 0, 0.1)';
                button.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
            });
        } else {
            header.style.background = 'transparent';
            header.style.boxShadow = 'none';
            document.querySelector('.logo').style.color = '#ffffff';
            document.querySelectorAll('.menu-button button, .login-button button').forEach(button => {
                button.style.color = '#ffffff';
                button.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                button.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
            });
        }
    });

    // Smooth scroll for anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Search button functionality with animation
    const searchButton = document.querySelector('.search-button');
    
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            
            const d = Math.max(this.clientWidth, this.clientHeight);
            ripple.style.width = ripple.style.height = d + 'px';
            
            const rect = this.getBoundingClientRect();
            ripple.style.left = '50%';
            ripple.style.top = '50%';
            ripple.style.transform = 'translate(-50%, -50%)';
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
            
            // Get search parameters
            const destination = document.querySelector('.search-item:nth-child(1) p').textContent;
            const dates = document.querySelector('.search-item:nth-child(2) p').textContent;
            const guests = document.querySelector('.search-item:nth-child(3) p').textContent;
            
            console.log('Search initiated:', {
                destination,
                dates,
                guests
            });
            
            // Animate search box on search
            const searchBox = document.querySelector('.search-box');
            searchBox.classList.add('searching');
            
            setTimeout(() => {
                searchBox.classList.remove('searching');
                // This would normally trigger an API call to fetch search results
                alert('Finding your perfect stay in ' + destination);
            }, 1000);
        });
    }

    // Reveal animations for activity cards
    const revealOnScroll = function() {
        const cards = document.querySelectorAll('.activity-card, .activity-promo');
        cards.forEach(card => {
            const cardTop = card.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (cardTop < windowHeight * 0.85) {
                card.classList.add('revealed');
            }
        });
    };
    
    // Add CSS class for cards
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            .activity-card, .activity-promo {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.8s ease, transform 0.8s ease;
            }
            .activity-card.revealed, .activity-promo.revealed {
                opacity: 1;
                transform: translateY(0);
            }
            .search-box.searching {
                transform: scale(1.03);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }
            .ripple {
                position: absolute;
                background-color: rgba(255, 255, 255, 0.7);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            }
            @keyframes ripple {
                to {
                    transform: translate(-50%, -50%) scale(4);
                    opacity: 0;
                }
            }
        </style>
    `);

    // Run initially and add event listener
    revealOnScroll();
    window.addEventListener('scroll', revealOnScroll);
    
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            
            if (this.classList.contains('search-button') || this.classList.contains('promo-button')) {
                this.style.transform = 'scale(1.1)';
            } else {
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Parallax effect for hero background
    window.addEventListener('scroll', function() {
        const scrollPosition = window.pageYOffset;
        const heroBg = document.querySelector('.hero-bg');
        if (heroBg) {
            heroBg.style.transform = `translateX(-50%) translateY(${scrollPosition * 0.2}px)`;
        }
    });
    
    // Initialize any sliders or carousels
    if (typeof jQuery !== 'undefined' && typeof jQuery().carousel === 'function') {
        $('.carousel').carousel({
            interval: 5000
        });
    }

    initRooms(); // Fetch rooms from the API
});

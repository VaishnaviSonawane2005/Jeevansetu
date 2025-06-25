document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const navMenu = document.querySelector('.nav-menu');
  
  if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener('click', function() {
      this.classList.toggle('active');
      navMenu.classList.toggle('open');
    });
  }

  // Header scroll effect
  const header = document.querySelector('.header');
  if (header) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // Animate elements when they come into view
  const animateOnScroll = function() {
    const elements = document.querySelectorAll('.animate');
    
    elements.forEach(element => {
      const elementPosition = element.getBoundingClientRect().top;
      const windowHeight = window.innerHeight;
      
      if (elementPosition < windowHeight - 100) {
        element.classList.add('animated');
      }
    });
  };

  // Run once on page load
  animateOnScroll();
  
  // Then run on scroll
  window.addEventListener('scroll', animateOnScroll);

  // Animated counter
  const animateCounter = function() {
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    counters.forEach(counter => {
      const target = +counter.getAttribute('data-count');
      const count = +counter.innerText;
      const increment = target / speed;
      
      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(animateCounter, 1);
      } else {
        counter.innerText = target.toLocaleString();
      }
    });
  };

  // Initialize counter when stats section is in view
  const statsSection = document.querySelector('.stats-section');
  if (statsSection) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersectionRatio > 0) {
          animateCounter();
          observer.unobserve(entry.target);
        }
      });
    });
    
    observer.observe(statsSection);
  }

  // Form validation
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      let isValid = true;
      const requiredFields = this.querySelectorAll('[required]');
      
      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('error');
          
          // Create error message if it doesn't exist
          if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
            const errorMessage = document.createElement('p');
            errorMessage.className = 'error-message';
            errorMessage.textContent = 'This field is required';
            errorMessage.style.color = 'var(--danger)';
            errorMessage.style.fontSize = '0.875rem';
            errorMessage.style.marginTop = '0.25rem';
            field.parentNode.insertBefore(errorMessage, field.nextSibling);
          }
        } else {
          field.classList.remove('error');
          const errorMessage = field.nextElementSibling;
          if (errorMessage && errorMessage.classList.contains('error-message')) {
            errorMessage.remove();
          }
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        
        // Scroll to first error
        const firstError = this.querySelector('.error');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstError.focus();
        }
      }
    });
  });

  // Interactive elements hover effects
  const interactiveElements = document.querySelectorAll('.btn, .card, .nav-link');
  interactiveElements.forEach(element => {
    element.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-3px)';
      this.style.boxShadow = 'var(--shadow-md)';
    });
    
    element.addEventListener('mouseleave', function() {
      this.style.transform = '';
      this.style.boxShadow = '';
    });
  });
});
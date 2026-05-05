/* ============================================================
   GURUROCDRILLINGTOOL (GRD) - Main JavaScript
   ============================================================ */

// ---- PRELOADER ----
window.addEventListener('load', () => {
  setTimeout(() => {
    const pre = document.getElementById('preloader');
    if (pre) pre.classList.add('hidden');
  }, 1800);
});

// ---- NAVBAR SCROLL ----
const navbar = document.getElementById('navbar');
const scrollTop = document.querySelector('.scroll-top');

window.addEventListener('scroll', () => {
  if (window.scrollY > 60) {
    navbar && navbar.classList.add('scrolled');
    scrollTop && scrollTop.classList.add('visible');
  } else {
    navbar && navbar.classList.remove('scrolled');
    scrollTop && scrollTop.classList.remove('visible');
  }

  // Active nav link
  const sections = document.querySelectorAll('section[id]');
  sections.forEach(sec => {
    const top = sec.offsetTop - 100;
    const bot = top + sec.offsetHeight;
    if (window.scrollY >= top && window.scrollY < bot) {
      document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
      const link = document.querySelector(`.nav-links a[href="#${sec.id}"]`);
      if (link) link.classList.add('active');
    }
  });
});

// Scroll to top
scrollTop && scrollTop.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ---- MOBILE MENU ----
const hamburger = document.querySelector('.hamburger');
const mobileMenu = document.querySelector('.mobile-menu');

hamburger && hamburger.addEventListener('click', () => {
  hamburger.classList.toggle('open');
  mobileMenu && mobileMenu.classList.toggle('open');
});

document.querySelectorAll('.mobile-menu a').forEach(a => {
  a.addEventListener('click', () => {
    hamburger && hamburger.classList.remove('open');
    mobileMenu && mobileMenu.classList.remove('open');
  });
});

// ---- HERO SLIDER ----
const heroSlides = document.querySelectorAll('.hero-slide');
const heroDots = document.querySelectorAll('.hero-dot');
let currentSlide = 0;

function goToSlide(n) {
  heroSlides[currentSlide] && heroSlides[currentSlide].classList.remove('active');
  heroDots[currentSlide] && heroDots[currentSlide].classList.remove('active');
  currentSlide = (n + heroSlides.length) % heroSlides.length;
  heroSlides[currentSlide] && heroSlides[currentSlide].classList.add('active');
  heroDots[currentSlide] && heroDots[currentSlide].classList.add('active');
}

heroDots.forEach((dot, i) => dot.addEventListener('click', () => goToSlide(i)));

if (heroSlides.length > 0) {
  setInterval(() => goToSlide(currentSlide + 1), 5000);
}

// ---- PARTICLES ----
function createParticles() {
  const container = document.querySelector('.hero-particles');
  if (!container) return;
  for (let i = 0; i < 15; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 4 + 2;
    p.style.cssText = `
      width:${size}px; height:${size}px;
      left:${Math.random() * 100}%;
      bottom:0;
      animation-duration:${8 + Math.random() * 12}s;
      animation-delay:${Math.random() * 8}s;
    `;
    container.appendChild(p);
  }
}
createParticles();

// ---- REVEAL ANIMATIONS ----
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      revealObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ---- COUNTER ANIMATION ----
function animateCounter(el, target, duration = 2000) {
  let start = null;
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = Math.min((timestamp - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.floor(eased * target).toLocaleString();
    if (progress < 1) requestAnimationFrame(step);
    else el.textContent = target.toLocaleString() + (el.dataset.suffix || '');
  };
  requestAnimationFrame(step);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const num = e.target.querySelector('.count-num');
      if (num && !num.dataset.animated) {
        num.dataset.animated = true;
        const target = parseInt(num.dataset.target);
        animateCounter(num, target);
      }
    }
  });
}, { threshold: 0.4 });

document.querySelectorAll('.counter-wrap').forEach(el => counterObserver.observe(el));

// ---- PRODUCT FILTERS ----
const filterBtns = document.querySelectorAll('.filter-btn');
const prodCards = document.querySelectorAll('.prod-card');

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.filter;

    prodCards.forEach(card => {
      if (cat === 'all' || card.dataset.category === cat) {
        card.style.display = '';
        setTimeout(() => { card.style.opacity = '1'; card.style.transform = ''; }, 10);
      } else {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => { card.style.display = 'none'; }, 300);
      }
    });
  });
});

// ---- BRANCH MAP ----
const branchCards = document.querySelectorAll('.branch-card');
branchCards.forEach(card => {
  card.addEventListener('click', () => {
    branchCards.forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    const mapUrl = card.dataset.mapUrl;
    const mapFrame = document.getElementById('branch-map');
    if (mapFrame && mapUrl) {
      mapFrame.src = mapUrl;
    }
  });
});

// Auto-click first branch
const firstBranch = document.querySelector('.branch-card');
if (firstBranch) firstBranch.click();

// ---- ENQUIRY FORM ----
const enquiryForm = document.getElementById('enquiry-form');
if (enquiryForm) {
  enquiryForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = enquiryForm.querySelector('.form-submit');
    const msg = enquiryForm.querySelector('.form-msg');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
      const formData = new FormData(enquiryForm);
      const res = await fetch('api/enquiry.php', { method: 'POST', body: formData });
      const data = await res.json();
      msg.className = 'form-msg ' + (data.success ? 'success' : 'error');
      msg.textContent = data.message;
      if (data.success) enquiryForm.reset();
    } catch {
      msg.className = 'form-msg error';
      msg.textContent = 'Something went wrong. Please try again.';
    }
    btn.disabled = false;
    btn.textContent = 'Send Enquiry';
    msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  });
}

// ---- QUICK ENQUIRE BUTTONS ----
document.querySelectorAll('.enquire-link').forEach(btn => {
  btn.addEventListener('click', () => {
    const productName = btn.closest('.prod-card').querySelector('.prod-name').textContent;
    const contactSection = document.getElementById('contact');
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: 'smooth' });
      setTimeout(() => {
        const msgField = document.getElementById('enquiry-message');
        if (msgField) {
          msgField.value = `I am interested in: ${productName}\n\nPlease provide more details and specifications.`;
          msgField.focus();
        }
      }, 800);
    }
  });
});

// ---- LOAD PRODUCTS FROM API ----
async function loadProducts() {
  try {
    const res = await fetch('api/products.php');
    const data = await res.json();
    if (data.success && data.products.length > 0) {
      renderProducts(data.products);
    }
  } catch (e) {
    console.log('Using static products');
  }
}

function renderProducts(products) {
  const grid = document.getElementById('products-grid');
  if (!grid) return;
  grid.innerHTML = '';

  const categoryIcons = {
    'DTH': '🔩', 'Rock Drilling': '⛏️', 'Mining': '🪨',
    'Top Hammer': '🔨', 'Accessories': '🔧', 'General': '⚙️'
  };

  products.forEach(p => {
    const specs = p.specifications ? JSON.parse(p.specifications) : {};
    const icon = categoryIcons[p.category] || '⚙️';
    const imgHtml = p.image
      ? `<img src="uploads/products/${p.image}" alt="${p.name}" loading="lazy">`
      : `<div class="prod-img-icon">${icon}</div>`;

    const specsHtml = Object.entries(specs).slice(0, 3).map(([k, v]) =>
      `<div class="spec-row"><span class="spec-key">${k}</span><span class="spec-val">${v}</span></div>`
    ).join('');

    const card = document.createElement('div');
    card.className = 'prod-card reveal';
    card.dataset.category = p.category;
    card.innerHTML = `
      <div class="prod-img">
        ${imgHtml}
        <span class="prod-cat-tag">${p.category}</span>
      </div>
      <div class="prod-body">
        <h3 class="prod-name">${p.name}</h3>
        <p class="prod-desc">${p.description || ''}</p>
        ${specsHtml ? `<div class="prod-specs">${specsHtml}</div>` : ''}
        <div class="prod-footer">
          <button class="enquire-link">Enquire Now</button>
        </div>
      </div>`;
    grid.appendChild(card);
    revealObserver.observe(card);
  });

  // Re-attach enquire listeners
  document.querySelectorAll('.enquire-link').forEach(btn => {
    btn.addEventListener('click', () => {
      const productName = btn.closest('.prod-card').querySelector('.prod-name').textContent;
      document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
      setTimeout(() => {
        const f = document.getElementById('enquiry-message');
        if (f) f.value = `Interested in: ${productName}\n\nPlease share specifications and pricing.`;
      }, 800);
    });
  });

  // Update filter buttons
  const categories = [...new Set(products.map(p => p.category))];
  const filterBar = document.querySelector('.filter-bar');
  if (filterBar) {
    filterBar.innerHTML = '<button class="filter-btn active" data-filter="all">All Products</button>';
    categories.forEach(cat => {
      const btn = document.createElement('button');
      btn.className = 'filter-btn';
      btn.dataset.filter = cat;
      btn.textContent = cat;
      btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.prod-card').forEach(card => {
          card.style.display = (cat === 'all' || card.dataset.category === cat) ? '' : 'none';
        });
      });
      filterBar.appendChild(btn);
    });
    const allBtn = filterBar.querySelector('[data-filter="all"]');
    allBtn && allBtn.addEventListener('click', () => {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      allBtn.classList.add('active');
      document.querySelectorAll('.prod-card').forEach(c => c.style.display = '');
    });
  }
}

// Load products on DOM ready
document.addEventListener('DOMContentLoaded', loadProducts);

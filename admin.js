/* ============================================================
   GRD Admin Panel JavaScript
   ============================================================ */

// ---- PAGE NAVIGATION ----
const navItems = document.querySelectorAll('.adm-nav-item');
const pages = document.querySelectorAll('.adm-page');

function showPage(pageId) {
  pages.forEach(p => p.classList.remove('active'));
  navItems.forEach(n => n.classList.remove('active'));
  const page = document.getElementById(pageId);
  if (page) page.classList.add('active');
  const nav = document.querySelector(`[data-page="${pageId}"]`);
  if (nav) nav.classList.add('active');
}

navItems.forEach(item => {
  item.addEventListener('click', () => showPage(item.dataset.page));
});

// Show dashboard by default
showPage('dashboard');

// ---- LOAD DASHBOARD STATS ----
async function loadStats() {
  try {
    const res = await fetch('../api/products.php?stats=1');
    const data = await res.json();
    if (data.success) {
      const counts = data.stats;
      document.getElementById('total-products') && (document.getElementById('total-products').textContent = counts.total || 0);
      document.getElementById('active-products') && (document.getElementById('active-products').textContent = counts.active || 0);
      document.getElementById('total-enquiries') && (document.getElementById('total-enquiries').textContent = counts.enquiries || 0);
    }
  } catch(e) { console.log('Stats load error', e); }
}

// ---- LOAD PRODUCTS TABLE ----
async function loadProductsTable() {
  try {
    const res = await fetch('../api/products.php');
    const data = await res.json();
    if (data.success) renderTable(data.products);
  } catch (e) { showAlert('Failed to load products.', 'error'); }
}

function renderTable(products) {
  const tbody = document.getElementById('products-tbody');
  if (!tbody) return;
  if (!products.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--silver-dark);padding:30px">No products found. Add your first product!</td></tr>';
    return;
  }
  tbody.innerHTML = products.map(p => {
    const specs = p.specifications ? JSON.parse(p.specifications) : {};
    const specText = Object.entries(specs).slice(0, 2).map(([k,v]) => `${k}: ${v}`).join(' | ') || '—';
    const imgHtml = p.image
      ? `<img src="../uploads/products/${p.image}" style="width:50px;height:38px;object-fit:cover">`
      : `<div style="width:50px;height:38px;background:var(--blue-navy);display:flex;align-items:center;justify-content:center;font-size:1.2rem">⚙️</div>`;
    return `<tr>
      <td>${imgHtml}</td>
      <td><strong>${p.name}</strong></td>
      <td><span style="color:var(--blue-bright)">${p.category}</span></td>
      <td style="font-size:0.8rem;color:var(--silver-dark);max-width:200px">${specText}</td>
      <td><span class="badge badge-${p.is_active == 1 ? 'active' : 'inactive'}">${p.is_active == 1 ? 'Active' : 'Hidden'}</span></td>
      <td>
        <div class="action-btns">
          <button class="adm-btn adm-btn-primary adm-btn-sm" onclick="editProduct(${p.id})">✏️ Edit</button>
          <button class="adm-btn adm-btn-danger adm-btn-sm" onclick="deleteProduct(${p.id}, '${p.name}')">🗑️ Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// ---- PRODUCT FORM ----
let specCount = 0;
function addSpecPair(key = '', val = '') {
  specCount++;
  const wrap = document.getElementById('spec-pairs');
  const div = document.createElement('div');
  div.className = 'spec-pair';
  div.id = `spec-${specCount}`;
  div.innerHTML = `
    <input type="text" placeholder="Specification (e.g. Diameter)" value="${key}" style="flex:1">
    <input type="text" placeholder="Value (e.g. 76mm - 200mm)" value="${val}" style="flex:1">
    <button onclick="removeSpec('spec-${specCount}')" title="Remove">✕</button>`;
  wrap.appendChild(div);
}
function removeSpec(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}
function getSpecs() {
  const pairs = document.querySelectorAll('#spec-pairs .spec-pair');
  const specs = {};
  pairs.forEach(pair => {
    const inputs = pair.querySelectorAll('input');
    const k = inputs[0]?.value.trim();
    const v = inputs[1]?.value.trim();
    if (k && v) specs[k] = v;
  });
  return specs;
}

// Image preview
document.getElementById('prod-image')?.addEventListener('change', function() {
  const preview = document.getElementById('img-preview');
  if (this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(this.files[0]);
  }
});

// Open Add Product modal
document.getElementById('btn-add-product')?.addEventListener('click', () => {
  document.getElementById('modal-title').textContent = 'Add New Product';
  document.getElementById('product-form').reset();
  document.getElementById('product-id').value = '';
  document.getElementById('spec-pairs').innerHTML = '';
  document.getElementById('img-preview').style.display = 'none';
  specCount = 0;
  addSpecPair();
  openModal('product-modal');
});

async function editProduct(id) {
  try {
    const res = await fetch(`../api/products.php?id=${id}`);
    const data = await res.json();
    if (!data.success) return showAlert('Product not found', 'error');
    const p = data.product;
    document.getElementById('modal-title').textContent = 'Edit Product';
    document.getElementById('product-id').value = p.id;
    document.getElementById('prod-name').value = p.name || '';
    document.getElementById('prod-category').value = p.category || 'General';
    document.getElementById('prod-desc').value = p.description || '';
    document.getElementById('prod-active').value = p.is_active;
    document.getElementById('spec-pairs').innerHTML = '';
    specCount = 0;
    const specs = p.specifications ? JSON.parse(p.specifications) : {};
    Object.entries(specs).forEach(([k,v]) => addSpecPair(k, v));
    if (!Object.keys(specs).length) addSpecPair();
    const preview = document.getElementById('img-preview');
    if (p.image) { preview.src = `../uploads/products/${p.image}`; preview.style.display = 'block'; }
    else preview.style.display = 'none';
    openModal('product-modal');
  } catch(e) { showAlert('Error loading product', 'error'); }
}

// Save product form
document.getElementById('product-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const saveBtn = document.getElementById('save-product-btn');
  saveBtn.disabled = true;
  saveBtn.textContent = 'Saving...';
  try {
    const formData = new FormData(e.target);
    const specs = getSpecs();
    formData.set('specifications', JSON.stringify(specs));
    const res = await fetch('../api/products.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      closeModal('product-modal');
      showAlert(data.message, 'success');
      loadProductsTable();
      loadStats();
    } else { showAlert(data.message || 'Save failed', 'error'); }
  } catch(e) { showAlert('Error saving product', 'error'); }
  saveBtn.disabled = false;
  saveBtn.textContent = 'Save Product';
});

async function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
  try {
    const formData = new FormData();
    formData.append('delete_id', id);
    const res = await fetch('../api/products.php', { method: 'POST', body: formData });
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'error');
    if (data.success) { loadProductsTable(); loadStats(); }
  } catch(e) { showAlert('Delete failed', 'error'); }
}

// ---- LOAD ENQUIRIES ----
async function loadEnquiries() {
  try {
    const res = await fetch('../api/enquiry.php?list=1');
    const data = await res.json();
    if (data.success) renderEnquiries(data.enquiries);
  } catch(e) { console.log('Enquiry load error'); }
}

function renderEnquiries(enquiries) {
  const tbody = document.getElementById('enquiries-tbody');
  if (!tbody) return;
  if (!enquiries.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--silver-dark);padding:30px">No enquiries yet.</td></tr>';
    return;
  }
  tbody.innerHTML = enquiries.map(e => `<tr>
    <td style="font-size:0.78rem;color:var(--silver-dark)">${new Date(e.created_at).toLocaleDateString('en-IN')}</td>
    <td><strong>${e.name}</strong></td>
    <td style="color:var(--blue-bright)">${e.email}</td>
    <td>${e.phone || '—'}</td>
    <td style="font-size:0.8rem;color:var(--silver-dark)">${(e.product_interest || '—').substring(0,30)}${(e.product_interest||'').length>30?'...':''}</td>
    <td>
      <button class="adm-btn adm-btn-primary adm-btn-sm" onclick="viewEnquiry(${e.id})">View</button>
    </td>
  </tr>`).join('');
}

async function viewEnquiry(id) {
  try {
    const res = await fetch(`../api/enquiry.php?id=${id}`);
    const data = await res.json();
    if (!data.success) return;
    const e = data.enquiry;
    document.getElementById('enq-detail').innerHTML = `
      <div style="display:grid;gap:12px">
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Name</label><p>${e.name}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Email</label><p>${e.email}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Phone</label><p>${e.phone || '—'}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Company</label><p>${e.company || '—'}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Product Interest</label><p>${e.product_interest || '—'}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Message</label><p style="white-space:pre-wrap">${e.message || '—'}</p></div>
        <div><label style="font-family:var(--font-ui);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--silver-dark)">Received</label><p>${new Date(e.created_at).toLocaleString('en-IN')}</p></div>
      </div>`;
    openModal('enquiry-modal');
  } catch(e) { showAlert('Error loading enquiry', 'error'); }
}

// ---- MODAL HELPERS ----
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.querySelectorAll('.modal-close').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.modal-overlay')?.classList.remove('open');
  });
});
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });
});

// ---- ALERT ----
function showAlert(message, type = 'success') {
  const alert = document.getElementById('global-alert');
  if (!alert) return;
  alert.className = `alert alert-${type}`;
  alert.textContent = message;
  alert.style.display = 'block';
  setTimeout(() => { alert.style.display = 'none'; }, 4000);
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadProductsTable();
  loadEnquiries();
  addSpecPair();
});

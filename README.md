# GRD – GURUROCDRILLINGTOOL Website
## Setup Instructions for XAMPP

### 📁 File Structure
```
gururocdrillingtool/
├── index.html          ← Homepage (main)
├── about.html          ← About Us page
├── products.html       ← Products page  
├── contact.html        ← Contact page
├── css/
│   ├── style.css       ← Main frontend styles
│   └── admin.css       ← Admin panel styles
├── js/
│   ├── main.js         ← Frontend JavaScript
│   └── admin.js        ← Admin panel JavaScript
├── images/
│   └── logo.png        ← GRD Logo
├── includes/
│   └── config.php      ← Database & email config ⭐ EDIT THIS
├── api/
│   ├── products.php    ← Products CRUD API
│   └── enquiry.php     ← Enquiry form + email API
├── admin/
│   ├── index.html      ← Admin panel
│   └── login.php       ← Admin login
└── uploads/
    └── products/       ← Uploaded product images
```

---

### 🚀 Quick Setup (XAMPP)

1. **Copy folder** → Paste `gururocdrillingtool` into:
   ```
   C:\xampp\htdocs\gururocdrillingtool\
   ```

2. **Start XAMPP** → Start Apache + MySQL

3. **Database auto-creates** — No manual setup needed!
   - Visit `http://localhost/gururocdrillingtool/` 
   - Database `grd_website` is created automatically on first load
   - Sample products are seeded automatically

4. **Visit the website:**
   - 🌐 Frontend: `http://localhost/gururocdrillingtool/`
   - 🔐 Admin: `http://localhost/gururocdrillingtool/admin/login.php`

---

### 🔐 Admin Panel
- **URL:** `http://localhost/gururocdrillingtool/admin/login.php`
- **Username:** `admin`
- **Password:** `GRD@2024!`

> ⚠️ Change password in `admin/login.php` before going live!

### ⭐ Key Feature: Real-time Product Management
1. Login to admin panel
2. Go to **Products** tab
3. Click **Add New Product** — fill name, category, specs, upload image
4. Click **Save** — the product appears on the website **instantly**!

---

### 📧 Email Configuration
Edit `includes/config.php`:
```php
define('SMTP_TO',   'your-email@domain.com');  // Where enquiries are sent
define('SMTP_FROM', 'noreply@yourdomain.com'); // From email
```

For production, use PHPMailer with SMTP for reliable email delivery.

---

### 📍 Update Branch Information
Edit `index.html` — find the `#branches` section and update:
- Branch names and addresses
- Phone numbers and emails
- Google Maps embed URLs (get from maps.google.com → Share → Embed)

### 📞 Update Contact Details
Search for `+91 80000 00001` in all HTML files and replace with real numbers.

---

### 🌐 Production Deployment
1. Upload all files to your hosting (cPanel public_html)
2. Create MySQL database via cPanel
3. Update `includes/config.php` with real DB credentials
4. Install SSL certificate
5. Update email settings

**Built for XAMPP | PHP 7.4+ | MySQL 5.7+**

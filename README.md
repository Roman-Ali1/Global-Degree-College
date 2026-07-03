# Global Degree College — College Management & Admission System

A full-stack college management website with online admission system and admin panel, built with Core PHP 8+, MySQL, Bootstrap 5, and vanilla JavaScript.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Vanilla JS, AOS, Lightbox2 |
| Backend | Core PHP 8.1+ (no framework) |
| Database | MySQL 8.0 / MariaDB |
| Server | Apache / XAMPP |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Inter + Playfair Display) |

---

## Project Structure
Global-Degree-College/
├── index.php                   # Homepage (12 sections, dynamic)
├── about.php                   # About page
├── courses.php                 # Programs with filter tabs
├── admissions.php              # Online admission form
├── gallery.php                 # Masonry gallery with lightbox
├── news.php                    # News listing
├── news-detail.php             # Single news article
├── events.php                  # Events listing
├── events-detail.php           # Single event
├── contact.php                 # Contact form
├── scholarships.php            # Scholarships listing
├── hostel.php                  # Hostel information
├── .htaccess                   # Security + rewrite rules
│
├── assets/
│   ├── css/
│   │   ├── style.css           # Global design system (navy/gold tokens)
│   │   ├── admin.css           # Admin panel styles
│   │   ├── home.css            # Homepage-specific styles
│   │   ├── about.css           # About page styles
│   │   ├── courses.css         # Courses page styles
│   │   ├── admissions.css      # Admission form styles
│   │   └── gallery.css         # Gallery masonry styles
│   ├── js/
│   │   ├── main.js             # Global JS (AOS, counter, navbar)
│   │   ├── admin.js            # Admin sidebar + helpers
│   │   └── gallery.js          # Gallery filter + lightbox config
│   └── images/
│       ├── logo/               # College logo + favicon
│       ├── hero/               # Hero slider images (slide-1.jpg, etc.)
│       ├── about/              # About page photos
│       └── defaults/           # Placeholder images
│
├── includes/
│   ├── config/
│   │   ├── app.php             # Bootstrapper (loads everything, starts session)
│   │   ├── constants.php       # BASE_URL, paths, limits
│   │   └── db.php              # PDO singleton + query helpers
│   ├── classes/
│   │   ├── Auth.php            # Login, logout, session guard, brute-force
│   │   ├── CSRF.php            # Token generation + verification
│   │   └── Uploader.php        # Secure file upload (MIME validation)
│   ├── helpers/
│   │   ├── functions.php       # slugify, paginate, flash, redirect, URL helpers
│   │   └── sanitize.php        # h(), cleanString, cleanEmail, post(), get()
│   └── templates/
│       ├── header.php          # Public navbar + alert bar + flash messages
│       └── footer.php          # Public footer + scripts
│
├── admin/
│   ├── index.php               # Dashboard
│   ├── login.php               # Admin login
│   ├── logout.php              # Session destroy
│   ├── modules/
│   │   ├── admissions.php      # Applications list + detail + status update
│   │   ├── courses.php         # Course CRUD
│   │   ├── faculty.php         # Faculty CRUD + photo upload
│   │   ├── gallery.php         # Image upload + categorize + delete
│   │   ├── news.php            # News CRUD + image + status
│   │   ├── events.php          # Events CRUD + image
│   │   ├── messages.php        # Contact inbox
│   │   ├── testimonials.php    # Approve/reject + home toggle
│   │   ├── scholarships.php    # Scholarship CRUD
│   │   ├── hostel.php          # Hostel CRUD
│   │   └── settings.php        # Tabbed site config
│   └── templates/
│       ├── admin-header.php    # Sidebar + topbar (opens HTML)
│       └── admin-footer.php    # Scripts + closes HTML
│
├── uploads/                    # All user-uploaded files (excluded from git)
│   ├── admissions/             # Student photos from admission forms
│   ├── gallery/                # Gallery images
│   ├── faculty/                # Faculty profile photos
│   ├── news/                   # News featured images
│   └── events/                 # Event banner images
│
└── sql/
└── fvc_database.sql        # Complete DB schema + seed data

---

## Local Setup (XAMPP)

### 1. Prerequisites
- XAMPP with Apache + MySQL running
- PHP 8.1 or higher
- Modules enabled: `mod_rewrite`, `mod_headers`, `pdo_mysql`

### 2. Installation

```bash
# Clone or extract into XAMPP htdocs
C:\xampp\htdocs\Global-Degree-College\

# Or on Linux/Mac
/opt/lampp/htdocs/Global-Degree-College/
```

### 3. Database

1. Open `http://localhost/phpmyadmin`
2. Create database: `fvc_db` — collation `utf8mb4_unicode_ci`
3. Import: `sql/fvc_database.sql`
4. Verify 13 tables created with seed data

### 4. Configuration

Open `includes/config/constants.php` and update:

```php
// Match your exact folder name (case-sensitive)
define('BASE_URL', 'http://localhost/Global-Degree-College');

// DB credentials (defaults work for XAMPP)
define('DB_USER', 'root');
define('DB_PASS', '');
```

Open `.htaccess` and update:
```apache
RewriteBase /Global-Degree-College/
```

### 5. Admin Password

Create `make_hash.php` in project root:
```php
<?php echo password_hash('YourPassword@123', PASSWORD_BCRYPT, ['cost' => 12]);
```

Visit `http://localhost/Global-Degree-College/make_hash.php`, copy the hash, then run in phpMyAdmin:

```sql
UPDATE admins
SET password = 'PASTE_HASH_HERE'
WHERE email = 'admin@futurevision.edu.pk';
```

**Delete `make_hash.php` immediately after.**

### 6. Folder Permissions

On Linux/Mac, ensure uploads directory is writable:
```bash
chmod -R 755 uploads/
```

### 7. Verify

Visit `http://localhost/Global-Degree-College/` — homepage should load.
Visit `http://localhost/Global-Degree-College/admin/login.php` — admin panel.

---

## Admin Panel

**URL:** `http://localhost/Global-Degree-College/admin/`

| Module | Path |
|---|---|
| Dashboard | `admin/index.php` |
| Admissions | `admin/modules/admissions.php` |
| Courses | `admin/modules/courses.php` |
| Faculty | `admin/modules/faculty.php` |
| Gallery | `admin/modules/gallery.php` |
| News | `admin/modules/news.php` |
| Events | `admin/modules/events.php` |
| Messages | `admin/modules/messages.php` |
| Testimonials | `admin/modules/testimonials.php` |
| Scholarships | `admin/modules/scholarships.php` |
| Hostel | `admin/modules/hostel.php` |
| Settings | `admin/modules/settings.php` |

### Admin Roles

| Role | Access |
|---|---|
| `super_admin` | Everything including settings + admin management |
| `editor` | Content only (news, events, gallery, testimonials) |
| `admission_officer` | Admissions module only |

---

## Database Schema (13 Tables)

| Table | Purpose |
|---|---|
| `admins` | Admin users with role + brute-force lockout |
| `students` | Student master profiles |
| `admission_applications` | Online form submissions with computed percentage |
| `courses` | Programs offered (FSc, ICS, FA, I.Com) |
| `faculty` | Teaching staff with photo + home page flag |
| `news` | Articles with draft/published/archived status |
| `events` | Events with type, status, venue |
| `gallery` | Images with category |
| `testimonials` | Student reviews with approval workflow |
| `contact_messages` | Public contact form submissions |
| `scholarships` | Scholarship programs |
| `hostel_information` | Hostel details with JSON facilities |
| `settings` | Key-value site config (editable from admin) |

---

## Security Implementation

| Threat | Defense |
|---|---|
| SQL Injection | PDO prepared statements on every query — no raw string interpolation |
| XSS | `h()` wrapper (`htmlspecialchars`) on every echoed variable |
| CSRF | Token on every POST form, verified before processing |
| Brute Force | Login attempt counter + timed lockout (5 attempts → 15 min lock) |
| Session Hijacking | `session_regenerate_id()` on login, `httponly` + `samesite` cookies |
| File Upload Abuse | MIME-type verified via `finfo` (not extension), renamed with `uniqid` |
| Directory Traversal | `.htaccess` blocks direct access to `/includes/` and `/sql/` |
| Clickjacking | `X-Frame-Options: SAMEORIGIN` header set globally |
| Info Disclosure | `display_errors Off` in production, `ServerSignature Off` |

---

## Key Design Decisions

**Why Core PHP over a framework?**
The project scope (single institution, fixed modules) doesn't justify framework overhead. The `includes/classes/` layer provides the same separation of concerns — Database singleton, Auth class, Uploader — without Composer dependencies or routing complexity.

**Why PDO over MySQLi?**
Named placeholders, cleaner exception handling, and DB-agnostic code if migration is ever needed.

**Why CSS `columns` for masonry over Masonry.js?**
No JS dependency, no layout recalculation on filter, works immediately on load. Trade-off: unequal row heights, which is acceptable for a photo gallery.

**Why key-value `settings` table?**
Lets the admin change phone numbers, social links, logo, and admission open/close toggle at runtime without touching code or doing a deployment.

**Why soft deletes (`is_active` flag) instead of hard DELETE?**
Prevents accidental data loss on critical records (applications, students). Hard deletes are used only on gallery images and messages where permanent removal is genuinely desired.

---

## Phases Completed

| Phase | Status | Description |
|---|---|---|
| 1 | ✅ | Project analysis, SRS, architecture |
| 2 | ✅ | MySQL schema (13 tables, indexes, seed data) |
| 3 | ✅ | Project structure, config, DB class, Auth, CSRF, helpers, templates |
| 4 | ✅ | Homepage (12 sections, dynamic DB content) |
| 5 | ✅ | About page (history, mission, chairman/principal, timeline) |
| 6 | ✅ | Courses page (cards, filter tabs, comparison table, pathways) |
| 7 | ✅ | Online admission form (validation, file upload, CSRF, duplicate check) |
| 8 | ✅ | Admin panel (dashboard, all 11 modules, login, settings) |
| 9 | ✅ | Gallery (masonry, lightbox, client-side filter, URL sync) |
| 10 | ⏳ | News & Events public pages |
| 11 | ⏳ | Contact system |
| 12 | ⏳ | Security hardening |
| 13 | ⏳ | Deployment guide |

---

## Remaining Work

- Phase 10: `news.php`, `news-detail.php`, `events.php`, `events-detail.php`
- Phase 11: `contact.php` with form submission
- Phase 12: Security audit + rate limiting + input hardening review
- Phase 13: cPanel deployment, SSL, backup strategy

---

## License

This project was built for **Global Degree College, Peshawar** as a real-world college management system. Not licensed for redistribution. 
<?php
/**
 * Future Vision College – Public Site Header
 * Save to: includes/templates/header.php
 *
 * Variables expected (set in each page before including this):
 *   $pageTitle       – <title> tag content
 *   $pageDescription – meta description (optional, falls back to setting)
 *   $bodyClass       – extra class on <body> (optional)
 */

// Ensure app is bootstrapped
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

$pageTitle       = $pageTitle ?? setting('site_name', APP_NAME);
$pageDescription = $pageDescription ?? setting('meta_description', '');
$bodyClass       = $bodyClass ?? '';

$siteName   = setting('site_name',  'Global Degree College');
$siteTagline = setting('site_tagline', 'Shaping Leaders of Tomorrow');
$siteLogo   = setting('site_logo',  'assets/images/logo/logo.png');
$admissionOpen = setting('admission_open', '1') === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($pageDescription) ?>">
    <meta name="keywords"    content="<?= h(setting('meta_keywords')) ?>">
    <meta name="theme-color" content="#1a3c6e">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= h($pageTitle) ?> | <?= h($siteName) ?>">
    <meta property="og:description" content="<?= h($pageDescription) ?>">
    <meta property="og:type"        content="website">

    <title><?= h($pageTitle) ?> | <?= h($siteName) ?></title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- AOS Animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
<?php if (!empty($extraCss)): foreach ($extraCss as $cssFile): ?>
<link href="<?= BASE_URL ?>/assets/css/<?= h($cssFile) ?>" rel="stylesheet">
<?php endforeach; endif; ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
</head>
<body class="<?= h($bodyClass) ?>">

<!-- ── Admission Alert Bar ─────────────────────────────────── -->
<?php if ($admissionOpen): ?>
<div class="admission-alert-bar" id="admissionAlert">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>
            <i class="fas fa-bullhorn me-2"></i>
            <strong>Admissions Open <?= date('Y') ?>–<?= date('y') + 1 ?></strong>
            — Last date to apply: <strong>31st August <?= date('Y') ?></strong>
        </span>
        <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-sm btn-warning fw-semibold">
            Apply Now <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ── Main Navbar ────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-dark fvc-navbar sticky-top" id="mainNav">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/<?= h($siteLogo) ?>"
                 alt="<?= h($siteName) ?> Logo"
                 height="48" width="48"
                 class="navbar-logo"
                 onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/defaults/placeholder.png'">
            <div class="brand-text">
                <span class="brand-name"><?= h($siteName) ?></span>
                <span class="brand-tagline"><?= h($siteTagline) ?></span>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('index') ?>"
                       href="<?= BASE_URL ?>/">
                        <i class="fas fa-home me-1 d-lg-none"></i>Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('about') ?>"
                       href="<?= BASE_URL ?>/about.php">
                        <i class="fas fa-university me-1 d-lg-none"></i>About
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('courses') ?>"
                       href="<?= BASE_URL ?>/courses.php">
                        <i class="fas fa-graduation-cap me-1 d-lg-none"></i>Programs
                    </a>
                </li>

                <!-- Dropdown: Campus Life -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        Campus Life
                    </a>
                    <ul class="dropdown-menu fvc-dropdown shadow-lg">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/gallery.php">
                                <i class="fas fa-images me-2 text-primary"></i>Gallery
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/events.php">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>Events
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/hostel.php">
                                <i class="fas fa-building me-2 text-primary"></i>Hostel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/scholarships.php">
                                <i class="fas fa-award me-2 text-primary"></i>Scholarships
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('news') ?>"
                       href="<?= BASE_URL ?>/news.php">
                        <i class="fas fa-newspaper me-1 d-lg-none"></i>News
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('contact') ?>"
                       href="<?= BASE_URL ?>/contact.php">
                        <i class="fas fa-envelope me-1 d-lg-none"></i>Contact
                    </a>
                </li>

                <?php if ($admissionOpen): ?>
                <li class="nav-item ms-lg-2">
                    <a class="btn fvc-btn-primary nav-link"
                       href="<?= BASE_URL ?>/admissions.php">
                        <i class="fas fa-file-alt me-1"></i>Apply Now
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div>

    </div>
</nav>
<!-- ── End Navbar ─────────────────────────────────────────────── -->

<?php
// Display flash message if set
$flash = getFlash();
if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= h($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle') ?> me-2"></i>
        <?= h($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<?php
/**
 * Admin Panel Header + Sidebar
 * Save to: admin/templates/admin-header.php
 */
Auth::requireLogin();
$adminUser    = Auth::user();
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');
$currentDir   = basename(dirname($_SERVER['PHP_SELF']));

// Unread counts for badges
$db = Database::getInstance();
$unreadMessages    = (int)$db->fetchColumn("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$pendingAdmissions = (int)$db->fetchColumn("SELECT COUNT(*) FROM admission_applications WHERE status = 'pending'");

function adminNavItem(string $href, string $icon, string $label, string $page, string $currentPage, int $badge = 0): void
{
    $active = ($currentPage === $page) ? 'active' : '';
    echo "<li class='nav-item'>";
    echo "<a href='{$href}' class='admin-nav-link {$active}'>";
    echo "<i class='fas {$icon}'></i><span>{$label}</span>";
    if ($badge > 0) echo "<span class='nav-badge'>{$badge}</span>";
    echo "</a></li>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($adminPageTitle ?? 'Dashboard') ?> | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-body">

<!-- ── Mobile overlay ───────────────────────────────────────── -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ════════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════════ -->
<aside class="admin-sidebar" id="adminSidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <img src="<?= BASE_URL ?>/<?= h(setting('site_logo','assets/images/logo/logo.png')) ?>"
             alt="Logo" height="36"
             onerror="this.style.display='none'">
        <div>
            <span class="sidebar-site-name"><?= h(setting('site_name','GDC')) ?></span>
            <span class="sidebar-panel-label">Admin Panel</span>
        </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">
        <ul class="sidebar-nav-list">

            <li class="nav-group-label">Main</li>
            <?php adminNavItem(ADMIN_URL.'/index.php',       'fa-tachometer-alt', 'Dashboard',   'index',       $currentPage) ?>
            <?php adminNavItem(ADMIN_URL.'/modules/admissions.php', 'fa-file-alt', 'Admissions', 'admissions',  $currentPage, $pendingAdmissions) ?>

            <li class="nav-group-label">Academic</li>
            <?php adminNavItem(ADMIN_URL.'/modules/courses.php',  'fa-graduation-cap', 'Courses',  'courses',  $currentPage) ?>
            <?php adminNavItem(ADMIN_URL.'/modules/faculty.php',  'fa-chalkboard-teacher', 'Faculty', 'faculty', $currentPage) ?>

            <li class="nav-group-label">Content</li>
            <?php adminNavItem(ADMIN_URL.'/modules/news.php',     'fa-newspaper',    'News',     'news',     $currentPage) ?>
            <?php adminNavItem(ADMIN_URL.'/modules/events.php',   'fa-calendar-alt', 'Events',   'events',   $currentPage) ?>
            <?php adminNavItem(ADMIN_URL.'/modules/gallery.php',  'fa-images',       'Gallery',  'gallery',  $currentPage) ?>
            <?php adminNavItem(ADMIN_URL.'/modules/testimonials.php','fa-star',      'Testimonials','testimonials',$currentPage) ?>

            <li class="nav-group-label">Other</li>
            <?php adminNavItem(ADMIN_URL.'/modules/messages.php', 'fa-envelope',     'Messages', 'messages', $currentPage, $unreadMessages) ?>

            <li class="nav-group-label">System</li>
            <?php adminNavItem(ADMIN_URL.'/modules/settings.php', 'fa-cog',          'Settings', 'settings', $currentPage) ?>

        </ul>
    </nav>

    <!-- Sidebar footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($adminUser['name'], 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name"><?= h($adminUser['name']) ?></span>
                <span class="sidebar-user-role"><?= h(str_replace('_',' ', $adminUser['role'])) ?></span>
            </div>
        </div>
        <a href="<?= ADMIN_URL ?>/logout.php" class="sidebar-logout" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

</aside>

<!-- ════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
═══════════════════════════════════════════════════════════════ -->
<div class="admin-main" id="adminMain">

    <!-- Top bar -->
    <header class="admin-topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-title"><?= h($adminPageTitle ?? 'Dashboard') ?></div>

        <div class="topbar-actions">
            <?php if ($unreadMessages > 0): ?>
            <a href="<?= ADMIN_URL ?>/modules/messages.php" class="topbar-icon-btn" title="Messages">
                <i class="fas fa-envelope"></i>
                <span class="topbar-badge"><?= $unreadMessages ?></span>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/" target="_blank" class="topbar-icon-btn" title="View Site">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <a href="<?= ADMIN_URL ?>/logout.php" class="topbar-icon-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="px-4 pt-3">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : h($flash['type']) ?> alert-dismissible fade show py-2 small">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= h($flash['message']) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page content starts here -->
    <div class="admin-content">
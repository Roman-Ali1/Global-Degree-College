<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Gallery';
$pageDescription = 'Browse photos from Global Degree College — campus life, events, labs, sports and more.';
$extraCss        = ['gallery.css'];
$extraJs  = ['gallery.js'];


$db = Database::getInstance();

// Active category filter
$activeCategory = get('category');
$defaultGalleryCategories = ['Campus', 'Events', 'Sports', 'General'];

// Fetch all distinct categories for filter tabs and merge with defaults so buttons are always available.
$categoryRows = $db->fetchAll(
    "SELECT DISTINCT category FROM gallery WHERE is_active = 1 ORDER BY category"
);
$categoryNames = [];
foreach ($categoryRows as $row) {
    $name = trim((string)($row['category'] ?? ''));
    if ($name !== '') {
        $categoryNames[] = $name;
    }
}
$categories = array_values(array_unique(array_merge($defaultGalleryCategories, $categoryNames)));

// Fetch images — filtered or all
$params = [];
$where  = 'is_active = 1';
if ($activeCategory) {
    $where   .= ' AND LOWER(category) = LOWER(?)';
    $params[] = $activeCategory;
}

$images = $db->fetchAll(
    "SELECT * FROM gallery WHERE $where ORDER BY created_at DESC",
    $params
);

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     PAGE HERO
═══════════════════════════════════════════════════════════════ -->
<section class="page-hero">
    <div class="container">
        <h1>Photo Gallery</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">Gallery</li>
            </ol>
        </nav>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     CATEGORY FILTER
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad-sm bg-off-white">
    <div class="container">
        <div class="gallery-filter-wrap" data-aos="fade-up">
            <button type="button"
                    class="gallery-filter-btn <?= !$activeCategory ? 'active' : '' ?>"
                    data-filter="all"
                    aria-pressed="<?= !$activeCategory ? 'true' : 'false' ?>">
                All Photos
                <span class="filter-count"><?= count($images) ?></span>
            </button>
            <?php foreach ($categories as $cat): ?>
            <button type="button"
                    class="gallery-filter-btn <?= strcasecmp((string)$activeCategory, (string)$cat) === 0 ? 'active' : '' ?>"
                    data-filter="<?= h($cat) ?>"
                    aria-pressed="<?= strcasecmp((string)$activeCategory, (string)$cat) === 0 ? 'true' : 'false' ?>">
                <?= h($cat) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     MASONRY GALLERY
═══════════════════════════════════════════════════════════════ -->
<section class="section-pad">
    <div class="container">

        <?php if (empty($images)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-images fa-3x mb-3 opacity-25"></i>
            <p>No images found<?= $activeCategory ? ' in this category' : '' ?>.</p>
            <?php if ($activeCategory): ?>
            <a href="<?= BASE_URL ?>/gallery.php" class="btn btn-fvc-outline btn-sm">View All</a>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- Masonry grid -->
        <div class="masonry-grid" id="masonryGrid">
            <?php foreach ($images as $i => $img): ?>
            <div class="masonry-item" data-category="<?= h($img['category']) ?>"
                 data-aos="fade-up" data-aos-delay="<?= min($i * 40, 300) ?>">

                <a href="<?= uploadUrl('gallery', $img['filename']) ?>"
                   data-lightbox="gallery"
                   data-title="<?= h($img['title'] ?? $img['category']) ?>">

                    <img src="<?= uploadUrl('gallery', $img['filename']) ?>"
                         alt="<?= h($img['title'] ?? $img['category']) ?>"
                         loading="lazy"
                         onerror="this.closest('.masonry-item').style.display='none'">

                    <div class="masonry-overlay">
                        <div class="masonry-overlay-inner">
                            <i class="fas fa-search-plus"></i>
                            <?php if ($img['title']): ?>
                            <span><?= h($img['title']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="masonry-category-tag"><?= h($img['category']) ?></div>

                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Image count -->
        <p class="text-center text-muted small mt-4" id="galleryCount">
            Showing <strong id="visibleCount"><?= count($images) ?></strong> photos
            <?= $activeCategory ? 'in <strong>' . h($activeCategory) . '</strong>' : '' ?>
        </p>

        <?php endif; ?>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     CTA
═══════════════════════════════════════════════════════════════ -->
<section class="contact-cta-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h3 class="text-white mb-1">Want to See More of Campus Life?</h3>
                <p class="text-white-50 mb-0">Visit us in person — or apply now to become part of our story.</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-light me-2">
                    Plan a Visit
                </a>
                <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-gold">
                    Apply Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
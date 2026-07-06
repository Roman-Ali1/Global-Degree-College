<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'News & Announcements';
$pageDescription = 'Latest news, announcements and updates from Global Degree College, Peshawar.';
$extraCss        = ['news.css'];

$db = Database::getInstance();

// Filters
$category = get('category');
$search   = get('q');

$where  = ["n.status = 'published'"];
$params = [];

if ($category) {
    $where[]  = 'n.category = ?';
    $params[] = $category;
}

if ($search) {
    $where[]  = '(n.title LIKE ? OR n.excerpt LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereStr = implode(' AND ', $where);

// Pagination
$total = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM news n WHERE $whereStr", $params
);
$pager = paginate($total, 9);

$newsList = $db->fetchAll(
    "SELECT n.*, a.full_name AS author_name
     FROM news n
     LEFT JOIN admins a ON n.author_id = a.id
     WHERE $whereStr
     ORDER BY n.published_at DESC
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}",
    $params
);

// Featured news (top of page, only on first page with no filter)
$featured = null;
if (!$category && !$search && $pager['current'] === 1) {
    $featured = $db->fetch(
        "SELECT n.*, a.full_name AS author_name
         FROM news n
         LEFT JOIN admins a ON n.author_id = a.id
         WHERE n.status = 'published' AND n.is_featured = 1
         ORDER BY n.published_at DESC LIMIT 1"
    );
    // Remove featured from main list if it appears there
    if ($featured) {
        $newsList = array_filter(
            $newsList,
            fn($item) => (int)$item['id'] !== (int)$featured['id']
        );
    }
}

// All categories for filter
$categories = $db->fetchAll(
    "SELECT DISTINCT category FROM news
     WHERE status = 'published' AND category IS NOT NULL
     ORDER BY category"
);

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="container">
        <h1>News & Announcements</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">News</li>
            </ol>
        </nav>
    </div>
</section>


<!-- SEARCH + FILTER BAR -->
<section class="section-pad-sm bg-off-white">
    <div class="container">
        <div class="row align-items-center g-3">
            <!-- Search -->
            <div class="col-md-5">
                <form method="GET" class="news-search-form">
                    <input type="text" name="q" class="form-control"
                           value="<?= h($search) ?>"
                           placeholder="Search news...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <!-- Category filter -->
            <div class="col-md-7">
                <div class="news-category-filters">
                    <a href="<?= BASE_URL ?>/news.php"
                       class="news-cat-btn <?= !$category ? 'active' : '' ?>">
                        All
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/news.php?category=<?= urlencode($cat['category']) ?>"
                       class="news-cat-btn <?= $category === $cat['category'] ? 'active' : '' ?>">
                        <?= h($cat['category']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- FEATURED NEWS -->
<?php if ($featured): ?>
<section class="section-pad-sm">
    <div class="container">
        <div class="featured-news-card" data-aos="fade-up">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6">
                    <div class="featured-news-img">
                        <img src="<?= uploadUrl('news', $featured['featured_image']) ?>"
                             alt="<?= h($featured['title']) ?>"
                             onerror="this.closest('.featured-news-img').style.background='var(--fvc-navy)'">
                        <span class="featured-label">
                            <i class="fas fa-star me-1"></i>Featured
                        </span>
                        <?php if ($featured['category']): ?>
                        <span class="featured-cat"><?= h($featured['category']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="featured-news-body">
                        <div class="news-meta">
                            <span>
                                <i class="far fa-calendar me-1"></i>
                                <?= formatDate($featured['published_at'] ?? $featured['created_at']) ?>
                            </span>
                            <?php if ($featured['author_name']): ?>
                            <span>
                                <i class="far fa-user me-1"></i><?= h($featured['author_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <h2 class="featured-news-title"><?= h($featured['title']) ?></h2>
                        <p class="featured-news-excerpt">
                            <?= h(truncate($featured['excerpt'] ?? strip_tags($featured['body']), 200)) ?>
                        </p>
                        <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($featured['slug']) ?>"
                           class="btn btn-fvc-primary">
                            Read Full Story <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- NEWS GRID -->
<section class="section-pad <?= $featured ? 'pt-0' : '' ?>">
    <div class="container">

        <?php if (empty($newsList) && !$featured): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i>
            <p>No news articles found<?= $search ? ' for "' . h($search) . '"' : '' ?>.</p>
            <?php if ($search || $category): ?>
            <a href="<?= BASE_URL ?>/news.php" class="btn btn-fvc-outline btn-sm">View All News</a>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <div class="row g-4">
            <?php foreach ($newsList as $i => $item): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <article class="news-card-v2 h-100">
                    <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($item['slug']) ?>"
                       class="news-card-img-link">
                        <img src="<?= uploadUrl('news', $item['featured_image']) ?>"
                             alt="<?= h($item['title']) ?>"
                             loading="lazy"
                             onerror="this.closest('.news-card-img-link').classList.add('no-img')">
                        <?php if ($item['category']): ?>
                        <span class="news-card-category"><?= h($item['category']) ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="news-card-body">
                        <div class="news-meta small">
                            <span>
                                <i class="far fa-calendar"></i>
                                <?= formatDate($item['published_at'] ?? $item['created_at'], 'd M Y') ?>
                            </span>
                            <span>
                                <i class="far fa-eye"></i>
                                <?= number_format((int)$item['views']) ?> views
                            </span>
                        </div>
                        <h5 class="news-card-title">
                            <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($item['slug']) ?>">
                                <?= h($item['title']) ?>
                            </a>
                        </h5>
                        <p class="news-card-excerpt">
                            <?= h(truncate($item['excerpt'] ?? strip_tags($item['body']), 100)) ?>
                        </p>
                        <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($item['slug']) ?>"
                           class="news-read-more">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pager['pages'] > 1): ?>
        <div class="d-flex justify-content-center gap-2 mt-5">
            <?php for ($p = 1; $p <= $pager['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>"
               class="btn <?= $p === $pager['current'] ? 'btn-fvc-primary' : 'btn-fvc-outline' ?> btn-sm">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
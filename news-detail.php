<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$db   = Database::getInstance();
$slug = get('slug');

if (empty($slug) || !validateSlug($slug)) {
    header('Location: ' . BASE_URL . '/news.php');
    exit;
}

// Fetch the article
$article = $db->fetch(
    "SELECT n.*, a.full_name AS author_name
     FROM news n
     LEFT JOIN admins a ON n.author_id = a.id
     WHERE n.slug = ? AND n.status = 'published'",
    [$slug]
);

if (!$article) {
    http_response_code(404);
    header('Location: ' . BASE_URL . '/news.php');
    exit;
}

// Increment view count
$db->execute("UPDATE news SET views = views + 1 WHERE id = ?", [$article['id']]);

// Related news (same category, exclude current)
$related = $db->fetchAll(
    "SELECT id, title, slug, featured_image, published_at, category
     FROM news
     WHERE status = 'published'
       AND id != ?
       AND category = ?
     ORDER BY published_at DESC LIMIT 3",
    [$article['id'], $article['category'] ?? '']
);

// Fallback: latest news if no category match
if (empty($related)) {
    $related = $db->fetchAll(
        "SELECT id, title, slug, featured_image, published_at, category
         FROM news
         WHERE status = 'published' AND id != ?
         ORDER BY published_at DESC LIMIT 3",
        [$article['id']]
    );
}

$pageTitle       = $article['title'];
$pageDescription = truncate($article['excerpt'] ?? strip_tags($article['body']), 160);
$extraCss        = ['news.css'];

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="container">
        <h1 class="fs-4"><?= h($article['title']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/news.php">News</a></li>
                <li class="breadcrumb-item active"><?= h(truncate($article['title'], 40)) ?></li>
            </ol>
        </nav>
    </div>
</section>


<!-- ARTICLE CONTENT -->
<section class="section-pad">
    <div class="container">
        <div class="row g-5">

            <!-- Article -->
            <div class="col-lg-8">
                <article class="news-article">

                    <!-- Meta -->
                    <div class="article-meta">
                        <?php if ($article['category']): ?>
                        <a href="<?= BASE_URL ?>/news.php?category=<?= urlencode($article['category']) ?>"
                           class="article-category">
                            <?= h($article['category']) ?>
                        </a>
                        <?php endif; ?>
                        <span>
                            <i class="far fa-calendar me-1"></i>
                            <?= formatDate($article['published_at'] ?? $article['created_at']) ?>
                        </span>
                        <?php if ($article['author_name']): ?>
                        <span>
                            <i class="far fa-user me-1"></i><?= h($article['author_name']) ?>
                        </span>
                        <?php endif; ?>
                        <span>
                            <i class="far fa-eye me-1"></i>
                            <?= number_format((int)$article['views']) ?> views
                        </span>
                    </div>

                    <!-- Featured Image -->
                    <?php if ($article['featured_image']): ?>
                    <div class="article-featured-img">
                        <img src="<?= uploadUrl('news', $article['featured_image']) ?>"
                             alt="<?= h($article['title']) ?>">
                    </div>
                    <?php endif; ?>

                    <!-- Body -->
                    <div class="article-body">
                        <?= nl2br(h($article['body'])) ?>
                    </div>

                    <!-- Tags -->
                    <?php if ($article['tags']): ?>
                    <div class="article-tags">
                        <i class="fas fa-tags text-gold me-2"></i>
                        <?php foreach (explode(',', $article['tags']) as $tag): ?>
                        <span class="article-tag"><?= h(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Share -->
                    <div class="article-share">
                        <span class="fw-600 small">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/news-detail.php?slug=' . $article['slug']) ?>"
                           target="_blank" rel="noopener" class="share-btn share-fb">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' ' . BASE_URL . '/news-detail.php?slug=' . $article['slug']) ?>"
                           target="_blank" rel="noopener" class="share-btn share-wa">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode(BASE_URL . '/news-detail.php?slug=' . $article['slug']) ?>"
                           target="_blank" rel="noopener" class="share-btn share-tw">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                    </div>

                </article>

                <!-- Back link -->
                <a href="<?= BASE_URL ?>/news.php" class="btn btn-fvc-outline btn-sm mt-2">
                    <i class="fas fa-arrow-left me-2"></i>All News
                </a>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="news-sidebar">

                    <!-- Related -->
                    <?php if (!empty($related)): ?>
                    <div class="sidebar-widget">
                        <h6 class="sidebar-widget-title">Related Articles</h6>
                        <?php foreach ($related as $rel): ?>
                        <div class="related-item">
                            <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($rel['slug']) ?>"
                               class="related-thumb">
                                <img src="<?= uploadUrl('news', $rel['featured_image']) ?>"
                                     alt="<?= h($rel['title']) ?>"
                                     onerror="this.closest('.related-thumb').classList.add('no-img')">
                            </a>
                            <div>
                                <a href="<?= BASE_URL ?>/news-detail.php?slug=<?= h($rel['slug']) ?>"
                                   class="related-title">
                                    <?= h(truncate($rel['title'], 55)) ?>
                                </a>
                                <span class="text-muted small">
                                    <?= formatDate($rel['published_at'] ?? '', 'd M Y') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Categories widget -->
                    <?php
                    $allCats = $db->fetchAll(
                        "SELECT category, COUNT(*) AS total
                         FROM news WHERE status='published' AND category IS NOT NULL
                         GROUP BY category ORDER BY total DESC"
                    );
                    if (!empty($allCats)):
                    ?>
                    <div class="sidebar-widget">
                        <h6 class="sidebar-widget-title">Categories</h6>
                        <ul class="sidebar-cat-list">
                            <?php foreach ($allCats as $cat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/news.php?category=<?= urlencode($cat['category']) ?>">
                                    <i class="fas fa-angle-right text-gold me-2"></i>
                                    <?= h($cat['category']) ?>
                                </a>
                                <span><?= (int)$cat['total'] ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- CTA widget -->
                    <div class="sidebar-widget sidebar-cta">
                        <i class="fas fa-graduation-cap fa-2x text-gold mb-3"></i>
                        <h6>Admissions Open <?= date('Y') ?></h6>
                        <p class="small text-secondary mb-3">
                            Apply now and secure your spot for the upcoming session.
                        </p>
                        <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-fvc-primary btn-sm w-100">
                            Apply Now
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
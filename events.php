<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$pageTitle       = 'Events';
$pageDescription = 'Upcoming and past events at Global Degree College — sports, cultural programs and exams.';
$extraCss        = ['news.css'];

$db = Database::getInstance();

$filterType   = get('type');
// If `status` is provided in the query we use it; otherwise default to 'upcoming'
$filterStatus = array_key_exists('status', $_GET) ? get('status') : 'upcoming';

$where  = ['1=1'];
$params = [];

if ($filterType) {
    $where[]  = 'type = ?';
    $params[] = $filterType;
}

if ($filterStatus === 'past') {
    $where[] = "event_date < CURDATE()";
} elseif ($filterStatus === 'upcoming') {
    $where[] = "event_date >= CURDATE()";
}

$whereStr = implode(' AND ', $where);
$order    = $filterStatus === 'past' ? 'DESC' : 'ASC';

$total  = (int)$db->fetchColumn("SELECT COUNT(*) FROM events WHERE $whereStr", $params);
$pager  = paginate($total, 9);

$events = $db->fetchAll(
    "SELECT * FROM events WHERE $whereStr
     ORDER BY event_date $order
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}",
    $params
);

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="container">
        <h1>Events</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active">Events</li>
            </ol>
        </nav>
    </div>
</section>


<!-- FILTER BAR -->
<section class="section-pad-sm bg-off-white">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="news-category-filters">
                    <a href="?status=upcoming"
                       class="news-cat-btn <?= $filterStatus === 'upcoming' ? 'active' : '' ?>">
                        <i class="fas fa-clock me-1"></i>Upcoming
                    </a>
                    <a href="?status=past"
                       class="news-cat-btn <?= $filterStatus === 'past' ? 'active' : '' ?>">
                        <i class="fas fa-history me-1"></i>Past Events
                    </a>
                    <a href="?status=all"
                       class="news-cat-btn <?= $filterStatus === 'all' ? 'active' : '' ?>">
                        All
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="news-category-filters justify-content-md-end">
                    <?php foreach (['academic','sports','cultural','exam'] as $t): ?>
                    <a href="?type=<?= $t ?>&status=<?= urlencode($filterStatus) ?>"
                       class="news-cat-btn <?= $filterType === $t ? 'active' : '' ?>">
                        <?= ucfirst($t) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- EVENTS GRID -->
<section class="section-pad">
    <div class="container">

        <?php if (empty($events)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
            <p>No <?= $filterStatus === 'upcoming' ? 'upcoming ' : '' ?>events found.</p>
        </div>

        <?php else: ?>

        <div class="row g-4">
            <?php foreach ($events as $i => $ev): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                <div class="event-card h-100">

                    <!-- Date block -->
                    <div class="event-card-header">
                        <div class="event-date-block">
                            <span class="event-date-day">
                                <?= date('d', strtotime($ev['event_date'])) ?>
                            </span>
                            <span class="event-date-month">
                                <?= date('M Y', strtotime($ev['event_date'])) ?>
                            </span>
                        </div>
                        <div>
                            <?php
                            $typeCls = match($ev['type']) {
                                'sports'   => 'badge-success',
                                'cultural' => 'badge-info',
                                'exam'     => 'badge-danger',
                                default    => 'badge-secondary'
                            };
                            ?>
                            <span class="status-badge <?= $typeCls ?>">
                                <?= ucfirst($ev['type']) ?>
                            </span>
                            <?php if ($ev['is_featured']): ?>
                            <span class="status-badge badge-warning ms-1">
                                <i class="fas fa-star"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Image -->
                    <?php if ($ev['featured_image']): ?>
                    <div class="event-card-img">
                        <img src="<?= uploadUrl('events', $ev['featured_image']) ?>"
                             alt="<?= h($ev['title']) ?>" loading="lazy">
                    </div>
                    <?php endif; ?>

                    <!-- Body -->
                    <div class="event-card-body">
                        <h5 class="event-title">
                            <a href="<?= BASE_URL ?>/events-detail.php?slug=<?= h($ev['slug']) ?>">
                                <?= h($ev['title']) ?>
                            </a>
                        </h5>
                        <ul class="event-info-list">
                            <?php if ($ev['event_time']): ?>
                            <li>
                                <i class="fas fa-clock"></i>
                                <?= date('h:i A', strtotime($ev['event_time'])) ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($ev['venue']): ?>
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <?= h($ev['venue']) ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($ev['end_date'] && $ev['end_date'] !== $ev['event_date']): ?>
                            <li>
                                <i class="fas fa-calendar-check"></i>
                                Ends: <?= formatDate($ev['end_date'], 'd M Y') ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($ev['description']): ?>
                        <p class="text-secondary small mb-3">
                            <?= h(truncate($ev['description'], 90)) ?>
                        </p>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/events-detail.php?slug=<?= h($ev['slug']) ?>"
                           class="btn btn-fvc-outline btn-sm">
                            View Details <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pager['pages'] > 1): ?>
        <div class="d-flex justify-content-center gap-2 mt-5">
            <?php for ($p = 1; $p <= $pager['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&type=<?= urlencode($filterType) ?>&status=<?= urlencode($filterStatus) ?>"
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
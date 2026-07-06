<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config/app.php';

$db   = Database::getInstance();
$slug = get('slug');

if (empty($slug) || !validateSlug($slug)) {
    header('Location: ' . BASE_URL . '/events.php');
    exit;
}

$event = $db->fetch(
    "SELECT * FROM events WHERE slug = ?",
    [$slug]
);

if (!$event) {
    http_response_code(404);
    header('Location: ' . BASE_URL . '/events.php');
    exit;
}

// Other upcoming events (sidebar)
$otherEvents = $db->fetchAll(
    "SELECT id, title, slug, event_date, venue, type
     FROM events
     WHERE event_date >= CURDATE() AND id != ?
     ORDER BY event_date ASC LIMIT 4",
    [$event['id']]
);

$pageTitle       = $event['title'];
$pageDescription = truncate($event['description'] ?? '', 160);
$extraCss        = ['news.css'];

require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="container">
        <h1 class="fs-4"><?= h($event['title']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/events.php">Events</a></li>
                <li class="breadcrumb-item active"><?= h(truncate($event['title'], 40)) ?></li>
            </ol>
        </nav>
    </div>
</section>


<!-- EVENT DETAIL -->
<section class="section-pad">
    <div class="container">
        <div class="row g-5">

            <!-- Main -->
            <div class="col-lg-8">

                <!-- Banner image -->
                <?php if ($event['featured_image']): ?>
                <div class="article-featured-img mb-4">
                    <img src="<?= uploadUrl('events', $event['featured_image']) ?>"
                         alt="<?= h($event['title']) ?>">
                </div>
                <?php endif; ?>

                <!-- Event info strip -->
                <div class="event-detail-strip">
                    <div class="event-detail-strip-item">
                        <i class="fas fa-calendar-alt text-gold"></i>
                        <div>
                            <span>Date</span>
                            <strong><?= formatDate($event['event_date']) ?></strong>
                        </div>
                    </div>
                    <?php if ($event['event_time']): ?>
                    <div class="event-detail-strip-item">
                        <i class="fas fa-clock text-gold"></i>
                        <div>
                            <span>Time</span>
                            <strong><?= date('h:i A', strtotime($event['event_time'])) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($event['venue']): ?>
                    <div class="event-detail-strip-item">
                        <i class="fas fa-map-marker-alt text-gold"></i>
                        <div>
                            <span>Venue</span>
                            <strong><?= h($event['venue']) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="event-detail-strip-item">
                        <i class="fas fa-tag text-gold"></i>
                        <div>
                            <span>Type</span>
                            <strong><?= ucfirst($event['type']) ?></strong>
                        </div>
                    </div>
                    <div class="event-detail-strip-item">
                        <i class="fas fa-info-circle text-gold"></i>
                        <div>
                            <span>Status</span>
                            <strong><?= ucfirst($event['status']) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($event['description']): ?>
                <div class="article-body mt-4">
                    <?= nl2br(h($event['description'])) ?>
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="article-share mt-4">
                    <span class="fw-600 small">Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/events-detail.php?slug=' . $event['slug']) ?>"
                       target="_blank" rel="noopener" class="share-btn share-fb">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($event['title'] . ' ' . BASE_URL . '/events-detail.php?slug=' . $event['slug']) ?>"
                       target="_blank" rel="noopener" class="share-btn share-wa">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>

                <a href="<?= BASE_URL ?>/events.php" class="btn btn-fvc-outline btn-sm mt-4">
                    <i class="fas fa-arrow-left me-2"></i>All Events
                </a>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="news-sidebar">

                    <!-- Countdown -->
                    <?php
                    $eventTimestamp = strtotime($event['event_date']);
                    $now            = time();
                    $diff           = $eventTimestamp - $now;
                    $isPast         = $diff < 0;
                    ?>
                    <div class="sidebar-widget text-center">
                        <?php if ($isPast): ?>
                        <div class="event-countdown-label">This event has concluded.</div>
                        <?php else: ?>
                        <div class="event-countdown-label">Event starts in</div>
                        <div class="event-countdown" id="eventCountdown"
                             data-date="<?= h($event['event_date']) ?>">
                            <div class="countdown-block">
                                <span class="countdown-num" id="cd-days">--</span>
                                <span class="countdown-unit">Days</span>
                            </div>
                            <div class="countdown-block">
                                <span class="countdown-num" id="cd-hours">--</span>
                                <span class="countdown-unit">Hours</span>
                            </div>
                            <div class="countdown-block">
                                <span class="countdown-num" id="cd-mins">--</span>
                                <span class="countdown-unit">Mins</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Other events -->
                    <?php if (!empty($otherEvents)): ?>
                    <div class="sidebar-widget">
                        <h6 class="sidebar-widget-title">Other Upcoming Events</h6>
                        <?php foreach ($otherEvents as $oe): ?>
                        <div class="related-item">
                            <div class="event-mini-date-box">
                                <span><?= date('d', strtotime($oe['event_date'])) ?></span>
                                <span><?= date('M', strtotime($oe['event_date'])) ?></span>
                            </div>
                            <div>
                                <a href="<?= BASE_URL ?>/events-detail.php?slug=<?= h($oe['slug']) ?>"
                                   class="related-title">
                                    <?= h(truncate($oe['title'], 50)) ?>
                                </a>
                                <?php if ($oe['venue']): ?>
                                <span class="text-muted small d-block">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= h($oe['venue']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- CTA widget -->
                    <div class="sidebar-widget sidebar-cta">
                        <i class="fas fa-graduation-cap fa-2x text-gold mb-3"></i>
                        <h6>Admissions Open</h6>
                        <p class="small text-secondary mb-3">
                            Join our college community. Apply before seats fill up.
                        </p>
                        <a href="<?= BASE_URL ?>/admissions.php"
                           class="btn btn-fvc-primary btn-sm w-100">
                            Apply Now
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Countdown JS -->
<script>
(function() {
    const el = document.getElementById('eventCountdown');
    if (!el) return;

    const eventDate = new Date(el.dataset.date + 'T00:00:00');

    function update() {
        const diff = eventDate - new Date();
        if (diff <= 0) {
            el.innerHTML = '<p class="text-muted small">Event is today!</p>';
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);

        document.getElementById('cd-days').textContent  = String(d).padStart(2,'0');
        document.getElementById('cd-hours').textContent = String(h).padStart(2,'0');
        document.getElementById('cd-mins').textContent  = String(m).padStart(2,'0');
    }

    update();
    setInterval(update, 60000);
})();
</script>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
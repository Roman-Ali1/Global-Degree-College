<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config/app.php';

$adminPageTitle = 'Dashboard';
$db = Database::getInstance();

// ── Stats cards ───────────────────────────────────────────────
$stats = [
    'total_applications' => (int)$db->fetchColumn("SELECT COUNT(*) FROM admission_applications"),
    'pending'            => (int)$db->fetchColumn("SELECT COUNT(*) FROM admission_applications WHERE status='pending'"),
    'accepted'           => (int)$db->fetchColumn("SELECT COUNT(*) FROM admission_applications WHERE status='accepted'"),
    'total_students'     => (int)$db->fetchColumn("SELECT COUNT(*) FROM students"),
    'total_faculty'      => (int)$db->fetchColumn("SELECT COUNT(*) FROM faculty WHERE is_active=1"),
    'unread_messages'    => (int)$db->fetchColumn("SELECT COUNT(*) FROM contact_messages WHERE is_read=0"),
    'published_news'     => (int)$db->fetchColumn("SELECT COUNT(*) FROM news WHERE status='published'"),
    'upcoming_events'    => (int)$db->fetchColumn("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()"),
];

// ── Recent applications ───────────────────────────────────────
$recentApps = $db->fetchAll(
    "SELECT a.*, c.title AS course_title
     FROM admission_applications a
     LEFT JOIN courses c ON a.course_id = c.id
     ORDER BY a.created_at DESC LIMIT 8"
);

// ── Applications by course (for mini chart) ───────────────────
$appsByCourse = $db->fetchAll(
    "SELECT c.title, COUNT(a.id) AS total
     FROM courses c
     LEFT JOIN admission_applications a ON a.course_id = c.id
     GROUP BY c.id ORDER BY total DESC"
);

// ── Recent messages ───────────────────────────────────────────
$recentMessages = $db->fetchAll(
    "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5"
);

require_once __DIR__ . '/templates/admin-header.php';
?>

<div class="row g-4 mb-4">

    <!-- Stat cards -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-blue">
            <div class="stat-card-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-number"><?= $stats['total_applications'] ?></div>
                <div class="stat-card-label">Total Applications</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-yellow">
            <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-number"><?= $stats['pending'] ?></div>
                <div class="stat-card-label">Pending Review</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green">
            <div class="stat-card-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-number"><?= $stats['accepted'] ?></div>
                <div class="stat-card-label">Accepted</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-red">
            <div class="stat-card-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-number"><?= $stats['unread_messages'] ?></div>
                <div class="stat-card-label">Unread Messages</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Recent Applications Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-file-alt me-2"></i>Recent Applications</h6>
                <a href="<?= ADMIN_URL ?>/modules/admissions.php" class="btn-card-link">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>App No.</th>
                                <th>Student</th>
                                <th>Program</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentApps)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No applications yet.</td></tr>
                        <?php else: foreach ($recentApps as $app): ?>
                            <tr>
                                <td><code><?= h($app['app_number']) ?></code></td>
                                <td>
                                    <div class="fw-600"><?= h($app['student_name']) ?></div>
                                    <div class="text-muted small"><?= h($app['mobile']) ?></div>
                                </td>
                                <td><?= h($app['course_title'] ?? '—') ?></td>
                                <td class="text-muted small"><?= formatDate($app['created_at'], 'd M Y') ?></td>
                                <td><?= statusBadge($app['status']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="col-lg-4">

        <!-- Applications by Program -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6><i class="fas fa-chart-bar me-2"></i>Applications by Program</h6>
            </div>
            <div class="admin-card-body">
                <?php foreach ($appsByCourse as $row): ?>
                <div class="mini-bar-item">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small"><?= h($row['title']) ?></span>
                        <span class="small fw-600"><?= (int)$row['total'] ?></span>
                    </div>
                    <?php
                    $maxApps = max(1, (int)($appsByCourse[0]['total'] ?? 1));
                    $pct     = $maxApps > 0 ? round(((int)$row['total'] / $maxApps) * 100) : 0;
                    ?>
                    <div class="mini-bar-track">
                        <div class="mini-bar-fill" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Quick Info</h6>
            </div>
            <div class="admin-card-body p-0">
                <ul class="quick-info-list">
                    <li>
                        <span><i class="fas fa-users text-gold"></i> Students</span>
                        <strong><?= $stats['total_students'] ?></strong>
                    </li>
                    <li>
                        <span><i class="fas fa-chalkboard-teacher text-gold"></i> Faculty</span>
                        <strong><?= $stats['total_faculty'] ?></strong>
                    </li>
                    <li>
                        <span><i class="fas fa-newspaper text-gold"></i> Published News</span>
                        <strong><?= $stats['published_news'] ?></strong>
                    </li>
                    <li>
                        <span><i class="fas fa-calendar text-gold"></i> Upcoming Events</span>
                        <strong><?= $stats['upcoming_events'] ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

<?php
// Shared helper used across all admin modules
function statusBadge(string $status): string
{
    $map = [
        'pending'      => ['warning',  'clock',        'Pending'],
        'under_review' => ['info',     'search',       'Reviewing'],
        'accepted'     => ['success',  'check-circle', 'Accepted'],
        'rejected'     => ['danger',   'times-circle', 'Rejected'],
        'waitlisted'   => ['secondary','hourglass-half','Waitlisted'],
        'published'    => ['success',  'check-circle', 'Published'],
        'draft'        => ['secondary','edit',         'Draft'],
        'archived'     => ['dark',     'archive',      'Archived'],
        'approved'     => ['success',  'check',        'Approved'],
        'active'       => ['success',  'check',        'Active'],
        'inactive'     => ['secondary','times',        'Inactive'],
    ];
    $s = $map[$status] ?? ['secondary', 'question', ucfirst($status)];
    return "<span class='status-badge badge-{$s[0]}'><i class='fas fa-{$s[1]}'></i> {$s[2]}</span>";
}
?>

<?php require_once __DIR__ . '/templates/admin-footer.php'; ?>
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

// Load helper inline
function statusBadge(string $status): string {
    $map = [
        'pending'=>['warning','clock','Pending'],
        'under_review'=>['info','search','Reviewing'],
        'accepted'=>['success','check-circle','Accepted'],
        'rejected'=>['danger','times-circle','Rejected'],
        'waitlisted'=>['secondary','hourglass-half','Waitlisted'],
    ];
    $s = $map[$status] ?? ['secondary','question',ucfirst($status)];
    return "<span class='status-badge badge-{$s[0]}'><i class='fas fa-{$s[1]}'></i> {$s[2]}</span>";
}

$adminPageTitle = 'Admission Applications';
$db = Database::getInstance();

// ── Handle status update ──────────────────────────────────────
if (isPost() && isset($_POST['update_status'])) {
    CSRF::requireValid();
    $appId  = postInt('app_id');
    $status = cleanString($_POST['status'] ?? '');
    $note   = post('review_notes');
    $allowed = ['pending','under_review','accepted','rejected','waitlisted'];
    if ($appId > 0 && in_array($status, $allowed, true)) {
        $db->execute(
            "UPDATE admission_applications
             SET status=?, review_notes=?, reviewed_by=?, reviewed_at=NOW()
             WHERE id=?",
            [$status, $note, Auth::user()['id'], $appId]
        );
        setFlash('success', 'Application status updated successfully.');
    }
    redirect(ADMIN_URL . '/modules/admissions.php');
}

// ── Filters ───────────────────────────────────────────────────
$filterStatus = get('status');
$filterCourse = getInt('course');
$search       = get('q');

$where  = ['1=1'];
$params = [];

if ($filterStatus) { $where[] = 'a.status = ?'; $params[] = $filterStatus; }
if ($filterCourse) { $where[] = 'a.course_id = ?'; $params[] = $filterCourse; }
if ($search)       {
    $where[]  = '(a.student_name LIKE ? OR a.app_number LIKE ? OR a.mobile LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereStr = implode(' AND ', $where);

// Pagination
$total  = (int)$db->fetchColumn("SELECT COUNT(*) FROM admission_applications a WHERE $whereStr", $params);
$pager  = paginate($total, ADMIN_PER_PAGE);

$applications = $db->fetchAll(
    "SELECT a.*, c.title AS course_title
     FROM admission_applications a
     LEFT JOIN courses c ON a.course_id = c.id
     WHERE $whereStr
     ORDER BY a.created_at DESC
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}",
    $params
);

$courses = $db->fetchAll("SELECT id, title FROM courses WHERE is_active=1 ORDER BY sort_order");

// Single application view
$viewApp = null;
if (getInt('view') > 0) {
    $viewApp = $db->fetch(
        "SELECT a.*, c.title AS course_title
         FROM admission_applications a
         LEFT JOIN courses c ON a.course_id = c.id
         WHERE a.id = ?",
        [getInt('view')]
    );
}

require_once __DIR__ . '/../templates/admin-header.php';
?>

<?php if ($viewApp): ?>
<!-- ── Single Application View ─────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= ADMIN_URL ?>/modules/admissions.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
    <h5 class="mb-0">Application: <?= h($viewApp['app_number']) ?></h5>
    <?= statusBadge($viewApp['status']) ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h6>Personal Information</h6></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <?php
                    $fields = [
                        'Student Name'    => $viewApp['student_name'],
                        'Father Name'     => $viewApp['father_name'],
                        'CNIC / B-Form'   => $viewApp['cnic_bform'],
                        'Date of Birth'   => formatDate($viewApp['date_of_birth']),
                        'Gender'          => ucfirst($viewApp['gender']),
                        'Mobile'          => $viewApp['mobile'],
                        'Email'           => $viewApp['email'] ?: '—',
                        'City'            => $viewApp['city'] ?: '—',
                        'Address'         => $viewApp['address'],
                        'Previous School' => $viewApp['previous_school'],
                        'Last Class'      => $viewApp['previous_class'],
                        'Marks'           => $viewApp['obtained_marks'].'/'.$viewApp['total_marks'].' ('.$viewApp['percentage'].'%)',
                        'Program'         => $viewApp['course_title'],
                        'Applied On'      => formatDate($viewApp['created_at'], 'd M Y, h:i A'),
                        'IP Address'      => $viewApp['ip_address'] ?? '—',
                    ];
                    foreach ($fields as $label => $value):
                    ?>
                    <div class="col-sm-6">
                        <div class="view-field">
                            <span class="view-field-label"><?= h($label) ?></span>
                            <span class="view-field-value"><?= h((string)$value) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Photo -->
        <?php if ($viewApp['photo']): ?>
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h6>Student Photo</h6></div>
            <div class="admin-card-body text-center">
                <img src="<?= uploadUrl('admissions', $viewApp['photo']) ?>"
                     alt="Student Photo" class="img-fluid rounded"
                     style="max-height:200px">
            </div>
        </div>
        <?php endif; ?>

        <!-- Update Status -->
        <?php if (Auth::hasRole('super_admin') || Auth::hasRole('admission_officer')): ?>
        <div class="admin-card">
            <div class="admin-card-header"><h6>Update Status</h6></div>
            <div class="admin-card-body">
                <form method="POST">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="app_id" value="<?= (int)$viewApp['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach (['pending','under_review','accepted','rejected','waitlisted'] as $s): ?>
                            <option value="<?= $s ?>" <?= $viewApp['status'] === $s ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_',' ',$s)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="review_notes" rows="3" class="form-control form-control-sm"
                                  placeholder="Internal notes..."><?= h($viewApp['review_notes'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">Update Status</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- ── Applications List ───────────────────────────────────── -->

<!-- Filters -->
<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Search name, app no, mobile..."
                       value="<?= h($search) ?>">
            </div>
            <div class="col-sm-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','under_review','accepted','rejected','waitlisted'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                        <?= ucwords(str_replace('_',' ',$s)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <select name="course" class="form-select form-select-sm">
                    <option value="">All Programs</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filterCourse === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= h($c['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-2">
                <button class="btn btn-admin-primary btn-sm">Filter</button>
                <a href="?" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-file-alt me-2"></i>Applications
            <span class="badge bg-secondary ms-2"><?= $total ?></span>
        </h6>
    </div>
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>App No.</th>
                        <th>Student</th>
                        <th>Program</th>
                        <th>Marks</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
                <?php else: foreach ($applications as $app): ?>
                    <tr>
                        <td><code class="small"><?= h($app['app_number']) ?></code></td>
                        <td>
                            <div class="fw-600"><?= h($app['student_name']) ?></div>
                            <div class="text-muted small"><?= h($app['mobile']) ?></div>
                        </td>
                        <td class="small"><?= h($app['course_title'] ?? '—') ?></td>
                        <td class="small"><?= $app['obtained_marks'] ?>/<?= $app['total_marks'] ?>
                            <span class="text-muted">(<?= $app['percentage'] ?>%)</span>
                        </td>
                        <td class="text-muted small"><?= formatDate($app['created_at'], 'd M Y') ?></td>
                        <td><?= statusBadge($app['status']) ?></td>
                        <td>
                            <a href="?view=<?= (int)$app['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pager['pages'] > 1): ?>
        <div class="admin-pagination">
            <?php for ($p = 1; $p <= $pager['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>&course=<?= $filterCourse ?>"
               class="page-btn <?= $p === $pager['current'] ? 'active' : '' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
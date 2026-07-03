<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Courses';
$db = Database::getInstance();

// ── DELETE ────────────────────────────────────────────────────
if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $db->execute("UPDATE courses SET is_active = 0 WHERE id = ?", [postInt('delete_id')]);
    setFlash('success', 'Course deactivated successfully.');
    redirect(ADMIN_URL . '/modules/courses.php');
}

// ── SAVE (add / edit) ─────────────────────────────────────────
if (isPost() && isset($_POST['save_course'])) {
    CSRF::requireValid();

    $id          = postInt('id');
    $title       = post('title');
    $description = post('description');
    $duration    = post('duration');
    $eligibility = post('eligibility');
    $seats       = postInt('total_seats');
    $fee_month   = cleanFloat($_POST['fee_per_month'] ?? '0');
    $fee_admit   = cleanFloat($_POST['admission_fee'] ?? '0');
    $icon        = post('icon');
    $sort        = postInt('sort_order');
    $active      = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if (empty($title))    $errors[] = 'Title is required.';
    if (empty($duration)) $errors[] = 'Duration is required.';

    if (empty($errors)) {
        if ($id > 0) {
            // Edit
            $db->execute(
                "UPDATE courses SET title=?, description=?, duration=?, eligibility=?,
                 total_seats=?, fee_per_month=?, admission_fee=?, icon=?,
                 sort_order=?, is_active=? WHERE id=?",
                [$title, $description, $duration, $eligibility,
                 $seats, $fee_month, $fee_admit, $icon, $sort, $active, $id]
            );
            setFlash('success', 'Course updated successfully.');
        } else {
            // Add
            $slug = slugify($title);
            // Ensure unique slug
            $exists = $db->fetchColumn("SELECT id FROM courses WHERE slug=?", [$slug]);
            if ($exists) $slug .= '-' . time();

            $db->insert(
                "INSERT INTO courses
                 (title, slug, short_code, description, duration, eligibility,
                  total_seats, fee_per_month, admission_fee, icon, sort_order, is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [$title, $slug, strtoupper(post('short_code')),
                 $description, $duration, $eligibility,
                 $seats, $fee_month, $fee_admit, $icon, $sort, $active]
            );
            setFlash('success', 'Course added successfully.');
        }
        redirect(ADMIN_URL . '/modules/courses.php');
    } else {
        setFlash('error', implode(' ', $errors));
    }
}

// ── Edit mode ─────────────────────────────────────────────────
$editCourse = null;
if (getInt('edit') > 0) {
    $editCourse = $db->fetch("SELECT * FROM courses WHERE id=?", [getInt('edit')]);
}

$courses = $db->fetchAll("SELECT * FROM courses ORDER BY sort_order, id");

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">

    <!-- Form -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editCourse ? 'Edit Course' : 'Add New Course' ?></h6>
                <?php if ($editCourse): ?>
                <a href="?" class="btn-card-link">+ Add New</a>
                <?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_course" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editCourse['id'] ?? 0) ?>">

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               value="<?= h($editCourse['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Code</label>
                        <input type="text" name="short_code" class="form-control form-control-sm"
                               value="<?= h($editCourse['short_code'] ?? '') ?>"
                               placeholder="e.g. FSC-MED" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control form-control-sm"><?= h($editCourse['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                        <input type="text" name="duration" class="form-control form-control-sm"
                               value="<?= h($editCourse['duration'] ?? '2 Years') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eligibility</label>
                        <textarea name="eligibility" rows="2"
                                  class="form-control form-control-sm"><?= h($editCourse['eligibility'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Total Seats</label>
                            <input type="number" name="total_seats" class="form-control form-control-sm"
                                   value="<?= (int)($editCourse['total_seats'] ?? 80) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm"
                                   value="<?= (int)($editCourse['sort_order'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Monthly Fee (PKR)</label>
                            <input type="number" name="fee_per_month" class="form-control form-control-sm"
                                   step="0.01" value="<?= (float)($editCourse['fee_per_month'] ?? 0) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Admission Fee (PKR)</label>
                            <input type="number" name="admission_fee" class="form-control form-control-sm"
                                   step="0.01" value="<?= (float)($editCourse['admission_fee'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Font Awesome Icon</label>
                        <input type="text" name="icon" class="form-control form-control-sm"
                               value="<?= h($editCourse['icon'] ?? '') ?>"
                               placeholder="e.g. fa-flask">
                        <div class="form-text">Without "fas " prefix</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input"
                               id="isActive" <?= ($editCourse['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i><?= $editCourse ? 'Update Course' : 'Add Course' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-graduation-cap me-2"></i>All Courses
                    <span class="badge bg-secondary ms-2"><?= count($courses) ?></span>
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Duration</th>
                            <th>Seats</th>
                            <th>Fee/mo</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td>
                                <i class="fas <?= h($c['icon'] ?: 'fa-book') ?> text-gold me-2"></i>
                                <strong><?= h($c['title']) ?></strong>
                            </td>
                            <td><code><?= h($c['short_code']) ?></code></td>
                            <td><?= h($c['duration']) ?></td>
                            <td><?= (int)$c['total_seats'] ?></td>
                            <td><?= formatCurrency((float)$c['fee_per_month']) ?></td>
                            <td>
                                <span class="status-badge <?= $c['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?= (int)$c['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Deactivate this course?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
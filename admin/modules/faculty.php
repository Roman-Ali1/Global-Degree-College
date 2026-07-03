<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Faculty';
$db = Database::getInstance();

// ── DELETE ────────────────────────────────────────────────────
if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $member = $db->fetch("SELECT photo FROM faculty WHERE id=?", [postInt('delete_id')]);
    if ($member && $member['photo']) {
        @unlink(UPLOAD_FACULTY . $member['photo']);
    }
    $db->execute("UPDATE faculty SET is_active=0 WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Faculty member deactivated.');
    redirect(ADMIN_URL . '/modules/faculty.php');
}

// ── SAVE ──────────────────────────────────────────────────────
if (isPost() && isset($_POST['save_faculty'])) {
    CSRF::requireValid();

    $id          = postInt('id');
    $full_name   = post('full_name');
    $designation = post('designation');
    $department  = post('department');
    $qual        = post('qualification');
    $exp         = postInt('experience_years');
    $email       = cleanEmail($_POST['email'] ?? '');
    $phone       = cleanPhone($_POST['phone'] ?? '');
    $bio         = post('bio');
    $course_id   = postInt('course_id') ?: null;
    $sort        = postInt('sort_order');
    $show_home   = isset($_POST['show_on_home']) ? 1 : 0;
    $active      = isset($_POST['is_active'])    ? 1 : 0;

    // Photo upload
    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploader = new Uploader(UPLOAD_FACULTY);
        $uploaded = $uploader->upload($_FILES['photo'], 'faculty_');
        if ($uploaded) {
            $photoFilename = $uploaded;
            // Delete old photo on edit
            if ($id > 0) {
                $old = $db->fetchColumn("SELECT photo FROM faculty WHERE id=?", [$id]);
                if ($old) @unlink(UPLOAD_FACULTY . $old);
            }
        } else {
            setFlash('error', $uploader->getError());
            redirect(ADMIN_URL . '/modules/faculty.php' . ($id ? "?edit=$id" : ''));
        }
    }

    if ($id > 0) {
        $sql = "UPDATE faculty SET full_name=?, designation=?, department=?,
                qualification=?, experience_years=?, email=?, phone=?, bio=?,
                course_id=?, sort_order=?, show_on_home=?, is_active=?"
             . ($photoFilename ? ", photo=?" : "")
             . " WHERE id=?";
        $params = [$full_name, $designation, $department, $qual, $exp,
                   $email, $phone, $bio, $course_id, $sort, $show_home, $active];
        if ($photoFilename) $params[] = $photoFilename;
        $params[] = $id;
        $db->execute($sql, $params);
        setFlash('success', 'Faculty member updated.');
    } else {
        $db->insert(
            "INSERT INTO faculty
             (full_name, designation, department, qualification, experience_years,
              email, phone, bio, photo, course_id, sort_order, show_on_home, is_active)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$full_name, $designation, $department, $qual, $exp,
             $email, $phone, $bio, $photoFilename, $course_id, $sort, $show_home, $active]
        );
        setFlash('success', 'Faculty member added.');
    }
    redirect(ADMIN_URL . '/modules/faculty.php');
}

$editMember = getInt('edit') > 0
    ? $db->fetch("SELECT * FROM faculty WHERE id=?", [getInt('edit')])
    : null;

$faculty = $db->fetchAll(
    "SELECT f.*, c.title AS course_title FROM faculty f
     LEFT JOIN courses c ON f.course_id = c.id
     ORDER BY f.sort_order, f.full_name"
);
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE is_active=1 ORDER BY sort_order");

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editMember ? 'Edit Member' : 'Add Faculty Member' ?></h6>
                <?php if ($editMember): ?>
                <a href="?" class="btn-card-link">+ Add New</a>
                <?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_faculty" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editMember['id'] ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control form-control-sm"
                               value="<?= h($editMember['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control form-control-sm"
                               value="<?= h($editMember['designation'] ?? '') ?>"
                               placeholder="e.g. Senior Lecturer">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control form-control-sm"
                               value="<?= h($editMember['department'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Qualification</label>
                        <input type="text" name="qualification" class="form-control form-control-sm"
                               value="<?= h($editMember['qualification'] ?? '') ?>"
                               placeholder="e.g. M.Phil Chemistry">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Experience (yrs)</label>
                            <input type="number" name="experience_years"
                                   class="form-control form-control-sm"
                                   value="<?= (int)($editMember['experience_years'] ?? 0) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order"
                                   class="form-control form-control-sm"
                                   value="<?= (int)($editMember['sort_order'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                               value="<?= h($editMember['email'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm"
                               value="<?= h($editMember['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Primary Course</label>
                        <select name="course_id" class="form-select form-select-sm">
                            <option value="">— None —</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= ($editMember['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= h($c['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" rows="2"
                                  class="form-control form-control-sm"><?= h($editMember['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Photo</label>
                        <?php if (!empty($editMember['photo'])): ?>
                        <div class="mb-1">
                            <img src="<?= uploadUrl('faculty', $editMember['photo']) ?>"
                                 height="50" class="rounded-circle border">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="photo" class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="mb-3 d-flex gap-3">
                        <div class="form-check">
                            <input type="checkbox" name="show_on_home" class="form-check-input"
                                   id="showHome"
                                   <?= ($editMember['show_on_home'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="showHome">Show on Home</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input"
                                   id="fActive"
                                   <?= ($editMember['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="fActive">Active</label>
                        </div>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>
                        <?= $editMember ? 'Update Member' : 'Add Member' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-chalkboard-teacher me-2"></i>Faculty Members
                    <span class="badge bg-secondary ms-2"><?= count($faculty) ?></span>
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Course</th>
                            <th>Home</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($faculty as $m): ?>
                        <tr>
                            <td>
                                <img src="<?= uploadUrl('faculty', $m['photo']) ?>"
                                     width="38" height="38"
                                     class="rounded-circle object-fit-cover border"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/defaults/avatar-placeholder.png'">
                            </td>
                            <td>
                                <div class="fw-600"><?= h($m['full_name']) ?></div>
                                <div class="text-muted small"><?= h($m['qualification'] ?? '') ?></div>
                            </td>
                            <td class="small"><?= h($m['designation']) ?></td>
                            <td class="small text-muted"><?= h($m['course_title'] ?? '—') ?></td>
                            <td>
                                <?php if ($m['show_on_home']): ?>
                                <span class="status-badge badge-success">Yes</span>
                                <?php else: ?>
                                <span class="status-badge badge-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?= (int)$m['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Deactivate this member?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$m['id'] ?>">
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
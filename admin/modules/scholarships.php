<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Scholarships';
$db = Database::getInstance();

if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $db->execute("DELETE FROM scholarships WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Scholarship deleted.');
    redirect(ADMIN_URL . '/modules/scholarships.php');
}

if (isPost() && isset($_POST['save_scholarship'])) {
    CSRF::requireValid();

    $id          = postInt('id');
    $title       = post('title');
    $description = post('description');
    $eligibility = post('eligibility');
    $amount      = cleanFloat($_POST['amount'] ?? '0') ?: null;
    $amount_type = cleanString($_POST['amount_type'] ?? 'monthly');
    $seats       = postInt('seats') ?: null;
    $deadline    = cleanDate($_POST['deadline'] ?? '') ?: null;
    $active      = isset($_POST['is_active']) ? 1 : 0;

    if ($id > 0) {
        $db->execute(
            "UPDATE scholarships SET title=?, description=?, eligibility=?,
             amount=?, amount_type=?, seats=?, deadline=?, is_active=? WHERE id=?",
            [$title, $description, $eligibility, $amount,
             $amount_type, $seats, $deadline, $active, $id]
        );
        setFlash('success', 'Scholarship updated.');
    } else {
        $slug = slugify($title);
        $ex = $db->fetchColumn("SELECT id FROM scholarships WHERE slug=?", [$slug]);
        if ($ex) $slug .= '-' . time();
        $db->insert(
            "INSERT INTO scholarships
             (title, slug, description, eligibility, amount, amount_type, seats, deadline, is_active)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$title, $slug, $description, $eligibility,
             $amount, $amount_type, $seats, $deadline, $active]
        );
        setFlash('success', 'Scholarship added.');
    }
    redirect(ADMIN_URL . '/modules/scholarships.php');
}

$editS = getInt('edit') > 0
    ? $db->fetch("SELECT * FROM scholarships WHERE id=?", [getInt('edit')])
    : null;

$scholarships = $db->fetchAll("SELECT * FROM scholarships ORDER BY id DESC");

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editS ? 'Edit Scholarship' : 'Add Scholarship' ?></h6>
                <?php if ($editS): ?><a href="?" class="btn-card-link">+ New</a><?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_scholarship" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editS['id'] ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               value="<?= h($editS['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control form-control-sm"><?= h($editS['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Eligibility</label>
                        <textarea name="eligibility" rows="2"
                                  class="form-control form-control-sm"><?= h($editS['eligibility'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" name="amount" step="0.01"
                                   class="form-control form-control-sm"
                                   value="<?= h((string)($editS['amount'] ?? '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="amount_type" class="form-select form-select-sm">
                                <?php foreach (['monthly','one_time','percentage','full_waiver'] as $t): ?>
                                <option value="<?= $t ?>"
                                    <?= ($editS['amount_type'] ?? 'monthly') === $t ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_',' ',$t)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Seats</label>
                            <input type="number" name="seats" class="form-control form-control-sm"
                                   value="<?= h((string)($editS['seats'] ?? '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control form-control-sm"
                                   value="<?= h($editS['deadline'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input"
                               id="sActive" <?= ($editS['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="sActive">Active</label>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>
                        <?= $editS ? 'Update' : 'Add Scholarship' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-award me-2"></i>All Scholarships</h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr><th>Title</th><th>Amount</th><th>Seats</th><th>Deadline</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($scholarships as $s): ?>
                        <tr>
                            <td><div class="fw-600"><?= h($s['title']) ?></div></td>
                            <td class="small">
                                <?= $s['amount_type'] === 'full_waiver'
                                    ? 'Full Waiver'
                                    : formatCurrency((float)($s['amount'] ?? 0)) . ' / ' . str_replace('_',' ',$s['amount_type'])
                                ?>
                            </td>
                            <td><?= $s['seats'] ?? '—' ?></td>
                            <td class="small text-muted">
                                <?= $s['deadline'] ? formatDate($s['deadline'], 'd M Y') : '—' ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $s['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?= (int)$s['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this scholarship?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$s['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
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
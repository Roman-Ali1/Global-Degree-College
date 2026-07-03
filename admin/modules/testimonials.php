<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Testimonials';
$db = Database::getInstance();

if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $db->execute("DELETE FROM testimonials WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Testimonial deleted.');
    redirect(ADMIN_URL . '/modules/testimonials.php');
}

if (isPost() && isset($_POST['update_status'])) {
    CSRF::requireValid();
    $db->execute(
        "UPDATE testimonials SET status=?, show_on_home=? WHERE id=?",
        [cleanString($_POST['status'] ?? 'pending'),
         isset($_POST['show_on_home']) ? 1 : 0,
         postInt('id')]
    );
    setFlash('success', 'Testimonial updated.');
    redirect(ADMIN_URL . '/modules/testimonials.php');
}

$testimonials = $db->fetchAll(
    "SELECT t.*, c.title AS course_title
     FROM testimonials t LEFT JOIN courses c ON t.course_id = c.id
     ORDER BY t.created_at DESC"
);

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-star me-2"></i>Testimonials
            <span class="badge bg-secondary ms-2"><?= count($testimonials) ?></span>
        </h6>
    </div>
    <div class="admin-card-body p-0">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Message</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Home</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($testimonials)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No testimonials yet.</td></tr>
            <?php else: foreach ($testimonials as $t): ?>
                <tr>
                    <td>
                        <div class="fw-600"><?= h($t['name']) ?></div>
                        <div class="text-muted small"><?= h($t['designation'] ?? '') ?></div>
                    </td>
                    <td class="small text-muted"><?= h(truncate($t['message'], 60)) ?></td>
                    <td>
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="fas fa-star small <?= $s <= $t['rating'] ? 'text-warning' : 'text-muted opacity-25' ?>"></i>
                        <?php endfor; ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $t['status'] === 'approved' ? 'badge-success' : ($t['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>">
                            <?= ucfirst($t['status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?= $t['show_on_home'] ? 'badge-success' : 'badge-secondary' ?>">
                            <?= $t['show_on_home'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1"
                                onclick="openTestimonialModal(<?= (int)$t['id'] ?>, '<?= h($t['status']) ?>', <?= $t['show_on_home'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this testimonial?')">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="delete_id" value="<?= (int)$t['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick update modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Update Testimonial</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= CSRF::field() ?>
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="id" id="modalId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="modalStatus" class="form-select form-select-sm">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="show_on_home" class="form-check-input" id="modalHome">
                        <label class="form-check-label" for="modalHome">Show on Homepage</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-admin-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openTestimonialModal(id, status, showHome) {
    document.getElementById('modalId').value     = id;
    document.getElementById('modalStatus').value = status;
    document.getElementById('modalHome').checked = showHome == 1;
    new bootstrap.Modal(document.getElementById('testimonialModal')).show();
}
</script>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
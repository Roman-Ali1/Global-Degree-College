<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Contact Messages';
$db = Database::getInstance();

// Delete message
if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $db->execute("DELETE FROM contact_messages WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Message deleted.');
    redirect(ADMIN_URL . '/modules/messages.php');
}

// Mark as read when viewing
$viewMsg = null;
if (getInt('view') > 0) {
    $viewMsg = $db->fetch("SELECT * FROM contact_messages WHERE id=?", [getInt('view')]);
    if ($viewMsg && !$viewMsg['is_read']) {
        $db->execute("UPDATE contact_messages SET is_read=1, read_at=NOW() WHERE id=?", [$viewMsg['id']]);
    }
}

$filterRead = get('filter');
$where  = $filterRead === 'unread' ? 'WHERE is_read=0' : ($filterRead === 'read' ? 'WHERE is_read=1' : '');
$total  = (int)$db->fetchColumn("SELECT COUNT(*) FROM contact_messages $where");
$pager  = paginate($total, ADMIN_PER_PAGE);
$messages = $db->fetchAll(
    "SELECT * FROM contact_messages $where ORDER BY created_at DESC
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}"
);

require_once __DIR__ . '/../templates/admin-header.php';
?>

<?php if ($viewMsg): ?>
<div class="d-flex gap-3 align-items-center mb-4">
    <a href="<?= ADMIN_URL ?>/modules/messages.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
    <h5 class="mb-0"><?= h($viewMsg['subject']) ?></h5>
</div>
<div class="admin-card">
    <div class="admin-card-body">
        <div class="row g-3 mb-4">
            <div class="col-sm-4"><div class="view-field"><span class="view-field-label">From</span><span class="view-field-value"><?= h($viewMsg['name']) ?></span></div></div>
            <div class="col-sm-4"><div class="view-field"><span class="view-field-label">Email</span><span class="view-field-value"><?= h($viewMsg['email']) ?></span></div></div>
            <div class="col-sm-4"><div class="view-field"><span class="view-field-label">Phone</span><span class="view-field-value"><?= h($viewMsg['phone'] ?: '—') ?></span></div></div>
            <div class="col-12"><div class="view-field"><span class="view-field-label">Received</span><span class="view-field-value"><?= formatDate($viewMsg['created_at'], 'd M Y, h:i A') ?></span></div></div>
        </div>
        <div class="message-body"><?= nl2br(h($viewMsg['message'])) ?></div>
        <div class="mt-4 d-flex gap-2">
            <a href="mailto:<?= h($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>"
               class="btn btn-admin-primary btn-sm">
                <i class="fas fa-reply me-2"></i>Reply via Email
            </a>
            <form method="POST" onsubmit="return confirm('Delete this message?')">
                <?= CSRF::field() ?>
                <input type="hidden" name="delete_id" value="<?= (int)$viewMsg['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>
</div>

<?php else: ?>

<div class="d-flex gap-2 mb-4">
    <a href="?" class="btn btn-sm <?= !$filterRead ? 'btn-admin-primary' : 'btn-outline-secondary' ?>">All</a>
    <a href="?filter=unread" class="btn btn-sm <?= $filterRead==='unread' ? 'btn-admin-primary' : 'btn-outline-secondary' ?>">Unread</a>
    <a href="?filter=read"   class="btn btn-sm <?= $filterRead==='read'   ? 'btn-admin-primary' : 'btn-outline-secondary' ?>">Read</a>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="admin-table">
            <thead><tr><th>From</th><th>Subject</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($messages)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No messages found.</td></tr>
            <?php else: foreach ($messages as $msg): ?>
                <tr class="<?= !$msg['is_read'] ? 'row-unread' : '' ?>">
                    <td>
                        <div class="fw-600"><?= h($msg['name']) ?></div>
                        <div class="text-muted small"><?= h($msg['email']) ?></div>
                    </td>
                    <td><?= h(truncate($msg['subject'], 50)) ?></td>
                    <td class="text-muted small"><?= formatDate($msg['created_at'], 'd M Y') ?></td>
                    <td>
                        <?php if (!$msg['is_read']): ?>
                            <span class="status-badge badge-warning"><i class="fas fa-circle"></i> New</span>
                        <?php else: ?>
                            <span class="status-badge badge-secondary"><i class="fas fa-check"></i> Read</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?view=<?= (int)$msg['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Events';
$db = Database::getInstance();

if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $row = $db->fetch("SELECT featured_image FROM events WHERE id=?", [postInt('delete_id')]);
    if ($row && $row['featured_image']) @unlink(UPLOAD_EVENTS . $row['featured_image']);
    $db->execute("DELETE FROM events WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Event deleted.');
    redirect(ADMIN_URL . '/modules/events.php');
}

if (isPost() && isset($_POST['save_event'])) {
    CSRF::requireValid();

    $id     = postInt('id');
    $title  = post('title');
    $desc   = post('description');
    $date   = cleanDate($_POST['event_date'] ?? '');
    $time   = cleanString($_POST['event_time'] ?? '');
    // Store NULL for empty time values so the DB TIME column isn't given an empty string
    $time   = $time !== '' ? $time : null;
    $end    = cleanDate($_POST['end_date'] ?? '') ?: null;
    $venue  = post('venue');
    $type   = cleanString($_POST['type'] ?? 'academic');
    $status = cleanString($_POST['status'] ?? 'upcoming');
    $feat   = isset($_POST['is_featured']) ? 1 : 0;

    $imageFile = null;
    if (!empty($_FILES['featured_image']['name'])) {
        $up = new Uploader(UPLOAD_EVENTS);
        $up2 = $up->upload($_FILES['featured_image'], 'event_');
        if ($up2) {
            $imageFile = $up2;
            if ($id > 0) {
                $old = $db->fetchColumn("SELECT featured_image FROM events WHERE id=?", [$id]);
                if ($old) @unlink(UPLOAD_EVENTS . $old);
            }
        }
    }

    if ($id > 0) {
        $sql = "UPDATE events SET title=?, description=?, event_date=?, event_time=?,
                end_date=?, venue=?, type=?, status=?, is_featured=?, created_by=?"
             . ($imageFile ? ", featured_image=?" : "") . " WHERE id=?";
        $p = [$title, $desc, $date, $time, $end, $venue, $type, $status, $feat, Auth::user()['id']];
        if ($imageFile) $p[] = $imageFile;
        $p[] = $id;
        $db->execute($sql, $p);
        setFlash('success', 'Event updated.');
    } else {
        $slug = slugify($title);
        $ex = $db->fetchColumn("SELECT id FROM events WHERE slug=?", [$slug]);
        if ($ex) $slug .= '-' . time();
        $db->insert(
            "INSERT INTO events (title, slug, description, event_date, event_time,
             end_date, venue, type, status, is_featured, featured_image, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            [$title, $slug, $desc, $date, $time, $end, $venue, $type,
             $status, $feat, $imageFile, Auth::user()['id']]
        );
        setFlash('success', 'Event created.');
    }
    redirect(ADMIN_URL . '/modules/events.php');
}

$editEvent = getInt('edit') > 0
    ? $db->fetch("SELECT * FROM events WHERE id=?", [getInt('edit')])
    : null;

$events = $db->fetchAll("SELECT * FROM events ORDER BY event_date DESC");

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editEvent ? 'Edit Event' : 'Add Event' ?></h6>
                <?php if ($editEvent): ?><a href="?" class="btn-card-link">+ New</a><?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_event" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editEvent['id'] ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               value="<?= h($editEvent['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control form-control-sm"><?= h($editEvent['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control form-control-sm"
                                   value="<?= h($editEvent['event_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Time</label>
                            <input type="time" name="event_time" class="form-control form-control-sm"
                                   value="<?= h($editEvent['event_time'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                               value="<?= h($editEvent['end_date'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control form-control-sm"
                               value="<?= h($editEvent['venue'] ?? '') ?>"
                               placeholder="e.g. Main Auditorium">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select form-select-sm">
                                                        <?php foreach (['academic','sports','cultural','exam'] as $t): ?>
                                <option value="<?= $t ?>"
                                    <?= ($editEvent['type'] ?? 'academic') === $t ? 'selected' : '' ?>>
                                    <?= ucfirst($t) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (['upcoming','ongoing','completed','cancelled'] as $s): ?>
                                <option value="<?= $s ?>"
                                    <?= ($editEvent['status'] ?? 'upcoming') === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Banner Image</label>
                        <input type="file" name="featured_image"
                               class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input"
                               id="eFeat" <?= ($editEvent['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="eFeat">Featured Event</label>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i><?= $editEvent ? 'Update' : 'Create Event' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-calendar-alt me-2"></i>All Events
                    <span class="badge bg-secondary ms-2"><?= count($events) ?></span>
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr><th>Title</th><th>Date</th><th>Venue</th><th>Type</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td><div class="fw-600"><?= h(truncate($ev['title'], 40)) ?></div></td>
                            <td class="small"><?= formatDate($ev['event_date'], 'd M Y') ?></td>
                            <td class="small text-muted"><?= h(truncate($ev['venue'] ?? '—', 25)) ?></td>
                            <td><span class="status-badge badge-info"><?= ucfirst($ev['type']) ?></span></td>
                            <td>
                                <?php
                                $cls = match($ev['status']) {
                                    'upcoming'  => 'badge-warning',
                                    'ongoing'   => 'badge-success',
                                    'completed' => 'badge-secondary',
                                    default     => 'badge-danger'
                                };
                                ?>
                                <span class="status-badge <?= $cls ?>"><?= ucfirst($ev['status']) ?></span>
                            </td>
                            <td>
                                <a href="?edit=<?= (int)$ev['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this event?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$ev['id'] ?>">
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
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Hostel Information';
$db = Database::getInstance();

if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $db->execute("UPDATE hostel_information SET is_active=0 WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'Hostel deactivated.');
    redirect(ADMIN_URL . '/modules/hostel.php');
}

if (isPost() && isset($_POST['save_hostel'])) {
    CSRF::requireValid();

    $id         = postInt('id');
    $name       = post('hostel_name');
    $gender     = cleanString($_POST['gender'] ?? 'male');
    $address    = post('address');
    $rooms      = postInt('total_rooms');
    $capacity   = postInt('capacity');
    $fee        = cleanFloat($_POST['fee_per_month'] ?? '0');
    $facilities = post('facilities'); // comma-separated, stored as JSON
    $warden     = post('warden_name');
    $warden_ph  = cleanPhone($_POST['warden_contact'] ?? '');
    $active     = isset($_POST['is_active']) ? 1 : 0;

    // Convert comma-separated facilities to JSON array
    $facilityArr = array_map('trim', explode(',', $facilities));
    $facilityArr = array_filter($facilityArr);
    $facilityJson = json_encode(array_values($facilityArr));

    if ($id > 0) {
        $db->execute(
            "UPDATE hostel_information SET hostel_name=?, gender=?, address=?,
             total_rooms=?, capacity=?, fee_per_month=?, facilities=?,
             warden_name=?, warden_contact=?, is_active=? WHERE id=?",
            [$name, $gender, $address, $rooms, $capacity, $fee,
             $facilityJson, $warden, $warden_ph, $active, $id]
        );
        setFlash('success', 'Hostel updated.');
    } else {
        $db->insert(
            "INSERT INTO hostel_information
             (hostel_name, gender, address, total_rooms, capacity, fee_per_month,
              facilities, warden_name, warden_contact, is_active)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$name, $gender, $address, $rooms, $capacity, $fee,
             $facilityJson, $warden, $warden_ph, $active]
        );
        setFlash('success', 'Hostel added.');
    }
    redirect(ADMIN_URL . '/modules/hostel.php');
}

$editH = getInt('edit') > 0
    ? $db->fetch("SELECT * FROM hostel_information WHERE id=?", [getInt('edit')])
    : null;

// Decode facilities JSON back to comma-separated for the form
$editFacilities = '';
if ($editH && $editH['facilities']) {
    $arr = json_decode($editH['facilities'], true);
    if (is_array($arr)) $editFacilities = implode(', ', $arr);
}

$hostels = $db->fetchAll("SELECT * FROM hostel_information ORDER BY gender, hostel_name");

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editH ? 'Edit Hostel' : 'Add Hostel' ?></h6>
                <?php if ($editH): ?><a href="?" class="btn-card-link">+ New</a><?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_hostel" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editH['id'] ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">Hostel Name <span class="text-danger">*</span></label>
                        <input type="text" name="hostel_name" class="form-control form-control-sm"
                               value="<?= h($editH['hostel_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select form-select-sm">
                            <?php foreach (['male','female','mixed'] as $g): ?>
                            <option value="<?= $g ?>"
                                <?= ($editH['gender'] ?? 'male') === $g ? 'selected' : '' ?>>
                                <?= ucfirst($g) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="2"
                                  class="form-control form-control-sm"><?= h($editH['address'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Total Rooms</label>
                            <input type="number" name="total_rooms"
                                   class="form-control form-control-sm"
                                   value="<?= (int)($editH['total_rooms'] ?? 0) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity"
                                   class="form-control form-control-sm"
                                   value="<?= (int)($editH['capacity'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fee / Month (PKR)</label>
                        <input type="number" name="fee_per_month" step="0.01"
                               class="form-control form-control-sm"
                               value="<?= (float)($editH['fee_per_month'] ?? 0) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Facilities</label>
                        <input type="text" name="facilities" class="form-control form-control-sm"
                               value="<?= h($editFacilities) ?>"
                               placeholder="WiFi, Hot Water, CCTV, ...">
                        <div class="form-text">Comma-separated list</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Warden Name</label>
                        <input type="text" name="warden_name" class="form-control form-control-sm"
                               value="<?= h($editH['warden_name'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Warden Contact</label>
                        <input type="text" name="warden_contact" class="form-control form-control-sm"
                               value="<?= h($editH['warden_contact'] ?? '') ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input"
                               id="hActive" <?= ($editH['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="hActive">Active</label>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i><?= $editH ? 'Update' : 'Add Hostel' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-building me-2"></i>Hostels
                    <span class="badge bg-secondary ms-2"><?= count($hostels) ?></span>
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr><th>Name</th><th>Gender</th><th>Rooms</th><th>Capacity</th><th>Fee/mo</th><th>Warden</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($hostels as $h): ?>
                        <tr>
                            <td><div class="fw-600"><?= h($h['hostel_name']) ?></div></td>
                            <td>
                                <span class="status-badge <?= $h['gender']==='male' ? 'badge-info' : 'badge-success' ?>">
                                    <?= ucfirst($h['gender']) ?>
                                </span>
                            </td>
                            <td><?= (int)$h['total_rooms'] ?></td>
                            <td><?= (int)$h['capacity'] ?></td>
                            <td class="small"><?= formatCurrency((float)$h['fee_per_month']) ?></td>
                            <td class="small text-muted"><?= h($h['warden_name'] ?? '—') ?></td>
                            <td>
                                <a href="?edit=<?= (int)$h['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Deactivate?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$h['id'] ?>">
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
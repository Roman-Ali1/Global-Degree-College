<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';
Auth::requireRole(['super_admin']);

$adminPageTitle = 'Site Settings';
$db = Database::getInstance();

if (isPost()) {
    CSRF::requireValid();
    $keys = $db->fetchAll("SELECT `key`, `type` FROM settings");
    foreach ($keys as $row) {
        $key  = $row['key'];
        $type = $row['type'];
        if ($type === 'toggle') {
            $value = isset($_POST[$key]) ? '1' : '0';
        } elseif ($type === 'image' && !empty($_FILES[$key]['name'])) {
            $uploader = new Uploader(ROOT_PATH . '/assets/images/logo/');
            $uploaded = $uploader->upload($_FILES[$key], $key . '_');
            $value    = $uploaded ? 'assets/images/logo/' . $uploaded : null;
            if (!$value) continue;
        } else {
            $value = cleanString($_POST[$key] ?? '');
        }
        $db->execute("UPDATE settings SET `value`=? WHERE `key`=?", [$value, $key]);
    }
    // Reload global settings
    $GLOBALS['site_settings'] = [];
    $rows = $db->fetchAll("SELECT `key`,`value` FROM settings");
    foreach ($rows as $r) $GLOBALS['site_settings'][$r['key']] = $r['value'];

    setFlash('success', 'Settings saved successfully.');
    redirect(ADMIN_URL . '/modules/settings.php');
}

// Group settings by group column
$allSettings = $db->fetchAll("SELECT * FROM settings ORDER BY `group`, id");
$grouped = [];
foreach ($allSettings as $s) {
    $grouped[$s['group']][] = $s;
}

require_once __DIR__ . '/../templates/admin-header.php';
?>

<form method="POST" enctype="multipart/form-data">
    <?= CSRF::field() ?>

    <!-- Tab nav -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs">
        <?php $first = true; foreach (array_keys($grouped) as $group): ?>
        <li class="nav-item">
            <button class="nav-link <?= $first ? 'active' : '' ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-<?= h($group) ?>">
                <?= ucfirst(h($group)) ?>
            </button>
        </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php $first = true; foreach ($grouped as $group => $fields): ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= h($group) ?>">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="row g-3">
                    <?php foreach ($fields as $field): ?>
                        <div class="col-md-6">
                            <label class="form-label"><?= h($field['label'] ?? $field['key']) ?></label>
                            <?php if ($field['type'] === 'toggle'): ?>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           name="<?= h($field['key']) ?>"
                                           id="<?= h($field['key']) ?>"
                                           <?= $field['value'] === '1' ? 'checked' : '' ?>>
                                </div>
                            <?php elseif ($field['type'] === 'textarea'): ?>
                                <textarea name="<?= h($field['key']) ?>" rows="3"
                                          class="form-control form-control-sm"><?= h($field['value'] ?? '') ?></textarea>
                            <?php elseif ($field['type'] === 'image'): ?>
                                <?php if ($field['value']): ?>
                                <div class="mb-1">
                                    <img src="<?= BASE_URL ?>/<?= h($field['value']) ?>" height="40"
                                         onerror="this.style.display='none'">
                                </div>
                                <?php endif; ?>
                                <input type="file" name="<?= h($field['key']) ?>"
                                       class="form-control form-control-sm" accept="image/*">
                            <?php else: ?>
                                <input type="<?= h($field['type']) ?>"
                                       name="<?= h($field['key']) ?>"
                                       class="form-control form-control-sm"
                                       value="<?= h($field['value'] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $first = false; endforeach; ?>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-admin-primary">
            <i class="fas fa-save me-2"></i>Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
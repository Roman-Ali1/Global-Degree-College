<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'Gallery';
$db = Database::getInstance();

// ── DELETE ────────────────────────────────────────────────────
if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $img = $db->fetch("SELECT filename FROM gallery WHERE id=?", [postInt('delete_id')]);
    if ($img) {
        @unlink(UPLOAD_GALLERY . $img['filename']);
        $db->execute("DELETE FROM gallery WHERE id=?", [postInt('delete_id')]);
    }
    setFlash('success', 'Image deleted.');
    redirect(ADMIN_URL . '/modules/gallery.php');
}

// ── UPLOAD ────────────────────────────────────────────────────
if (isPost() && isset($_POST['upload_image'])) {
    CSRF::requireValid();

    $title    = post('title');
    $category = post('category') ?: 'General';
    $desc     = post('description');

    if (empty($_FILES['image']['name'])) {
        setFlash('error', 'Please select an image to upload.');
    } else {
        $uploader = new Uploader(UPLOAD_GALLERY);
        $uploaded = $uploader->upload($_FILES['image'], 'gallery_');
        if ($uploaded) {
            $db->insert(
                "INSERT INTO gallery (title, description, filename, category, uploaded_by)
                 VALUES (?,?,?,?,?)",
                [$title, $desc, $uploaded, $category, Auth::user()['id']]
            );
            setFlash('success', 'Image uploaded successfully.');
        } else {
            setFlash('error', $uploader->getError());
        }
    }
    redirect(ADMIN_URL . '/modules/gallery.php');
}

$filterCat = trim((string)get('category'));
$defaultGalleryCategories = ['Campus', 'Events', 'Sports', 'General'];

$categoryRows = $db->fetchAll("SELECT DISTINCT category FROM gallery ORDER BY category");
$categoryNames = [];
foreach ($categoryRows as $row) {
    $name = trim((string)($row['category'] ?? ''));
    if ($name !== '') {
        $categoryNames[] = $name;
    }
}
$categories = array_values(array_unique(array_merge($defaultGalleryCategories, $categoryNames)));

$params = [];
$where  = '1=1';
if ($filterCat) { $where .= ' AND LOWER(category) = LOWER(?)'; $params[] = $filterCat; }

$total    = (int)$db->fetchColumn("SELECT COUNT(*) FROM gallery WHERE $where", $params);
$pager    = paginate($total, 20);
$images   = $db->fetchAll(
    "SELECT * FROM gallery WHERE $where ORDER BY created_at DESC
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}",
    $params
);
require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">

    <!-- Upload Form -->
    <div class="col-lg-3">
        <div class="admin-card">
            <div class="admin-card-header"><h6>Upload Image</h6></div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="upload_image" value="1">
                    <div class="mb-2">
                        <label class="form-label">Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp" required>
                        <div class="form-text">JPG/PNG/WEBP, max 5MB</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               placeholder="Image title">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control form-control-sm"
                               list="catList" value="General">
                        <datalist id="catList">
                            <option>Campus</option>
                            <option>Events</option>
                            <option>Sports</option>
                            <option>Labs</option>
                            <option>General</option>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Optional..."></textarea>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="col-lg-9">

        <!-- Category filter -->
        <div class="d-flex gap-2 flex-wrap mb-3">
            <a href="?" class="btn btn-sm <?= !$filterCat ? 'btn-admin-primary' : 'btn-outline-secondary' ?>">
                All (<?= $total ?>)
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?>"
               class="btn btn-sm <?= strcasecmp($filterCat, (string)$cat) === 0 ? 'btn-admin-primary' : 'btn-outline-secondary' ?>">
                <?= h($cat) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <?php if (empty($images)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-images fa-2x mb-2"></i>
                <p>No images uploaded yet.</p>
            </div>
            <?php else: foreach ($images as $img): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="gallery-admin-item">
                    <img src="<?= uploadUrl('gallery', $img['filename']) ?>"
                         alt="<?= h($img['title']) ?>"
                         loading="lazy">
                    <div class="gallery-admin-overlay">
                        <span class="gallery-cat-tag"><?= h($img['category']) ?></span>
                        <form method="POST"
                              onsubmit="return confirm('Delete this image permanently?')">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="delete_id" value="<?= (int)$img['id'] ?>">
                            <button class="gallery-delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php if ($img['title']): ?>
                    <div class="gallery-admin-caption"><?= h(truncate($img['title'], 30)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pager['pages'] > 1): ?>
        <div class="admin-pagination mt-3">
            <?php for ($p = 1; $p <= $pager['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&category=<?= urlencode($filterCat) ?>"
               class="page-btn <?= $p === $pager['current'] ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Gallery admin styles (inline, small enough) -->
<style>
.gallery-admin-item { position:relative; border-radius:8px; overflow:hidden; background:#f0f4f8; aspect-ratio:1/1; }
.gallery-admin-item img { width:100%; height:100%; object-fit:cover; display:block; }
.gallery-admin-overlay { position:absolute; inset:0; background:rgba(15,37,72,0.6); opacity:0; transition:all .25s; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; }
.gallery-admin-item:hover .gallery-admin-overlay { opacity:1; }
.gallery-cat-tag { background:rgba(201,168,76,0.9); color:#0f2548; font-size:0.65rem; font-weight:700; padding:2px 10px; border-radius:20px; text-transform:uppercase; }
.gallery-delete-btn { background:#e53e3e; color:#fff; border:none; width:34px; height:34px; border-radius:50%; cursor:pointer; font-size:0.8rem; }
.gallery-admin-caption { padding:6px 8px; font-size:0.72rem; color:#4a5568; background:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>

<?php require_once __DIR__ . '/../templates/admin-footer.php'; ?>
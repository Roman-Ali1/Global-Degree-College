<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config/app.php';

$adminPageTitle = 'News';
$db = Database::getInstance();

// ── DELETE ────────────────────────────────────────────────────
if (isPost() && isset($_POST['delete_id'])) {
    CSRF::requireValid();
    $row = $db->fetch("SELECT featured_image FROM news WHERE id=?", [postInt('delete_id')]);
    if ($row && $row['featured_image']) @unlink(UPLOAD_NEWS . $row['featured_image']);
    $db->execute("DELETE FROM news WHERE id=?", [postInt('delete_id')]);
    setFlash('success', 'News article deleted.');
    redirect(ADMIN_URL . '/modules/news.php');
}

// ── SAVE ──────────────────────────────────────────────────────
if (isPost() && isset($_POST['save_news'])) {
    CSRF::requireValid();

    $id       = postInt('id');
    $title    = post('title');
    $excerpt  = post('excerpt');
    $body     = cleanString($_POST['body'] ?? ''); // strip_tags for now
    $category = post('category');
    $status   = in_array($_POST['status'] ?? '', ['draft','published','archived'])
                ? $_POST['status'] : 'draft';
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $pubAt    = $status === 'published' ? date('Y-m-d H:i:s') : null;

    // Image upload
    $imageFile = null;
    if (!empty($_FILES['featured_image']['name'])) {
        $up = new Uploader(UPLOAD_NEWS);
        $uploaded = $up->upload($_FILES['featured_image'], 'news_');
        if ($uploaded) {
            $imageFile = $uploaded;
            if ($id > 0) {
                $old = $db->fetchColumn("SELECT featured_image FROM news WHERE id=?", [$id]);
                if ($old) @unlink(UPLOAD_NEWS . $old);
            }
        } else {
            setFlash('error', $up->getError());
            redirect(ADMIN_URL . '/modules/news.php' . ($id ? "?edit=$id" : ''));
        }
    }

    if ($id > 0) {
        $sql = "UPDATE news SET title=?, excerpt=?, body=?, category=?,
                status=?, is_featured=?, published_at=COALESCE(published_at, ?),
                author_id=?"
             . ($imageFile ? ", featured_image=?" : "")
             . " WHERE id=?";
        $p = [$title, $excerpt, $body, $category, $status, $featured,
              $pubAt, Auth::user()['id']];
        if ($imageFile) $p[] = $imageFile;
        $p[] = $id;
        $db->execute($sql, $p);
        setFlash('success', 'Article updated.');
    } else {
        $slug = slugify($title);
        $exists = $db->fetchColumn("SELECT id FROM news WHERE slug=?", [$slug]);
        if ($exists) $slug .= '-' . time();

        $db->insert(
            "INSERT INTO news (title, slug, excerpt, body, featured_image,
             category, author_id, is_featured, status, published_at)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$title, $slug, $excerpt, $body, $imageFile,
             $category, Auth::user()['id'], $featured, $status, $pubAt]
        );
        setFlash('success', 'Article created.');
    }
    redirect(ADMIN_URL . '/modules/news.php');
}

$editNews = getInt('edit') > 0
    ? $db->fetch("SELECT * FROM news WHERE id=?", [getInt('edit')])
    : null;

$newsList = $db->fetchAll(
    "SELECT n.*, a.full_name AS author
     FROM news n LEFT JOIN admins a ON n.author_id = a.id
     ORDER BY n.created_at DESC"
);

require_once __DIR__ . '/../templates/admin-header.php';
?>

<div class="row g-4">

    <!-- Form -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><?= $editNews ? 'Edit Article' : 'Add Article' ?></h6>
                <?php if ($editNews): ?>
                <a href="?" class="btn-card-link">+ New</a>
                <?php endif; ?>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="save_news" value="1">
                    <input type="hidden" name="id" value="<?= (int)($editNews['id'] ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               value="<?= h($editNews['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control form-control-sm"
                               list="newsCats"
                               value="<?= h($editNews['category'] ?? '') ?>"
                               placeholder="e.g. Announcement">
                        <datalist id="newsCats">
                            <option>Announcement</option>
                            <option>Achievement</option>
                            <option>Result</option>
                            <option>Event</option>
                            <option>General</option>
                        </datalist>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Short summary..."><?= h($editNews['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Body <span class="text-danger">*</span></label>
                        <textarea name="body" rows="6"
                                  class="form-control form-control-sm"><?= h($editNews['body'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Featured Image</label>
                        <?php if (!empty($editNews['featured_image'])): ?>
                        <div class="mb-1">
                            <img src="<?= uploadUrl('news', $editNews['featured_image']) ?>"
                                 height="60" class="rounded border">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image"
                               class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach (['draft','published','archived'] as $s): ?>
                            <option value="<?= $s ?>"
                                <?= ($editNews['status'] ?? 'draft') === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input"
                               id="nFeatured"
                               <?= ($editNews['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="nFeatured">Featured Article</label>
                    </div>
                    <button class="btn btn-admin-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>
                        <?= $editNews ? 'Update Article' : 'Publish Article' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-newspaper me-2"></i>All Articles
                    <span class="badge bg-secondary ms-2"><?= count($newsList) ?></span>
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <table class="admin-table">
                    <thead>
                        <tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($newsList as $n): ?>
                        <tr>
                            <td>
                                <div class="fw-600"><?= h(truncate($n['title'], 45)) ?></div>
                                <div class="text-muted small"><?= formatDate($n['created_at'], 'd M Y') ?></div>
                            </td>
                            <td class="small"><?= h($n['category'] ?: '—') ?></td>
                            <td class="small text-muted"><?= h($n['author'] ?? '—') ?></td>
                            <td>
                                <?php
                                $cls = match($n['status']) {
                                    'published' => 'badge-success',
                                    'draft'     => 'badge-warning',
                                    default     => 'badge-secondary'
                                };
                                ?>
                                <span class="status-badge <?= $cls ?>"><?= ucfirst($n['status']) ?></span>
                            </td>
                            <td>
                                <a href="?edit=<?= (int)$n['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this article?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$n['id'] ?>">
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
<?php
// admin/books.php — Manage Book Catalog
require_once __DIR__ . '/../config.php';
requireAdmin();

$db = getDB();
$errors = [];
$success = '';

// ── Delete book ──────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Check no orders reference this book (referenced by order_items FK)
    try {
        $stmt = $db->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$delId]);
        $success = 'Book deleted successfully.';
    } catch (PDOException $e) {
        $errors['db'] = 'Cannot delete this book — it is referenced by existing orders.';
    }
}

// ── Add book ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title  = trim($_POST['title']  ?? '');
    $author = trim($_POST['author'] ?? '');
    $price  = trim($_POST['price']  ?? '');
    $desc   = trim($_POST['description'] ?? '');

    if ($title  === '') $errors['title']  = 'Title is required.';
    if ($author === '') $errors['author'] = 'Author is required.';
    if (!is_numeric($price) || (float)$price <= 0)
                        $errors['price']  = 'Enter a valid price greater than 0.';

    // Handle image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed   = ['jpg','jpeg','png','gif','webp'];
        $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors['image'] = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
        } else {
            $imageName = uniqid('book_') . '.' . $ext;
            $dest      = __DIR__ . '/../images/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors['image'] = 'Image upload failed. Check server write permissions.';
                $imageName = null;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO books (title, author, price, image, description) VALUES (?,?,?,?,?)");
        $stmt->execute([$title, $author, (float)$price, $imageName, $desc]);
        $success = "Book \"$title\" added successfully.";
    }
}

// ── Fetch all books ──────────────────────────────────────
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;
$total   = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();
$pages   = ceil($total / $perPage);
$books   = $db->query("SELECT * FROM books ORDER BY created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Books — BookHaven Admin</title>
  <link rel="stylesheet" href="/obs/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <span class="site-logo">📚 Book<span>Haven</span> Admin</span>
    <div style="display:flex;align-items:center;gap:1rem;">
      <span style="color:rgba(255,255,255,.6);font-size:.9rem;">
        <?= e($_SESSION['admin_username']) ?>
      </span>
      <a href="/obs/admin/logout.php" class="btn btn-outline btn-sm" style="color:#fff;border-color:rgba(255,255,255,.3);">Logout</a>
    </div>
  </div>
</header>

<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-brand">🔧 Admin Panel</div>
    <nav>
      <a href="/obs/admin/index.php">📊 Dashboard</a>
      <a href="/obs/admin/books.php" class="active">📚 Manage Books</a>
      <a href="/obs/admin/orders.php">📦 View Orders</a>
      <a href="/obs/index.php" target="_blank">🌐 View Store</a>
      <a href="/obs/admin/logout.php">🚪 Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <h1>Manage Books</h1>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['db'])): ?>
      <div class="alert alert-error"><?= e($errors['db']) ?></div>
    <?php endif; ?>

    <!-- Add Book Form -->
    <div style="background:var(--card-bg);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:2rem;margin-bottom:2.5rem;">
      <h2 style="font-family:var(--font-head);font-size:1.3rem;margin-bottom:1.5rem;">➕ Add New Book</h2>
      <form action="/obs/admin/books.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
          <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" class="form-control <?= isset($errors['title']) ? 'invalid' : '' ?>"
                   placeholder="Book Title" value="<?= e($_POST['title'] ?? '') ?>">
            <?php if (isset($errors['title'])): ?><div class="field-error visible"><?= e($errors['title']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="author">Author *</label>
            <input type="text" id="author" name="author" class="form-control <?= isset($errors['author']) ? 'invalid' : '' ?>"
                   placeholder="Author Name" value="<?= e($_POST['author'] ?? '') ?>">
            <?php if (isset($errors['author'])): ?><div class="field-error visible"><?= e($errors['author']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01"
                   class="form-control <?= isset($errors['price']) ? 'invalid' : '' ?>"
                   placeholder="14.99" value="<?= e($_POST['price'] ?? '') ?>">
            <?php if (isset($errors['price'])): ?><div class="field-error visible"><?= e($errors['price']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="image">Cover Image (optional)</label>
            <input type="file" id="image" name="image" class="form-control"
                   accept="image/jpeg,image/png,image/gif,image/webp">
            <?php if (isset($errors['image'])): ?><div class="field-error visible"><?= e($errors['image']) ?></div><?php endif; ?>
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="form-control"
                      placeholder="Brief description of the book…"><?= e($_POST['description'] ?? '') ?></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Book</button>
      </form>
    </div>

    <!-- Book List -->
    <h2 style="font-family:var(--font-head);font-size:1.3rem;margin-bottom:1rem;">
      Book Catalog <small style="font-size:.8rem;color:var(--muted);font-weight:400;">(<?= $total ?> books)</small>
    </h2>
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Cover</th>
          <th>Title</th>
          <th>Author</th>
          <th>Price</th>
          <th>Added</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $book): ?>
          <tr>
            <td><?= $book['id'] ?></td>
            <td>
              <?php if (!empty($book['image']) && file_exists(__DIR__ . '/../images/' . $book['image'])): ?>
                <img src="/obs/images/<?= e($book['image']) ?>" alt="" style="width:40px;height:56px;object-fit:cover;border-radius:4px;">
              <?php else: ?>
                <div style="width:40px;height:56px;background:linear-gradient(135deg,#3d2512,#6b3a1f);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;">📖</div>
              <?php endif; ?>
            </td>
            <td><strong><?= e($book['title']) ?></strong></td>
            <td><?= e($book['author']) ?></td>
            <td>$<?= number_format($book['price'], 2) ?></td>
            <td><?= date('M j, Y', strtotime($book['created_at'])) ?></td>
            <td>
              <a href="/obs/admin/books.php?delete=<?= $book['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete \'<?= e(addslashes($book['title'])) ?>\'? This cannot be undone.')">
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <div style="display:flex;gap:.5rem;margin-top:1.25rem;">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <a href="?page=<?= $p ?>"
             class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

</body>
</html>

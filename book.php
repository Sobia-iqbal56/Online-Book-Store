<?php
// book.php — Individual Book Detail Page
require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/obs/index.php');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    http_response_code(404);
    $pageTitle = 'Book Not Found';
    include __DIR__ . '/partials/header.php';
    echo '<div class="container"><div class="alert alert-error">Book not found. <a href="/obs/index.php">Return to catalog</a>.</div></div>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = $book['title'];
include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <p class="breadcrumb">
    <a href="/obs/index.php">Catalog</a> › <span><?= e($book['title']) ?></span>
  </p>

  <?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success">✅ "<?= e($book['title']) ?>" added to your cart. <a href="/obs/cart.php">View Cart →</a></div>
  <?php endif; ?>

  <div class="book-detail">
    <!-- Cover -->
    <div class="cover-wrap">
      <?php if (!empty($book['image']) && file_exists(__DIR__ . '/images/' . $book['image'])): ?>
        <img src="/obs/images/<?= e($book['image']) ?>" alt="<?= e($book['title']) ?>">
      <?php else: ?>
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,.7);padding:2rem;text-align:center;">
          <div style="font-size:5rem;">📖</div>
          <div style="font-family:var(--font-head);font-size:1.1rem;font-style:italic;margin-top:1rem;line-height:1.3;"><?= e($book['title']) ?></div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="detail-info">
      <h1><?= e($book['title']) ?></h1>
      <div class="author">by <?= e($book['author']) ?></div>
      <div class="detail-price">$<?= number_format($book['price'], 2) ?></div>

      <?php if (!empty($book['description'])): ?>
        <p class="description"><?= e($book['description']) ?></p>
      <?php endif; ?>

      <form action="/obs/cart.php" method="POST">
        <input type="hidden" name="action"  value="add">
        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
        <div class="qty-input">
          <label for="qty">Quantity:</label>
          <input type="number" id="qty" name="qty" value="1" min="1" max="99">
        </div>
        <button type="submit" class="btn btn-primary">🛒 Add to Cart</button>
        &nbsp;
        <a href="/obs/index.php" class="btn btn-outline">← Back to Catalog</a>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

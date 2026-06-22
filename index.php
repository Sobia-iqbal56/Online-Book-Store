<?php
// index.php — Homepage / Book Catalog
require_once __DIR__ . '/config.php';

$pageTitle = 'Book Catalog';
$db = getDB();

// Search query
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $db->prepare("SELECT * FROM books WHERE title LIKE :q OR author LIKE :q ORDER BY created_at DESC");
    $stmt->execute([':q' => "%$search%"]);
} else {
    $stmt = $db->query("SELECT * FROM books ORDER BY created_at DESC");
}
$books = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<?php if ($search === ''): ?>
<!-- Hero — only shown on unfiltered catalog -->
<section class="hero">
  <h1>Discover Your Next<br><em>Great Read</em></h1>
  <p>Browse our curated collection of timeless classics and modern favourites.</p>
  <a href="#catalog" class="btn btn-gold">Browse Books</a>
</section>
<?php endif; ?>

<div class="container" id="catalog">
  <?php if ($search !== ''): ?>
    <p class="breadcrumb">
      <a href="/obs/index.php">Catalog</a> › <span>Search: "<?= e($search) ?>"</span>
    </p>
    <h2 class="section-title">
      <?= count($books) ?> result<?= count($books) !== 1 ? 's' : '' ?> for "<?= e($search) ?>"
    </h2>
  <?php else: ?>
    <h2 class="section-title">All Books</h2>
  <?php endif; ?>

  <?php if (empty($books)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>No books found</h3>
      <p>Try a different search term or <a href="/obs/index.php">browse all books</a>.</p>
    </div>
  <?php else: ?>
    <div class="books-grid">
      <?php foreach ($books as $book): ?>
        <div class="book-card">
          <a href="/obs/book.php?id=<?= $book['id'] ?>">
            <div class="book-cover">
              <?php if (!empty($book['image']) && file_exists(__DIR__ . '/images/' . $book['image'])): ?>
                <img src="/obs/images/<?= e($book['image']) ?>" alt="<?= e($book['title']) ?>">
              <?php else: ?>
                <div class="book-cover-placeholder">
                  <div class="ph-icon">📖</div>
                  <div class="ph-title"><?= e($book['title']) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </a>
          <div class="book-info">
            <div class="book-title">
              <a href="/obs/book.php?id=<?= $book['id'] ?>"><?= e($book['title']) ?></a>
            </div>
            <div class="book-author">by <?= e($book['author']) ?></div>
            <div class="book-price">$<?= number_format($book['price'], 2) ?></div>
            <div class="book-actions">
              <a href="/obs/book.php?id=<?= $book['id'] ?>" class="btn btn-outline btn-sm">Details</a>
              <form action="/obs/cart.php" method="POST" style="display:inline;">
                <input type="hidden" name="action"  value="add">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <input type="hidden" name="qty"     value="1">
                <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

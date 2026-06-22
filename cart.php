<?php
// cart.php — Shopping Cart (session-based)
require_once __DIR__ . '/config.php';

$db = getDB();

// ── Handle POST actions ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $bookId  = (int)($_POST['book_id'] ?? 0);

    if ($action === 'add' && $bookId > 0) {
        // Verify book exists
        $stmt = $db->prepare("SELECT id, title, price FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch();

        if ($book) {
            $qty = max(1, (int)($_POST['qty'] ?? 1));
            if (isset($_SESSION['cart'][$bookId])) {
                $_SESSION['cart'][$bookId]['quantity'] += $qty;
            } else {
                $_SESSION['cart'][$bookId] = [
                    'book_id'  => $bookId,
                    'title'    => $book['title'],
                    'price'    => $book['price'],
                    'quantity' => $qty,
                ];
            }
        }
        redirect('/obs/book.php?id=' . $bookId . '&added=1');
    }

    if ($action === 'update' && $bookId > 0) {
        $qty = (int)($_POST['qty'] ?? 0);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$bookId]);
        } else {
            if (isset($_SESSION['cart'][$bookId])) {
                $_SESSION['cart'][$bookId]['quantity'] = $qty;
            }
        }
        redirect('/obs/cart.php');
    }

    if ($action === 'remove' && $bookId > 0) {
        unset($_SESSION['cart'][$bookId]);
        redirect('/obs/cart.php');
    }
}

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pageTitle = 'Shopping Cart';
include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <p class="breadcrumb"><a href="/obs/index.php">Catalog</a> › <span>Shopping Cart</span></p>
  <h2 class="section-title">Shopping Cart</h2>

  <?php if (empty($cart)): ?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <h3>Your cart is empty</h3>
      <p>Browse the catalog and add some books to get started.</p>
      <a href="/obs/index.php" class="btn btn-primary" style="margin-top:1rem;">Browse Books</a>
    </div>
  <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr>
          <th>Book</th>
          <th>Author</th>
          <th>Unit Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart as $item): ?>
          <tr>
            <td><strong><?= e($item['title']) ?></strong></td>
            <td>—</td>
            <td>$<?= number_format($item['price'], 2) ?></td>
            <td>
              <form action="/obs/cart.php" method="POST" style="display:flex;align-items:center;gap:.4rem;">
                <input type="hidden" name="action"  value="update">
                <input type="hidden" name="book_id" value="<?= $item['book_id'] ?>">
                <input type="number" name="qty" value="<?= $item['quantity'] ?>" min="1" max="99">
                <button type="submit" class="btn btn-outline btn-sm">Update</button>
              </form>
            </td>
            <td><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
            <td>
              <form action="/obs/cart.php" method="POST">
                <input type="hidden" name="action"  value="remove">
                <input type="hidden" name="book_id" value="<?= $item['book_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Remove this item?')">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="cart-total">
      Total: $<?= number_format($total, 2) ?>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:1rem;justify-content:flex-end;">
      <a href="/obs/index.php" class="btn btn-outline">← Continue Shopping</a>
      <a href="/obs/checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

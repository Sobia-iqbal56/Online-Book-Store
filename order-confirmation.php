<?php
// order-confirmation.php
require_once __DIR__ . '/config.php';

// Only accessible right after placing an order
if (!isset($_SESSION['last_order'])) {
    redirect('/obs/index.php');
}

$order     = $_SESSION['last_order'];
unset($_SESSION['last_order']); // Show once only

$pageTitle = 'Order Confirmed';
include __DIR__ . '/partials/header.php';
?>

<div class="container" style="max-width:700px;">
  <div class="confirm-card">
    <div class="confirm-icon">🎉</div>
    <h2>Order Confirmed!</h2>
    <p style="color:var(--muted);margin-bottom:.5rem;">
      Thank you, <strong><?= e($order['customer_name']) ?></strong>! Your order has been placed successfully.
    </p>
    <div class="order-id-badge">Order #<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></div>

    <table class="order-summary-table">
      <thead>
        <tr>
          <th>Book</th>
          <th>Qty</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td><?= e($item['title']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2"><strong>Total</strong></td>
          <td><strong style="color:var(--accent);">$<?= number_format($order['total'], 2) ?></strong></td>
        </tr>
      </tfoot>
    </table>

    <p style="font-size:.9rem;color:var(--muted);margin-bottom:1.5rem;">
      📦 Delivery address: <em><?= e($order['address']) ?></em>
    </p>

    <a href="/obs/index.php" class="btn btn-primary">Continue Shopping</a>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

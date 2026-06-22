<?php
// checkout.php — Checkout Form
require_once __DIR__ . '/config.php';

// Redirect if cart is empty
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('/obs/cart.php');
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$errors = [];
$formData = ['name' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side validation
    $name    = trim($_POST['customer_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $formData = ['name' => $name, 'address' => $address];

    if ($name === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    if ($address === '') {
        $errors['address'] = 'Delivery address is required.';
    } elseif (strlen($address) < 5) {
        $errors['address'] = 'Please enter a complete address.';
    }

    if (empty($errors)) {
        $db = getDB();
        try {
            $db->beginTransaction();

            // Insert order
            $stmt = $db->prepare("INSERT INTO orders (customer_name, address, total_price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $address, $total]);
            $orderId = $db->lastInsertId();

            // Insert order items
            $stmt2 = $db->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            foreach ($cart as $item) {
                $stmt2->execute([$orderId, $item['book_id'], $item['quantity'], $item['price']]);
            }

            $db->commit();

            // Store order in session for confirmation page, then clear cart
            $_SESSION['last_order'] = [
                'order_id'      => $orderId,
                'customer_name' => $name,
                'address'       => $address,
                'total'         => $total,
                'items'         => $cart,
            ];
            unset($_SESSION['cart']);

            redirect('/obs/order-confirmation.php');
        } catch (Exception $e) {
            $db->rollBack();
            $errors['db'] = 'Order could not be saved. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <p class="breadcrumb">
    <a href="/obs/index.php">Catalog</a> ›
    <a href="/obs/cart.php">Cart</a> ›
    <span>Checkout</span>
  </p>

  <h2 class="section-title">Checkout</h2>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:2.5rem;align-items:start;">
    <!-- Form -->
    <div class="form-card">
      <h3 style="font-family:var(--font-head);font-size:1.4rem;margin-bottom:1.5rem;">Delivery Details</h3>

      <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-error"><?= e($errors['db']) ?></div>
      <?php endif; ?>

      <form action="/obs/checkout.php" method="POST" id="checkoutForm" novalidate>
        <div class="form-group">
          <label for="customer_name">Full Name *</label>
          <input type="text" id="customer_name" name="customer_name"
                 class="form-control <?= isset($errors['name']) ? 'invalid' : '' ?>"
                 placeholder="e.g. Omaima Naseer"
                 value="<?= e($formData['name']) ?>" required>
          <div class="field-error <?= isset($errors['name']) ? 'visible' : '' ?>">
            <?= e($errors['name'] ?? '') ?>
          </div>
        </div>

        <div class="form-group">
          <label for="address">Delivery Address *</label>
          <textarea id="address" name="address" rows="4"
                    class="form-control <?= isset($errors['address']) ? 'invalid' : '' ?>"
                    placeholder="Street, City, Country…" required><?= e($formData['address']) ?></textarea>
          <div class="field-error <?= isset($errors['address']) ? 'visible' : '' ?>">
            <?= e($errors['address'] ?? '') ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:.8rem;">
          ✅ Confirm Order — $<?= number_format($total, 2) ?>
        </button>
      </form>
    </div>

    <!-- Order Summary -->
    <div style="background:var(--card-bg);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:1.5rem;">
      <h3 style="font-family:var(--font-head);font-size:1.2rem;margin-bottom:1.25rem;color:var(--ink);">Order Summary</h3>
      <?php foreach ($cart as $item): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:.75rem;font-size:.9rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;">
          <div>
            <strong><?= e($item['title']) ?></strong><br>
            <span style="color:var(--muted);">× <?= $item['quantity'] ?></span>
          </div>
          <div style="font-weight:600;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;color:var(--accent);margin-top:.5rem;">
        <span>Total</span>
        <span>$<?= number_format($total, 2) ?></span>
      </div>
    </div>
  </div>
</div>

<script>
// Client-side validation
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    let valid = true;

    const name    = document.getElementById('customer_name');
    const address = document.getElementById('address');
    const nameErr    = name.nextElementSibling;
    const addressErr = address.nextElementSibling;

    // Reset
    [name, address].forEach(el => el.classList.remove('invalid'));
    [nameErr, addressErr].forEach(el => { el.textContent = ''; el.classList.remove('visible'); });

    if (name.value.trim().length < 2) {
        name.classList.add('invalid');
        nameErr.textContent = 'Full name is required (min. 2 characters).';
        nameErr.classList.add('visible');
        valid = false;
    }

    if (address.value.trim().length < 5) {
        address.classList.add('invalid');
        addressErr.textContent = 'Please enter a complete delivery address.';
        addressErr.classList.add('visible');
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

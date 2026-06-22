<?php
// admin/orders.php — View All Orders
require_once __DIR__ . '/../config.php';
requireAdmin();

$db = getDB();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['pending', 'confirmed', 'cancelled'];
    $status  = $_POST['status'];
    if (in_array($status, $allowed, true)) {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, (int)$_POST['order_id']]);
    }
    redirect('/obs/admin/orders.php?updated=1');
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$total   = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pages   = ceil($total / $perPage);
$orders  = $db->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT $perPage OFFSET $offset")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders — BookHaven Admin</title>
  <link rel="stylesheet" href="/obs/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <span class="site-logo">📚 Book<span>Haven</span> Admin</span>
    <div style="display:flex;align-items:center;gap:1rem;">
      <span style="color:rgba(255,255,255,.6);font-size:.9rem;"><?= e($_SESSION['admin_username']) ?></span>
      <a href="/obs/admin/logout.php" class="btn btn-outline btn-sm" style="color:#fff;border-color:rgba(255,255,255,.3);">Logout</a>
    </div>
  </div>
</header>

<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-brand">🔧 Admin Panel</div>
    <nav>
      <a href="/obs/admin/index.php">📊 Dashboard</a>
      <a href="/obs/admin/books.php">📚 Manage Books</a>
      <a href="/obs/admin/orders.php" class="active">📦 View Orders</a>
      <a href="/obs/index.php" target="_blank">🌐 View Store</a>
      <a href="/obs/admin/logout.php">🚪 Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <h1>All Orders <small style="font-size:.8rem;color:var(--muted);font-family:var(--font-body);">(<?= $total ?>)</small></h1>

    <?php if (isset($_GET['updated'])): ?>
      <div class="alert alert-success">Order status updated.</div>
    <?php endif; ?>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Address</th>
          <th>Total</th>
          <th>Status</th>
          <th>Date</th>
          <th>Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
            <td><?= e($order['customer_name']) ?></td>
            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                title="<?= e($order['address']) ?>">
              <?= e($order['address']) ?>
            </td>
            <td>$<?= number_format($order['total_price'], 2) ?></td>
            <td>
              <?php
                $statusColors = [
                  'pending'   => ['#fff3cd','#856404'],
                  'confirmed' => ['#d4edda','#155724'],
                  'cancelled' => ['#f8d7da','#721c24'],
                ];
                [$bg,$fg] = $statusColors[$order['status']] ?? ['#eee','#333'];
              ?>
              <span style="padding:.2rem .6rem;border-radius:999px;font-size:.78rem;font-weight:600;background:<?= $bg ?>;color:<?= $fg ?>;">
                <?= ucfirst($order['status']) ?>
              </span>
            </td>
            <td><?= date('M j, Y H:i', strtotime($order['order_date'])) ?></td>
            <td>
              <form action="/obs/admin/orders.php" method="POST" style="display:flex;gap:.4rem;align-items:center;">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status" class="form-control" style="padding:.3rem .6rem;font-size:.82rem;width:120px;">
                  <?php foreach (['pending','confirmed','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <div style="display:flex;gap:.5rem;margin-top:1.25rem;">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

</body>
</html>

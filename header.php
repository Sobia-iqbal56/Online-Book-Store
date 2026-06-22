<?php
// partials/header.php
// Requires: $pageTitle (string), config.php already included
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'BookHaven') ?> — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="/obs/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <a href="/obs/index.php" class="site-logo">📚 Book<span>Haven</span></a>

    <form class="search-form" action="/obs/index.php" method="GET">
      <input type="text" name="q" placeholder="Search books or authors…"
             value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit">🔍</button>
    </form>

    <ul class="nav-links">
      <li><a href="/obs/index.php">Catalog</a></li>
      <li>
        <a href="/obs/cart.php" class="nav-cart">
          🛒 Cart <span class="cart-count"><?= $cartCount ?></span>
        </a>
      </li>
    </ul>
  </div>
</header>

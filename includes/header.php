<?php
$pageTitle = $pageTitle ?? 'Склад и торговля';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/po_alina/assets/css/style.css">
</head>
<body>
<?php if (currentUser()): ?>
<header class="main-header">
    <div class="header-inner">
        <a href="/po_alina/index.php" class="logo"><i class="fa-solid fa-warehouse"></i> Склад и торговля</a>
        <nav class="main-nav">
            <a href="/po_alina/index.php"><i class="fa-solid fa-gauge-high"></i> Главная</a>
            <a href="/po_alina/products.php"><i class="fa-solid fa-boxes-stacked"></i> Товары</a>
            <a href="/po_alina/movements.php"><i class="fa-solid fa-arrows-left-right"></i> Движения</a>
            <a href="/po_alina/orders.php"><i class="fa-solid fa-cart-shopping"></i> Заказы</a>
            <a href="/po_alina/clients.php"><i class="fa-solid fa-users"></i> Клиенты</a>
            <?php if (isAdmin()): ?>
            <a href="/po_alina/categories.php"><i class="fa-solid fa-tags"></i> Категории</a>
            <?php endif; ?>
        </nav>
        <div class="header-user">
            <span class="user-name"><i class="fa-solid fa-user"></i> <?= htmlspecialchars(currentUser()['full_name']) ?></span>
            <a href="/po_alina/logout.php" class="btn btn-outline"><i class="fa-solid fa-right-from-bracket"></i> Выход</a>
        </div>
    </div>
</header>
<?php endif; ?>

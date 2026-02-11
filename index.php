<?php
require_once __DIR__ . '/includes/init.php';
if (!currentUser()) {
    header('Location: /po_alina/login.php');
    exit;
}

$pdo = getDb();

$stats = [
    'products' => $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders_today' => $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetchColumn(),
    'orders_month' => $pdo->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status IN ('completed','shipped','confirmed')")->fetchColumn(),
    'revenue_month' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ('completed','shipped') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'low_stock' => $pdo->query('SELECT COUNT(*) FROM products WHERE min_quantity > 0 AND quantity <= min_quantity')->fetchColumn(),
];

$recentOrders = $pdo->query("
    SELECT o.id, o.status, o.total, o.created_at, c.name AS client_name
    FROM orders o
    LEFT JOIN clients c ON c.id = o.client_id
    WHERE o.status != 'cancelled'
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$lowStockProducts = $pdo->query("
    SELECT id, name, sku, quantity, min_quantity, unit
    FROM products
    WHERE min_quantity > 0 AND quantity <= min_quantity
    ORDER BY quantity ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Главная';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-gauge-high"></i> Главная</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="value"><?= (int) $stats['products'] ?></div>
        <div class="label"><i class="fa-solid fa-boxes-stacked"></i> Товаров на складе</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) $stats['orders_today'] ?></div>
        <div class="label"><i class="fa-solid fa-cart-shopping"></i> Заказов сегодня</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) $stats['orders_month'] ?></div>
        <div class="label"><i class="fa-solid fa-calendar-days"></i> Заказов за месяц</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= number_format($stats['revenue_month'], 0, '.', ' ') ?> <span class="text-muted" style="font-size:14px">руб.</span></div>
        <div class="label"><i class="fa-solid fa-ruble-sign"></i> Выручка за месяц</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) $stats['low_stock'] ?></div>
        <div class="label"><i class="fa-solid fa-triangle-exclamation"></i> Товаров ниже минимума</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Последние заказы</h2>
        <a href="/po_alina/orders.php" class="btn btn-outline btn-sm">Все заказы</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Клиент</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><?= (int) $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['client_name'] ?: '—') ?></td>
                    <td><?= number_format($o['total'], 0, '.', ' ') ?> руб.</td>
                    <td>
                        <?php
                        $statusClass = ['draft' => 'badge-secondary', 'confirmed' => 'badge-info', 'shipped' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'];
                        $statusLabel = ['draft' => 'Черновик', 'confirmed' => 'Подтвержден', 'shipped' => 'Отправлен', 'completed' => 'Выполнен', 'cancelled' => 'Отменен'];
                        ?>
                        <span class="badge <?= $statusClass[$o['status']] ?? 'badge-secondary' ?>"><?= $statusLabel[$o['status']] ?? $o['status'] ?></span>
                    </td>
                    <td class="text-muted"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                    <td><a href="/po_alina/order_view.php?id=<?= (int) $o['id'] ?>" class="btn btn-outline btn-sm">Открыть</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="6" class="text-muted">Нет заказов</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-triangle-exclamation"></i> Товары с низким остатком</h2>
        <a href="/po_alina/products.php" class="btn btn-outline btn-sm">Все товары</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Артикул</th>
                    <th>Наименование</th>
                    <th>Остаток</th>
                    <th>Минимум</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['sku']) ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td class="low-stock"><?= (int) $p['quantity'] ?> <?= htmlspecialchars($p['unit']) ?></td>
                    <td><?= (int) $p['min_quantity'] ?> <?= htmlspecialchars($p['unit']) ?></td>
                    <td><a href="/po_alina/product_edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Изменить</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($lowStockProducts)): ?>
                <tr><td colspan="5" class="text-muted">Все остатки в норме</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

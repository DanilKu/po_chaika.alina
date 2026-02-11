<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /po_alina/orders.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("SELECT o.*, c.name AS client_name, c.phone, c.email, u.full_name AS user_name
    FROM orders o
    LEFT JOIN clients c ON c.id = o.client_id
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    header('Location: /po_alina/orders.php');
    exit;
}

$items = $pdo->prepare('SELECT oi.*, p.name AS product_name, p.sku FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$items->execute([$id]);
$items = $items->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = ['draft' => 'Черновик', 'confirmed' => 'Подтвержден', 'shipped' => 'Отправлен', 'completed' => 'Выполнен', 'cancelled' => 'Отменен'];
$statusClass = ['draft' => 'badge-secondary', 'confirmed' => 'badge-info', 'shipped' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'];

$pageTitle = 'Заказ №' . $id;
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-file-lines"></i> Заказ № <?= (int) $order['id'] ?></h1>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <h2>Информация о заказе</h2>
        <span class="badge <?= $statusClass[$order['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$order['status']] ?? $order['status'] ?></span>
        <?php if ($order['status'] === 'draft'): ?>
        <a href="/po_alina/order_edit.php?id=<?= (int) $order['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Редактировать</a>
        <form method="post" action="order_status.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="status" value="confirmed">
            <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Подтвердить заказ</button>
        </form>
        <?php elseif ($order['status'] === 'confirmed'): ?>
        <form method="post" action="order_status.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="status" value="shipped">
            <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-truck"></i> Отправлен</button>
        </form>
        <?php endif; ?>
        <?php if (in_array($order['status'], ['confirmed', 'shipped'])): ?>
        <form method="post" action="order_status.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="status" value="completed">
            <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-flag-checkered"></i> Выполнен</button>
        </form>
        <?php endif; ?>
        <?php if ($order['status'] === 'draft'): ?>
        <form method="post" action="order_status.php" style="display:inline;" onsubmit="return confirm('Отменить заказ?');">
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-times"></i> Отменить</button>
        </form>
        <?php endif; ?>
    </div>
    <div style="padding: 20px;">
        <p><strong>Клиент:</strong> <?= htmlspecialchars($order['client_name'] ?: '—') ?></p>
        <?php if ($order['phone']): ?>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></p>
        <?php endif; ?>
        <?php if ($order['email']): ?>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <?php endif; ?>
        <p><strong>Менеджер:</strong> <?= htmlspecialchars($order['user_name']) ?></p>
        <p><strong>Дата создания:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
        <?php if ($order['comment']): ?>
        <p><strong>Комментарий:</strong> <?= htmlspecialchars($order['comment']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Позиции заказа</h2></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Кол-во</th>
                    <th>Цена</th>
                    <th class="text-right">Сумма</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['sku']) ?> — <?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2, '.', ' ') ?> руб.</td>
                    <td class="text-right"><?= number_format($item['quantity'] * $item['price'], 2, '.', ' ') ?> руб.</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Итого:</strong></td>
                    <td class="text-right"><strong><?= number_format($order['total'], 2, '.', ' ') ?> руб.</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p><a href="/po_alina/orders.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> К списку заказов</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

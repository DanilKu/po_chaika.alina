<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT o.id, o.status, o.total, o.created_at, c.name AS client_name
        FROM orders o
        LEFT JOIN clients c ON c.id = o.client_id
        WHERE 1=1";
$params = [];
if ($statusFilter !== '') {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}
if ($search !== '') {
    if (is_numeric($search)) {
        $sql .= ' AND o.id = ?';
        $params[] = (int) $search;
    } else {
        $sql .= ' AND c.name LIKE ?';
        $params[] = '%' . $search . '%';
    }
}
$sql .= ' ORDER BY o.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = ['draft' => 'Черновик', 'confirmed' => 'Подтвержден', 'shipped' => 'Отправлен', 'completed' => 'Выполнен', 'cancelled' => 'Отменен'];

$pageTitle = 'Заказы';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-cart-shopping"></i> Заказы</h1>

<div class="card">
    <div class="card-header">
        <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="№ заказа или клиент" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px; min-width:180px;">
            <select name="status" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px;">
                <option value="">Все статусы</option>
                <?php foreach ($statusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Найти</button>
        </form>
        <a href="/po_alina/order_edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Новый заказ</a>
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
                <?php
                $statusClass = ['draft' => 'badge-secondary', 'confirmed' => 'badge-info', 'shipped' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'];
                foreach ($orders as $o):
                ?>
                <tr>
                    <td><?= (int) $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['client_name'] ?: '—') ?></td>
                    <td><?= number_format($o['total'], 0, '.', ' ') ?> руб.</td>
                    <td><span class="badge <?= $statusClass[$o['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$o['status']] ?? $o['status'] ?></span></td>
                    <td class="text-muted"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                    <td>
                        <a href="/po_alina/order_view.php?id=<?= (int) $o['id'] ?>" class="btn btn-outline btn-sm">Просмотр</a>
                        <?php if ($o['status'] === 'draft'): ?>
                        <a href="/po_alina/order_edit.php?id=<?= (int) $o['id'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="text-muted">Заказов не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

$productFilter = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;
$typeFilter = trim($_GET['type'] ?? '');

$sql = 'SELECT m.*, p.name AS product_name, p.sku, u.full_name AS user_name
        FROM stock_movements m
        JOIN products p ON p.id = m.product_id
        JOIN users u ON u.id = m.user_id
        WHERE 1=1';
$countSql = 'SELECT COUNT(*) FROM stock_movements m WHERE 1=1';
$params = [];
if ($productFilter) {
    $sql .= ' AND m.product_id = ?';
    $countSql .= ' AND m.product_id = ?';
    $params[] = $productFilter;
}
if ($typeFilter !== '') {
    $sql .= ' AND m.type = ?';
    $countSql .= ' AND m.type = ?';
    $params[] = $typeFilter;
}
$total = $pdo->prepare($countSql);
$total->execute($params);
$total = (int) $total->fetchColumn();

$sql .= ' ORDER BY m.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$typeLabels = ['in' => 'Приход', 'out' => 'Расход', 'adjust' => 'Корректировка', 'sale' => 'Продажа', 'return' => 'Возврат'];
$products = $pdo->query('SELECT id, name, sku FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Движения по складу';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-arrows-left-right"></i> Движения по складу</h1>

<div class="card">
    <div class="card-header">
        <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <select name="product_id" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px; min-width:200px;">
                <option value="">Все товары</option>
                <?php foreach ($products as $pr): ?>
                    <option value="<?= (int) $pr['id'] ?>" <?= $productFilter === (int) $pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['sku'] . ' — ' . $pr['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px;">
                <option value="">Все типы</option>
                <?php foreach ($typeLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $typeFilter === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Найти</button>
        </form>
        <a href="/po_alina/movement_add.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Новое движение</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Товар</th>
                    <th>Тип</th>
                    <th>Кол-во</th>
                    <th>Остаток после</th>
                    <th>Пользователь</th>
                    <th>Комментарий</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $m): ?>
                <tr>
                    <td class="text-muted"><?= date('d.m.Y H:i', strtotime($m['created_at'])) ?></td>
                    <td><?= htmlspecialchars($m['sku']) ?> — <?= htmlspecialchars($m['product_name']) ?></td>
                    <td><span class="badge badge-info"><?= $typeLabels[$m['type']] ?? $m['type'] ?></span></td>
                    <td><?= $m['quantity'] > 0 ? '+' : '' ?><?= $m['quantity'] ?></td>
                    <td><?= (int) $m['balance_after'] ?></td>
                    <td class="text-muted"><?= htmlspecialchars($m['user_name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($m['comment'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movements)): ?>
                <tr><td colspan="7" class="text-muted">Движений не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total > $perPage): ?>
    <div style="padding:12px 20px; border-top:1px solid var(--border);" class="text-muted">
        Страница <?= $page ?> из <?= ceil($total / $perPage) ?>. Всего записей: <?= $total ?>.
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

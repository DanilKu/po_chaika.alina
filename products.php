<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$categoryFilter = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$search = trim($_GET['q'] ?? '');

$sql = 'SELECT p.id, p.name, p.sku, p.unit, p.price_buy, p.price_sell, p.quantity, p.min_quantity, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE 1=1';
$params = [];
if ($categoryFilter) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryFilter;
}
if ($search !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY p.name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Товары';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-boxes-stacked"></i> Товары</h1>

<div class="card">
    <div class="card-header">
        <form method="get" class="form-inline" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по названию или артикулу" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px; min-width:220px;">
            <select name="category_id" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px;">
                <option value="">Все категории</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $categoryFilter === (int) $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Найти</button>
        </form>
        <a href="/po_alina/product_edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Добавить товар</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Артикул</th>
                    <th>Наименование</th>
                    <th>Категория</th>
                    <th>Ед.</th>
                    <th>Цена закупки</th>
                    <th>Цена продажи</th>
                    <th>Остаток</th>
                    <th>Мин.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['sku']) ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($p['category_name'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($p['unit']) ?></td>
                    <td><?= number_format($p['price_buy'], 2, '.', ' ') ?></td>
                    <td><?= number_format($p['price_sell'], 2, '.', ' ') ?></td>
                    <td class="<?= $p['min_quantity'] > 0 && $p['quantity'] <= $p['min_quantity'] ? 'low-stock' : '' ?>"><?= (int) $p['quantity'] ?></td>
                    <td><?= (int) $p['min_quantity'] ?></td>
                    <td class="table-actions">
                        <a href="/po_alina/movement_add.php?product_id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm" title="Движение"><i class="fa-solid fa-arrows-left-right"></i></a>
                        <a href="/po_alina/product_edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr><td colspan="9" class="text-muted">Товаров не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        header('Location: /po_alina/products.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
    $unit = trim($_POST['unit'] ?? 'шт');
    $price_buy = (float) str_replace(',', '.', $_POST['price_buy'] ?? '0');
    $price_sell = (float) str_replace(',', '.', $_POST['price_sell'] ?? '0');
    $min_quantity = (int) ($_POST['min_quantity'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);

    if (!$name || !$sku) {
        $error = 'Заполните название и артикул.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = ? AND id != ?');
            $stmt->execute([$sku, $id]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = ?');
            $stmt->execute([$sku]);
        }
        if ($stmt->fetch()) {
            $error = 'Артикул уже существует.';
        } else {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE products SET name=?, category_id=?, sku=?, unit=?, price_buy=?, price_sell=?, min_quantity=?, quantity=? WHERE id=?');
                $stmt->execute([$name, $category_id ?: null, $sku, $unit, $price_buy, $price_sell, $min_quantity, $quantity, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (name, category_id, sku, unit, price_buy, price_sell, min_quantity, quantity) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([$name, $category_id ?: null, $sku, $unit, $price_buy, $price_sell, $min_quantity, $quantity]);
            }
            header('Location: /po_alina/products.php');
            exit;
        }
    }
    $_POST['quantity'] = $quantity;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = $product ? 'Редактирование товара' : 'Новый товар';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-box"></i> <?= $product ? 'Редактирование товара' : 'Новый товар' ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Данные товара</h2></div>
    <div style="padding: 20px;">
        <form method="post">
            <div class="form-group">
                <label>Наименование *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? $_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Артикул (SKU) *</label>
                <input type="text" name="sku" value="<?= htmlspecialchars($product['sku'] ?? $_POST['sku'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <option value="">—</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= ($product['category_id'] ?? $_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Единица измерения</label>
                <input type="text" name="unit" value="<?= htmlspecialchars($product['unit'] ?? $_POST['unit'] ?? 'шт') ?>">
            </div>
            <div class="form-group">
                <label>Цена закупки (руб.)</label>
                <input type="text" name="price_buy" value="<?= htmlspecialchars($product['price_buy'] ?? $_POST['price_buy'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label>Цена продажи (руб.)</label>
                <input type="text" name="price_sell" value="<?= htmlspecialchars($product['price_sell'] ?? $_POST['price_sell'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label>Остаток на складе</label>
                <input type="number" name="quantity" value="<?= (int) ($product['quantity'] ?? $_POST['quantity'] ?? 0) ?>" min="0">
            </div>
            <div class="form-group">
                <label>Минимальный остаток (оповещение)</label>
                <input type="number" name="min_quantity" value="<?= (int) ($product['min_quantity'] ?? $_POST['min_quantity'] ?? 0) ?>" min="0">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Сохранить</button>
                <a href="/po_alina/products.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;
$product = null;
if ($productId) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    $validTypes = ['in', 'out', 'adjust', 'sale', 'return'];
    if (!$product_id || !in_array($type, $validTypes)) {
        $error = 'Укажите товар и тип движения.';
    } elseif ($quantity === 0 && $type !== 'adjust') {
        $error = 'Укажите ненулевое количество.';
    } elseif ($type === 'adjust' && $quantity === 0) {
        $error = 'Укажите величину корректировки (положительную или отрицательную).';
    } else {
        $stmt = $pdo->prepare('SELECT quantity FROM products WHERE id = ?');
        $stmt->execute([$product_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $error = 'Товар не найден.';
        } else {
            $current = (int) $row['quantity'];
            $delta = $quantity;
            if (in_array($type, ['out', 'sale'])) {
                $delta = -$quantity;
            }
            if ($type === 'adjust') {
                $delta = $quantity;
            }
            $newBalance = $current + $delta;
            if ($newBalance < 0) {
                $error = 'Недостаточно товара на складе. Текущий остаток: ' . $current . '.';
            } else {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?');
                    $stmt->execute([$delta, $product_id]);
                    $stmt = $pdo->prepare('INSERT INTO stock_movements (product_id, type, quantity, balance_after, user_id, comment) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$product_id, $type, $delta, $newBalance, currentUser()['id'], $comment ?: null]);
                    $pdo->commit();
                    header('Location: /po_alina/movements.php');
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Ошибка сохранения.';
                }
            }
        }
    }
}

$products = $pdo->query('SELECT id, name, sku, quantity, unit FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$typeLabels = ['in' => 'Приход', 'out' => 'Расход', 'adjust' => 'Корректировка', 'sale' => 'Продажа', 'return' => 'Возврат'];

$pageTitle = 'Новое движение';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-arrows-left-right"></i> Новое движение по складу</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Данные движения</h2></div>
    <div style="padding: 20px;">
        <form method="post">
            <div class="form-group">
                <label>Товар *</label>
                <select name="product_id" required id="product_select">
                    <option value="">Выберите товар</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" data-qty="<?= (int) $p['quantity'] ?>" data-unit="<?= htmlspecialchars($p['unit']) ?>"
                            <?= ($product && (int) $product['id'] === (int) $p['id']) || (int)($_POST['product_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['sku'] . ' — ' . $p['name']) ?> (остаток: <?= (int) $p['quantity'] ?> <?= $p['unit'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Тип движения *</label>
                <select name="type" required>
                    <?php foreach ($typeLabels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($_POST['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Количество * (для прихода — положительное; для расхода/продажи — положительное, списание автоматически; для корректировки — + или −)</label>
                <input type="number" name="quantity" value="<?= (int) ($_POST['quantity'] ?? 1) ?>" step="1" id="movement_qty" required>
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <input type="text" name="comment" value="<?= htmlspecialchars($_POST['comment'] ?? '') ?>" placeholder="Причина, номер накладной и т.д.">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Сохранить</button>
                <a href="/po_alina/movements.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

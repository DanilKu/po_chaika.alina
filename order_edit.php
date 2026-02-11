<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$order = null;
$items = [];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order || $order['status'] !== 'draft') {
        header('Location: /po_alina/orders.php');
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = !empty($_POST['client_id']) ? (int) $_POST['client_id'] : null;
    $comment = trim($_POST['comment'] ?? '');
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    $newItems = [];
    foreach ($product_ids as $i => $pid) {
        $pid = (int) $pid;
        $qty = (int) ($quantities[$i] ?? 0);
        $price = (float) str_replace(',', '.', $prices[$i] ?? '0');
        if ($pid && $qty > 0) {
            $newItems[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
        }
    }

    if ($id) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE orders SET client_id = ?, comment = ? WHERE id = ?');
            $stmt->execute([$client_id, $comment ?: null, $id]);
            $pdo->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$id]);
            $total = 0;
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($newItems as $it) {
                $ins->execute([$id, $it['product_id'], $it['quantity'], $it['price']]);
                $total += $it['quantity'] * $it['price'];
            }
            $pdo->prepare('UPDATE orders SET total = ? WHERE id = ?')->execute([$total, $id]);
            $pdo->commit();
            header('Location: /po_alina/order_view.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка сохранения.';
        }
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO orders (client_id, user_id, status, comment) VALUES (?, ?, "draft", ?)');
            $stmt->execute([$client_id, currentUser()['id'], $comment ?: null]);
            $orderId = (int) $pdo->lastInsertId();
            $total = 0;
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($newItems as $it) {
                $ins->execute([$orderId, $it['product_id'], $it['quantity'], $it['price']]);
                $total += $it['quantity'] * $it['price'];
            }
            $pdo->prepare('UPDATE orders SET total = ? WHERE id = ?')->execute([$total, $orderId]);
            $pdo->commit();
            header('Location: /po_alina/order_view.php?id=' . $orderId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка сохранения.';
        }
    }
}

$clients = $pdo->query('SELECT id, name FROM clients ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query('SELECT id, name, sku, price_sell, quantity, unit FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

if (empty($items) && !$id) {
    $items = [['product_id' => '', 'quantity' => 1, 'price' => '']];
}

$pageTitle = $order ? 'Редактирование заказа' : 'Новый заказ';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-cart-plus"></i> <?= $order ? 'Редактирование заказа' : 'Новый заказ' ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Данные заказа</h2></div>
    <div style="padding: 20px;">
        <form method="post" id="orderForm">
            <div class="form-group">
                <label>Клиент</label>
                <select name="client_id">
                    <option value="">— Без клиента —</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= ($order['client_id'] ?? $_POST['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <input type="text" name="comment" value="<?= htmlspecialchars($order['comment'] ?? $_POST['comment'] ?? '') ?>">
            </div>

            <h3 style="margin: 24px 0 12px;">Позиции заказа</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th style="width:100px">Кол-во</th>
                            <th style="width:120px">Цена</th>
                            <th style="width:80px"></th>
                        </tr>
                    </thead>
                    <tbody id="orderItems">
                        <?php foreach ($items as $i => $it): ?>
                        <tr class="order-row">
                            <td>
                                <select name="product_id[]" class="product-select" data-index="<?= $i ?>">
                                    <option value="">Выберите товар</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= (int) $p['id'] ?>" data-price="<?= $p['price_sell'] ?>"
                                            <?= (int)($it['product_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['sku'] . ' — ' . $p['name']) ?> (<?= $p['price_sell'] ?> руб.)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" value="<?= (int)($it['quantity'] ?? 1) ?>" min="1" style="width:80px; padding:8px;"></td>
                            <td><input type="text" name="price[]" value="<?= htmlspecialchars($it['price'] ?? '') ?>" class="price-input" style="width:100px; padding:8px;"></td>
                            <td><button type="button" class="btn btn-outline btn-sm remove-row"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="mt-2"><button type="button" id="addRow" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Добавить позицию</button></p>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Сохранить заказ</button>
                <a href="<?= $id ? '/po_alina/order_view.php?id=' . $id : '/po_alina/orders.php' ?>" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var products = <?= json_encode(array_map(function($p) { return ['id' => (int)$p['id'], 'price_sell' => $p['price_sell'], 'label' => $p['sku'] . ' — ' . $p['name']]; }, $products)) ?>;
    var tbody = document.getElementById('orderItems');

    function addRow() {
        var tr = document.createElement('tr');
        tr.className = 'order-row';
        var opts = '<option value="">Выберите товар</option>';
        products.forEach(function(p) {
            opts += '<option value="' + p.id + '" data-price="' + p.price_sell + '">' + escapeHtml(p.label) + ' (' + p.price_sell + ' руб.)</option>';
        });
        tr.innerHTML = '<td><select name="product_id[]" class="product-select">' + opts + '</select></td>' +
            '<td><input type="number" name="quantity[]" value="1" min="1" style="width:80px; padding:8px;"></td>' +
            '<td><input type="text" name="price[]" class="price-input" style="width:100px; padding:8px;"></td>' +
            '<td><button type="button" class="btn btn-outline btn-sm remove-row"><i class="fa-solid fa-trash"></i></button></td>';
        tbody.appendChild(tr);
        tr.querySelector('.product-select').addEventListener('change', onProductChange);
        tr.querySelector('.remove-row').addEventListener('click', function() { tr.remove(); });
    }

    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function onProductChange(e) {
        var sel = e.target;
        var opt = sel.options[sel.selectedIndex];
        var price = opt && opt.dataset.price ? opt.dataset.price : '';
        var row = sel.closest('tr');
        row.querySelector('.price-input').value = price;
    }

    document.getElementById('addRow').addEventListener('click', addRow);
    document.querySelectorAll('.product-select').forEach(function(sel) {
        sel.addEventListener('change', onProductChange);
    });
    document.querySelectorAll('.remove-row').forEach(function(btn) {
        btn.addEventListener('click', function() { btn.closest('tr').remove(); });
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

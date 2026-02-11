<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();
if (!isAdmin()) {
    header('Location: /po_alina/index.php');
    exit;
}

$pdo = getDb();
$categories = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS products_count FROM categories c ORDER BY c.name')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Категории';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-tags"></i> Категории товаров</h1>

<div class="card">
    <div class="card-header">
        <h2>Список категорий</h2>
        <a href="/po_alina/category_edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Добавить категорию</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Товаров</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['description'] ?: '—') ?></td>
                    <td><?= (int) $c['products_count'] ?></td>
                    <td><a href="/po_alina/category_edit.php?id=<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="text-muted">Категорий нет</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

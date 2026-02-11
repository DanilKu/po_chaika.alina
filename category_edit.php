<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();
if (!isAdmin()) {
    header('Location: /po_alina/index.php');
    exit;
}

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$category = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        header('Location: /po_alina/categories.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$name) {
        $error = 'Укажите название.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
            $stmt->execute([$name, $description ?: null, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?,?)');
            $stmt->execute([$name, $description ?: null]);
        }
        header('Location: /po_alina/categories.php');
        exit;
    }
}

$pageTitle = $category ? 'Редактирование категории' : 'Новая категория';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-tag"></i> <?= $category ? 'Редактирование категории' : 'Новая категория' ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Данные категории</h2></div>
    <div style="padding: 20px;">
        <form method="post">
            <div class="form-group">
                <label>Название *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? $_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Описание</label>
                <input type="text" name="description" value="<?= htmlspecialchars($category['description'] ?? $_POST['description'] ?? '') ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Сохранить</button>
                <a href="/po_alina/categories.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

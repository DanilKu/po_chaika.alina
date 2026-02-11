<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$client = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
    $stmt->execute([$id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) {
        header('Location: /po_alina/clients.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if (!$name) {
        $error = 'Укажите наименование.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE clients SET name=?, phone=?, email=?, address=? WHERE id=?');
            $stmt->execute([$name, $phone ?: null, $email ?: null, $address ?: null, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO clients (name, phone, email, address) VALUES (?,?,?,?)');
            $stmt->execute([$name, $phone ?: null, $email ?: null, $address ?: null]);
        }
        header('Location: /po_alina/clients.php');
        exit;
    }
}

$pageTitle = $client ? 'Редактирование клиента' : 'Новый клиент';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-user"></i> <?= $client ? 'Редактирование клиента' : 'Новый клиент' ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Данные клиента</h2></div>
    <div style="padding: 20px;">
        <form method="post">
            <div class="form-group">
                <label>Наименование / ФИО *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($client['name'] ?? $_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($client['phone'] ?? $_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($client['email'] ?? $_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Адрес</label>
                <input type="text" name="address" value="<?= htmlspecialchars($client['address'] ?? $_POST['address'] ?? '') ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Сохранить</button>
                <a href="/po_alina/clients.php" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

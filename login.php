<?php
require_once __DIR__ . '/includes/init.php';

if (currentUser()) {
    header('Location: /po_alina/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$login || !$password) {
        $error = 'Введите логин и пароль.';
    } else {
        $pdo = getDb();
        $stmt = $pdo->prepare('SELECT id, login, password_hash, full_name, email, role FROM users WHERE login = ?');
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            header('Location: /po_alina/index.php');
            exit;
        }
        $error = 'Неверный логин или пароль.';
    }
}

$pageTitle = 'Вход';
require_once __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1><i class="fa-solid fa-warehouse"></i> Склад и торговля</h1>
        <p class="auth-subtitle">Вход в систему</p>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="auth-form">
            <label>
                <span>Логин</span>
                <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required autofocus>
            </label>
            <label>
                <span>Пароль</span>
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Войти</button>
        </form>
        <p class="auth-footer">Нет аккаунта? <a href="register.php">Регистрация</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

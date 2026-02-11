<?php
require_once __DIR__ . '/includes/init.php';

if (currentUser()) {
    header('Location: /po_alina/index.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$login || !$password || !$full_name || !$email) {
        $error = 'Заполните все обязательные поля.';
    } elseif (strlen($login) < 3) {
        $error = 'Логин не менее 3 символов.';
    } elseif ($password !== $password2) {
        $error = 'Пароли не совпадают.';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль не менее 6 символов.';
    } else {
        $pdo = getDb();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Такой логин уже занят.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, full_name, email, role) VALUES (?, ?, ?, ?, "user")');
            $stmt->execute([$login, $hash, $full_name, $email]);
            $success = 'Регистрация успешна. Теперь можно войти.';
            $_POST = [];
        }
    }
}

$pageTitle = 'Регистрация';
require_once __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1><i class="fa-solid fa-user-plus"></i> Регистрация</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="auth-form">
            <label>
                <span>Логин</span>
                <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
            </label>
            <label>
                <span>Пароль</span>
                <input type="password" name="password" required>
            </label>
            <label>
                <span>Пароль еще раз</span>
                <input type="password" name="password2" required>
            </label>
            <label>
                <span>ФИО</span>
                <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </label>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Зарегистрироваться</button>
        </form>
        <p class="auth-footer">Уже есть аккаунт? <a href="login.php">Вход</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

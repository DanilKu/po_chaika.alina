<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pdo = getDb();
$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM clients WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Клиенты';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><i class="fa-solid fa-users"></i> Клиенты</h1>

<div class="card">
    <div class="card-header">
        <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по имени, телефону, email" style="padding:8px 12px; border:1px solid var(--border); border-radius:6px; min-width:240px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Найти</button>
        </form>
        <a href="/po_alina/client_edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Добавить клиента</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Адрес</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['email'] ?: '—') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['address'] ?: '—') ?></td>
                    <td><a href="/po_alina/client_edit.php?id=<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr><td colspan="5" class="text-muted">Клиентов не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

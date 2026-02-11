<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: /po_alina/login.php');
        exit;
    }
}

function isAdmin(): bool {
    $u = currentUser();
    return $u && ($u['role'] ?? '') === 'admin';
}

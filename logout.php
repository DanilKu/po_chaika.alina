<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /po_alina/login.php');
exit;

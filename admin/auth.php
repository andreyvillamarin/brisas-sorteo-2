<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'login') {
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, password_hash FROM administradores WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $usuario;
            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: index.php?error=1');
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function isJastiper() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'JASTIPER';
}

function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'CUSTOMER';
}

function requireJastiper() {
    requireLogin();
    if (!isJastiper()) {
        header('Location: dashboard.php');
        exit();
    }
}

function requireCustomer() {
    requireLogin();
    if (!isCustomer()) {
        header('Location: dashboard.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

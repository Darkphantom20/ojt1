<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function generate_csrf_token(){
    return $_SESSION['csrf_token'] ?? ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));
}
function validate_csrf_token(string $token): bool{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
function get_csrf_token(){
    return $_SESSION['csrf_token'] ?? null;
}
function regenerate_csrf_token(){
    return $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
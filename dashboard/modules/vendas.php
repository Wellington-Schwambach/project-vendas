<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit;
}
?>
<?php include '../../vendas/index.php'; ?>

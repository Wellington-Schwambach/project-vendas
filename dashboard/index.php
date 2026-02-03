<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Project Dashboard</title>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/styleDashboard.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            <img src="../img/logo_zucchetti.jpg" width="100%" height="40">
        </div>

        <div class="nav-menu">
            <a href="#" class="nav-item active" data-module="home">
                <i class="fas fa-home"></i><span>Home</span>
            </a>

            <a href="#" class="nav-item" data-module="clientes">
                <i class="fas fa-user-circle"></i><span>Cadastro de Cliente</span>
            </a>

            <a href="#" class="nav-item" data-module="produtos">
                <i class="fas fa-sliders-h"></i><span>Cadastro de Produtos</span>
            </a>

            <a href="#" class="nav-item" data-module="pagamentos">
                <i class="fas fa-wallet"></i><span>Formas de Pagamento</span>
            </a>

            <a href="#" class="nav-item" data-module="vendas">
                <i class="fas fa-comment-dots"></i><span>Vendas</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1 class="welcome-title" id="tituloModulo">Home</h1>
        </div>

        <div class="dashboard-container" id="conteudoModulo">
            <!-- JS carrega aqui -->
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>

</html>
    <link rel="stylesheet" href="../assets/styleCadastro.css" />
    <script src="../cadastro/pagamentos.js"></script>

    <div class="dashboard-main">
        <div class="card-form">
            <div class="card">

                <h2 class="section-title">Pagamentos</h2>

                <!-- Abas -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="cadastroPagamento">Cadastro</button>
                    <button class="tab-btn" data-tab="listaPagamento">Listagem</button>
                </div>

                <!-- Conteúdo: Cadastro -->
                <div class="tab-content active" id="cadastroPagamento">
                    <form id="formPagamento" class="form-grid">
                        <input type="hidden" id="idPagamento">
                        <div class="form-group full">
                            <label>Forma de Pagamento: *</label>
                            <input type="text" id="txtPagamento" required>

                            <label>Quantidade de Parcelas: *</label>
                            <input type="number" id="txtQtdParcelas" min="1" max="12" required>
                        </div>
                        <div class="form-actions full">
                            <button type="submit" class="btn-primary">
                                Cadastrar Forma de Pagamento
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Conteúdo: Listagem -->
                <div class="tab-content" id="listaPagamento">
                    <?php include 'listagem.php'; ?>
                </div>
            </div>
        </div>
    </div>
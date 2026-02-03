    <link rel="stylesheet" href="../assets/styleCadastro.css" />
    <script src="../cadastro/produtos.js"></script>

    <div class="dashboard-main">
        <div class="card-form">
            <div class="card">

                <h2 class="section-title">Produtos</h2>

                <!-- Abas -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="cadastroProd">Cadastro</button>
                    <button class="tab-btn" data-tab="listaProd">Listagem</button>
                </div>

                <!-- Conteúdo: Cadastro -->
                <div class="tab-content active" id="cadastroProd">
                    <form id="formProduto" class="form-grid">
                        <input type="hidden" id="idProduto">
                        <div class="form-group full">
                            <label>Nome do Produto: *</label>
                            <input type="text" id="txtProduto" required>
                        </div>

                        <div class="form-group">
                            <label>Quantidade: *</label>
                            <input type="number" id="txtQuantidadeProduto" required>
                        </div>

                        <div class="form-group">
                            <label>Valor do Produto: *</label>
                            <input type="text" id="txtValorProduto" required>
                        </div>
                        <div class="form-actions full">
                            <button type="submit" class="btn-primary">
                                Cadastrar Produto
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Conteúdo: Listagem -->
                <div class="tab-content" id="listaProd">
                    <?php include 'listagem.php'; ?>
                </div>
            </div>
        </div>
    </div>
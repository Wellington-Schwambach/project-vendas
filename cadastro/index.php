    <link rel="stylesheet" href="../assets/styleCadastro.css" />
    <script src="../cadastro/cliente.js"></script>

    <div class="dashboard-main">
        <div class="card-form">
            <div class="card">

                <h2 class="section-title">Clientes</h2>

                <!-- Abas -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="cadastroCli">Cadastro</button>
                    <button class="tab-btn" data-tab="listaCli">Listagem</button>
                </div>

                <!-- Conteúdo: Cadastro -->
                <div class="tab-content active" id="cadastroCli">
                    <form id="formCliente" class="form-grid">
                        <input type="hidden" id="idCliente">
                        <div class="form-group full">
                            <label>Nome: *</label>
                            <input type="text" id="txtNomeCliente" required>
                        </div>

                        <div class="form-group">
                            <label>CPF: *</label>
                            <input type="text" id="txtCPFCliente"
                                placeholder="000.000.000-00"
                                oninput="formatCPF(this)" required>
                        </div>

                        <div class="form-group">
                            <label>Telefone:</label>
                            <input type="text" id="txtTelefone"
                                placeholder="(00) 00000-0000"
                                oninput="formatTelefone(this)">
                        </div>

                        <div class="form-group">
                            <label>CEP: *</label>
                            <input type="text" id="txtCEPCliente" placeholder="00000-000" required>
                        </div>

                        <div class="form-group">
                            <label>Número:</label>
                            <input type="text" id="txtNumeroCliente">
                        </div>

                        <div class="form-group full">
                            <label>Endereço:</label>
                            <input type="text" id="txtEnderecoCliente">
                        </div>

                        <div class="form-group full">
                            <label>Bairro:</label>
                            <input type="text" id="txtBairroCliente">
                        </div>

                        <div class="form-group full">
                            <label>E-mail:</label>
                            <input type="email" id="txtEmailCliente">
                        </div>

                        <div class="form-actions full">
                            <button type="submit" class="btn-primary">
                                Cadastrar Cliente
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Conteúdo: Listagem -->
                <div class="tab-content" id="listaCli">
                    <?php include 'listagem.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="../assets/styleCadastro.css" />
    <script src="../cadastro/pagamentos.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <div class="dashboard-main">
        <div class="card-form">
            <div class="card">

                <h2 class="section-title">Registro de Vendas</h2>

                <!-- Abas -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="cadastroVenda">Cadastro</button>
                    <button class="tab-btn" data-tab="listaVenda">Listagem</button>
                </div>

                <!-- Conteúdo: Cadastro -->
                <div class="tab-content active" id="cadastroVenda">
                    <form id="formVenda">
                        <input type="hidden" id="idVenda">
                        <div class="form-actions">
                            <button type="button" id="btnFecharVisualizacao" style="display: none" class="btn-primary" onClick="limparVenda()">
                                Nova Venda
                            </button>
                        </div>
                        <div class="form-grid">
                            <!-- CLIENTE -->
                            <div class="form-group select-group">
                                <label>Cliente: *</label>
                                <select id="txtSelectCliente" required>
                                    <option value="">Carregando clientes...</option>
                                </select>
                            </div>
                            <!-- FORMA DE PAGAMENTO -->
                            <div class="form-group select-group">
                                <label>Forma de Pagamento: *</label>
                                <select id="txtSelectPagamento" required>
                                    <option value="">Carregando formas de pagamento...</option>
                                </select>
                            </div>
                            <!-- PRODUTO -->
                            <div class="form-group full">
                                <label>Adicionar Produto: *</label>
                                <div style="display:flex; gap:12px;">
                                    <div class="select-group" style="flex:2">
                                        <select id="txtSelectProduto" style="flex:2">
                                            <option value="">Selecione o produto</option>
                                        </select>
                                    </div>
                                    <input type="number" id="txtQtdProduto" placeholder="Quantidade *" min="1" max="99" style="flex:1">

                                    <button type="button" class="btn-primary" id="btnAddProduto">
                                        Adicionar
                                    </button>
                                </div>
                            </div>
                            <!-- LISTA DE PRODUTOS -->
                            <div class="form-group full">
                                <table class="table-lista" id="tabelaProdutosVenda">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Qtd</th>
                                            <th>Valor Unit.</th>
                                            <th>Total</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaProdutos">
                                        <tr>
                                            <td colspan="5" style="text-align:center;">
                                                Nenhum produto adicionado
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- TOTAL -->
                            <div class="form-group full">
                                <h3>
                                    Total da Venda:
                                    <strong id="totalVenda">R$ 0.00</strong>
                                </h3>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="btnSalvarVenda" class="btn-primary">
                                Finalizar Venda
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Conteúdo: Listagem -->
                <div class="tab-content" id="listaVenda">
                    <?php include 'listagem.php'; ?>
                </div>
            </div>
        </div>
    </div>
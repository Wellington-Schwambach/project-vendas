(() => {
    let produtosVenda = [];

    /* Troca de Telas entre Cadastro e Listagem */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });

    /* Carrega dados do select de Clientes */
    async function carregarClientes() {
        try {
            const res = await fetch('/project_vendas/api/index.php/clientes', {
                method: 'GET',
                credentials: 'include'
            });
            const dados = await res.json();
            const select = document.getElementById('txtSelectCliente');
            select.innerHTML = '<option value="">Selecione o cliente</option>';

            dados.forEach(data => {
                select.innerHTML += `<option value="${data.id_cliente}">${data.ds_nome} - [${data.nr_cpf}]</option>`;
            });

        } catch (err) {
            console.error('Erro:', err);
        }
    }

    /* Carrega dados do select de Produtos */
    async function carregarProdutos() {
        try {
            const res = await fetch('/project_vendas/api/index.php/produtos', {
                method: 'GET',
                credentials: 'include'
            });
            const dados = await res.json();
            const select = document.getElementById('txtSelectProduto');
            select.innerHTML = '<option value="">Selecione o produto</option>';

            dados.forEach(data => {
                select.innerHTML += `<option value="${data.id_produto}">${data.ds_produto} - Qtd: ${data.nr_quantidade}</option>`;
            });

        } catch (err) {
            console.error('Erro:', err);
        }
    }

    /* Carrega dados do select de Pagamentos */
    async function carregarPagamentos() {
        try {
            const res = await fetch('/project_vendas/api/index.php/pagamentos', {
                method: 'GET',
                credentials: 'include'
            });
            const dados = await res.json();
            const select = document.getElementById('txtSelectPagamento');
            select.innerHTML = '<option value="">Selecione o pagamentos</option>';

            dados.forEach(data => {
                select.innerHTML += `<option value="${data.id_pagamento}">${data.ds_pagamento} - [Nº Parcelas ${data.nr_parcelas}]</option>`;
            });

        } catch (err) {
            console.error('Erro:', err);
        }
    }

    document.querySelectorAll('.form-group select').forEach(select => {
        select.addEventListener('change', () => {
            select.classList.add('filled');
        });
    });

    /* Adicionar produto */
    document.getElementById('btnAddProduto').onclick = async () => {
        const select = document.getElementById('txtSelectProduto');
        const qtd = Number(document.getElementById('txtQtdProduto').value);

        if (!select.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione um produto'
            });
            return;
        }

        if (!qtd) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Adicione a quantidade de produtos!'
            });
            return;
        }

        try {
            const res = await fetch(
                `/project_vendas/api/index.php/produtos?id_produto=${select.value}`,
                {
                    method: 'GET',
                    credentials: 'include'
                }
            );

            if (!res.ok) throw new Error('Erro ao buscar produto');
            const produto = await res.json();

            /* Quantidade já adicionada desse produto */
            const qtdJaInserida = produtosVenda
                .filter(p => p.id_produto == produto.id_produto)
                .reduce((total, p) => total + p.qtd, 0);

            /* Validação de estoque total */
            if (qtdJaInserida + qtd > produto.nr_quantidade) {
                Swal.fire(
                    'Quantidade inválida',
                    `Estoque disponível: ${produto.nr_quantidade} unid.<br>
                        Já adicionadas: ${qtdJaInserida} unid.`,
                    'warning'
                );
                return;
            }

            produtosVenda.push({
                id_produto: produto.id_produto,
                nome: produto.ds_produto,
                valor: Number(produto.nr_valor),
                qtd: qtd
            });

            renderTabela();

        } catch (e) {
            console.error(e);
            Swal.fire('Erro', 'Erro ao buscar dados do produto', 'error');
        }
    };


    /* Remover Item da tabela */
    function removerProduto(index) {
        produtosVenda.splice(index, 1);
        renderTabela();
    }

    /* Render da tabela */
    function renderTabela() {
        const tabela = document.getElementById('tabelaProdutosVenda');
        const tbodyTabela = tabela.querySelector('tbody');
        let totalVenda = 0;
        tbodyTabela.innerHTML = '';

        produtosVenda.forEach((p, i) => {
            const subtotal = p.valor * p.qtd;
            totalVenda += subtotal;
            tbodyTabela.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${p.nome}</td>
                    <td>${p.qtd} Un.</td>
                    <td>R$ ${(p.valor).toFixed(2)}</td>
                    <td>R$ ${(subtotal).toFixed(2)}</td>
                    <td>
                        <button class="btn-excluir" data-index="${i}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            document.getElementById('totalVenda').innerHTML = `R$ ${totalVenda.toFixed(2)}`;
        });
        /* Remover Item da tabela */
        tbodyTabela.addEventListener('click', e => {
            if (e.target.closest('.btn-excluir')) {
                const index = e.target.closest('.btn-excluir').dataset.index;
                removerProduto(index);
            }
        });
    }

    /* Salvar venda */
    document.getElementById('btnSalvarVenda').addEventListener('click', salvarVenda);
    async function salvarVenda() {
        try {
            const id_cliente = document.getElementById('txtSelectCliente').value;
            const forma_pagamento = document.getElementById('txtSelectPagamento').value;

            // validações
            if (!id_cliente) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione um cliente'
                });
                return;
            }

            if (!forma_pagamento) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione a forma de pagamento'
                });
                return;
            }

            if (!Array.isArray(produtosVenda) || produtosVenda.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Adicione pelo menos um produto à venda'
                });
                return;
            }

            const body = {
                id_cliente: Number(id_cliente),
                forma_pagamento: forma_pagamento,
                produtos: produtosVenda.map(p => ({
                    id_produto: Number(p.id_produto),
                    qtd: Number(p.qtd),
                    valorVenda: Number(p.valor * p.qtd)
                }))
            };

            // loading
            Swal.fire({
                title: 'Salvando venda...',
                text: 'Aguarde',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch('/project_vendas/api/index.php/vendas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(body)
            });

            const data = await response.json();
            Swal.close();

            if (!response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao salvar venda'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: data.message || 'Venda salva com sucesso'
            }).then(() => {
                produtosVenda = [];
                carregarClientes();
                carregarProdutos();
                carregarPagamentos();
                carregarVendas();
                renderTabela();
                document.getElementById('totalVenda').innerHTML = `R$ 0.00`;
                document.getElementById('txtQtdProduto').value = '';
            });

        } catch (error) {
            console.error(error);
            Swal.close();

            Swal.fire({
                icon: 'error',
                title: 'Erro inesperado',
                text: 'Não foi possível salvar a venda'
            });
        }
    }

    /* Consultar Vendas na Listagem */
    const inputClienteVendas = document.getElementById('txtClienteVenda');
    const btnConsultarVendas = document.getElementById('btnConsultarVendas');
    const tbodyVendas = document.getElementById('tbodyVendas');

    async function carregarVendas(cliente = '') {
        try {
            const res = await fetch(
                `/project_vendas/api/index.php/vendas?ds_cliente=${cliente}`,
                { credentials: 'include' }
            );

            const venda = await res.json();
            tbodyVendas.innerHTML = '';

            const vendas = Array.isArray(venda) ? venda : [venda];

            if (vendas.length === 0 || !vendas[0].id_venda) {
                tbodyVendas.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center">
                        Nenhuma venda encontrada
                    </td>
                </tr>
            `;
                return;
            }
            vendas.forEach(data => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                                <td>${data.id_venda}</td>
                                <td>${data.ds_nome}</td>
                                <td style="text-align:center">${data.dt_venda}</td>
                                <td style="text-align:center">${data.ds_pagamento}</td>
                                <td style="text-align:center">${formatReal(data.nr_totalvenda)}</td>
                                <td>
                                    <button class="btn-detalhes" title="Detalhes" data-id="${data.id_venda}">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-excluir" title="Excluir" data-id="${data.id_venda}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            `;
                tbodyVendas.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            tbodyVendas.innerHTML = `
                                    <tr>
                                        <td colspan="5" style="color:red;text-align:center">
                                            Erro ao carregar vendas
                                        </td>
                                    </tr>
                                `;
        }
    }
    btnConsultarVendas.addEventListener('click', () => {
        carregarVendas(inputClienteVendas.value.trim());
    });

    document.getElementById('tbodyVendas').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-excluir');
        if (!btn) return;
        const idVenda = btn.dataset.id;

        Swal.fire({
            title: 'Excluir venda?',
            text: 'Essa ação não poderá ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const res = await fetch('/project_vendas/api/index.php/vendas', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        id_venda: idVenda
                    })
                });

                const data = await res.json();

                if (!data.success) {
                    Swal.fire('Erro', data.message, 'error');
                    return;
                }

                Swal.fire('Excluído!', data.message, 'success');
                carregarClientes();
                carregarProdutos();
                carregarPagamentos();
                
                // Remove a linha da tabela
                btn.closest('tr').remove();

            } catch (err) {
                console.error(err);
                Swal.fire('Erro', 'Erro ao excluir venda', 'error');
            }
        });
    });

    carregarVendas();
    carregarClientes();
    carregarProdutos();
    carregarPagamentos();
})();

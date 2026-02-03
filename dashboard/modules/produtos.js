(() => {
    /* Troca de Telas entre Cadastro e Listagem */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });

    /* Submit para cadastro do Produto */
    document.getElementById("formProduto").addEventListener("submit", async function (e) {
        e.preventDefault();

        const idProduto = document.getElementById("idProduto").value;

        const payload = {
            id_produto: idProduto || null,
            nomeProduto: document.getElementById("txtProduto").value,
            qtdProduto: document.getElementById("txtQuantidadeProduto").value,
            valorProduto: document.getElementById("txtValorProduto").value
        };

        const method = idProduto ? "PUT" : "POST";

        try {
            const response = await fetch("/project_vendas/api/index.php/produtos", {
                method,
                credentials: "include",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: "success",
                    title: "Sucesso!",
                    text: result.message,
                    confirmButtonColor: "#4270F4"
                }).then(() => {
                    resetFormProdutos();
                    carregarProdutos('')
                });
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Atenção",
                    text: result.message,
                    confirmButtonColor: "#f39c12"
                });
            }

        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Erro",
                text: "Erro ao comunicar com o servidor",
                confirmButtonColor: "#e74c3c"
            });
        }
    });

    /* Listagem dos produtos */
    const inputProduto = document.getElementById('txtProdutoLista');
    const btnConsultarProduto = document.getElementById('btnConsultarProduto');
    const tbodyProdutos = document.getElementById('listaProdutos');

    async function carregarProdutos(nome = '') {
        try {
            const res = await fetch('/project_vendas/api/index.php/produtos?nomeProduto=' + nome);
            const produto = await res.json();

            tbodyProdutos.innerHTML = '';

            const produtos = Array.isArray(produto) ? produto : [produto];

            if (produtos.length === 0 || !produtos[0].id_produto) {
                tbodyProdutos.innerHTML = '<tr><td colspan="6" style="text-align:center">Nenhum produto encontrado</td></tr>';
                return;
            }

            produtos.forEach(data => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                                <td>${data.ds_produto}</td>
                                <td style="text-align: center">${data.nr_quantidade}</td>
                                <td style="text-align: center">${formatReal(data.nr_valor)}</td>
                                <td>
                                    <button class="btn-editar" title="Editar" data-id="${data.id_produto}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn-excluir" title="Excluir" data-id="${data.id_produto}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            `;
                tbodyProdutos.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            tbodyProdutos.innerHTML = '<tr><td colspan="6" style="color:red;text-align:center">Erro ao carregar produtos</td></tr>';
        }
    }

    // Evento do botão Consultar
    btnConsultarProduto.addEventListener('click', () => {
        carregarProdutos(inputProduto.value.trim());
    });

    carregarProdutos();

    tbodyProdutos.addEventListener("click", async (e) => {
        const btn = e.target.closest(".btn-editar");
        if (!btn) return;

        const id = btn.dataset.id;

        try {
            document.querySelectorAll(".tab-btn").forEach(b =>
                b.classList.remove("active")
            );
            document.querySelector('[data-tab="cadastroProd"]').classList.add("active");

            document.querySelectorAll(".tab-content").forEach(t =>
                t.classList.remove("active")
            );
            document.getElementById("cadastroProd").classList.add("active");


            const res = await fetch(`/project_vendas/api/index.php/produtos?id_produto=${id}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await res.json();
            document.getElementById("idProduto").value = data.id_produto;
            document.getElementById("txtProduto").value = data.ds_produto;
            document.getElementById("txtQuantidadeProduto").value = data.nr_quantidade;
            document.getElementById("txtValorProduto").value = data.nr_valor;

            document.querySelector("#formProduto button[type=submit]").textContent = "Atualizar Produto";

        } catch (err) {
            console.error(err);
            Swal.fire("Erro", "Erro ao carregar produto", "error");
        }
    });

    tbodyProdutos.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-excluir');
        if (!btn) return;
        resetFormProdutos()
        const id = btn.dataset.id;

        Swal.fire({
            title: "Deletar Produto!",
            text: "Tem certeza que deseja excluir este produto?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, deletar!"
        }).then((result) => {
            if (result.isConfirmed) {
                // Função async interna
                (async () => {
                    try {
                        const res = await fetch(`/project_vendas/api/index.php/produtos`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_produto: id })
                        });

                        const data = await res.json();

                        if (data.success) {
                            btn.closest('tr').remove();
                            Swal.fire('Deletado!', data.message, 'success');
                            carregarProdutos()
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Erro', 'Erro ao excluir produto.', 'error');
                    }
                })();
            }
        });
    });

    function resetFormProdutos() {
        document.getElementById("formProduto").reset();
        document.getElementById("idProduto").value = "";
        document.querySelector("#formProduto button[type=submit]").textContent = "Cadastrar Produto";
    }
})();
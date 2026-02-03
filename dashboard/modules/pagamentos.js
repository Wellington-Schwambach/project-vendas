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

    /* Submit para cadastro do pagamento */
    document.getElementById("formPagamento").addEventListener("submit", async function (e) {
        e.preventDefault();

        const idPagamento = document.getElementById("idPagamento").value;

        const payload = {
            id_pagamento: idPagamento || null,
            formaPagamento: document.getElementById("txtPagamento").value,
            qtdParcelas: document.getElementById("txtQtdParcelas").value
        };

        const method = idPagamento ? "PUT" : "POST";

        try {
            const response = await fetch("/project_vendas/api/index.php/pagamentos", {
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
                    resetFormPagamentos();
                    carregarPagamentos('')
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

    /* Listagem dos pagamentos */
    const inputPagamento = document.getElementById('txtPagamentoLista');
    const btnConsultarPagamento = document.getElementById('btnConsultarPagamento');
    const tbodyPagamentos = document.getElementById('listaPagamentos');

    async function carregarPagamentos(nome = '') {
        try {
            const res = await fetch('/project_vendas/api/index.php/pagamentos?formaPagamento=' + nome);
            const pagamento = await res.json();

            tbodyPagamentos.innerHTML = '';

            const pagamentos = Array.isArray(pagamento) ? pagamento : [pagamento];

            if (pagamentos.length === 0 || !pagamentos[0].id_pagamento) {
                tbodyPagamentos.innerHTML = '<tr><td colspan="6" style="text-align:center">Nenhuma forma de pagamento encontrada</td></tr>';
                return;
            }

            pagamentos.forEach(data => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                                <td>${data.ds_pagamento}</td>
                                <td style="text-align: center">${data.nr_parcelas}</td>
                                <td>
                                    <button class="btn-editar" title="Editar" data-id="${data.id_pagamento}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn-excluir" title="Excluir" data-id="${data.id_pagamento}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            `;
                tbodyPagamentos.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            tbodyPagamentos.innerHTML = '<tr><td colspan="6" style="color:red;text-align:center">Erro ao carregar as formas de pagamento</td></tr>';
        }
    }

    // Evento do botão Consultar
    btnConsultarPagamento.addEventListener('click', () => {
        carregarPagamentos(inputPagamento.value.trim());
    });

    carregarPagamentos();

    tbodyPagamentos.addEventListener("click", async (e) => {
        const btn = e.target.closest(".btn-editar");
        if (!btn) return;

        const id = btn.dataset.id;

        try {
            document.querySelectorAll(".tab-btn").forEach(b =>
                b.classList.remove("active")
            );
            document.querySelector('[data-tab="cadastroPagamento"]').classList.add("active");

            document.querySelectorAll(".tab-content").forEach(t =>
                t.classList.remove("active")
            );
            document.getElementById("cadastroPagamento").classList.add("active");


            const res = await fetch(`/project_vendas/api/index.php/pagamentos?id_pagamento=${id}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await res.json();
            document.getElementById("idPagamento").value = data.id_pagamento;
            document.getElementById("txtPagamento").value = data.ds_pagamento;
            document.getElementById("txtQtdParcelas").value = data.nr_parcelas;

            document.querySelector("#formPagamento button[type=submit]").textContent = "Atualizar Forma de Pagamento";

        } catch (err) {
            console.error(err);
            Swal.fire("Erro", "Erro ao carregar forma de pagamento", "error");
        }
    });

    tbodyPagamentos.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-excluir');
        if (!btn) return;
        resetFormPagamentos()
        const id = btn.dataset.id;

        Swal.fire({
            title: "Deletar Pagamento!",
            text: "Tem certeza que deseja excluir esta forma de pagamento?",
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
                        const res = await fetch(`/project_vendas/api/index.php/pagamentos`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_pagamento: id })
                        });

                        const data = await res.json();

                        if (data.success) {
                            btn.closest('tr').remove();
                            Swal.fire('Deletado!', data.message, 'success');
                            carregarPagamentos()
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Erro', 'Erro ao excluir pagamento.', 'error');
                    }
                })();
            }
        });
    });

    function resetFormPagamentos() {
        document.getElementById("formPagamento").reset();
        document.getElementById("idPagamento").value = "";
        document.querySelector("#formPagamento button[type=submit]").textContent = "Cadastrar Forma de Pagamento";
    }

    const inputParcelas = document.getElementById('txtQtdParcelas');

    inputParcelas.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        if (this.value === '') return;
        let valor = parseInt(this.value, 10);

        if (valor > 12) {
            this.value = 12;
        } else if (valor < 1) {
            this.value = 1;
        }
    });
})();
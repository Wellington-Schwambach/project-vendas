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

    /* Submit para cadastro do Cliente */
    document.getElementById("formCliente").addEventListener("submit", async function (e) {
        e.preventDefault();

        const idCliente = document.getElementById("idCliente").value;

        const payload = {
            id_cliente: idCliente || null,
            nome: document.getElementById("txtNomeCliente").value,
            cpf: document.getElementById("txtCPFCliente").value,
            telefone: document.getElementById("txtTelefone").value,
            cep: document.getElementById("txtCEPCliente").value.replace("-", ""),
            endereco: document.getElementById("txtEnderecoCliente").value,
            bairro: document.getElementById("txtBairroCliente").value,
            numero: document.getElementById("txtNumeroCliente").value,
            email: document.getElementById("txtEmailCliente").value
        };

        const method = idCliente ? "PUT" : "POST";

        try {
            const response = await fetch("/project_vendas/api/index.php/clientes", {
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
                    resetFormCliente();
                    carregarClientes('', '')
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

    /* Busca do CEP via API */
    document.getElementById('txtCEPCliente').addEventListener('blur', function () {
        let cep = this.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            Swal.fire({
                icon: "warning",
                title: "Atenção",
                text: 'CEP Inválido, Verifique!',
                confirmButtonColor: "#f39c12"
            });
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {

                if (data.erro) {
                    Swal.fire({
                        icon: "warning",
                        title: "Atenção",
                        text: 'CEP não encontrado',
                        confirmButtonColor: "#f39c12"
                    });
                    return;
                }

                document.getElementById('txtEnderecoCliente').value = data.logradouro || '';
                document.getElementById('txtBairroCliente').value = data.bairro || '';

            })
            .catch(() => {
                Swal.fire({
                    icon: "warning",
                    title: "Atenção",
                    text: 'Erro ao consultar o CEP',
                    confirmButtonColor: "#f31212"
                });
            });

    });

    /* Formatação de Telefone */
    window.formatTelefone = function formatTelefone(input) {
        let value = input.value.replace(/\D/g, '');

        value = value.slice(0, 11);

        if (value.length > 6) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/(\d{2})(\d+)/, '($1) $2');
        }

        input.value = value;
    }

    /* Formatação de Telefone */
    window.formatCPF = function formatCPF(input) {
        let value = input.value.replace(/\D/g, '');

        value = value.slice(0, 11);

        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        input.value = value;
    }



    /* Listagem dos clientes */
    const inputNome = document.getElementById('txtClienteLista');
    const inputCPF = document.getElementById('txtCPFLista');
    const btnConsultarCliente = document.getElementById('btnConsultarCliente');
    const tbodyClientes = document.getElementById('listaClientes');

    async function carregarClientes(nome = '', cpf = '') {
        try {
            const res = await fetch('/project_vendas/api/index.php/clientes?nome=' + nome + '&cpf=' + cpf);
            const cliente = await res.json();

            tbodyClientes.innerHTML = '';

            const clientes = Array.isArray(cliente) ? cliente : [cliente];

            if (clientes.length === 0 || !clientes[0].id_cliente) {
                tbodyClientes.innerHTML = '<tr><td colspan="6" style="text-align:center">Nenhum cliente encontrado</td></tr>';
                return;
            }

            clientes.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${c.ds_nome}</td>
            <td>${c.nr_cpf}</td>
            <td>${c.nr_telefone || ''}</td>
            <td>${c.ds_endereco || ''}</td>
            <td>${c.nr_cep || ''}</td>
            <td>
                <button class="btn-editar" title="Editar" data-id="${c.id_cliente}">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn-excluir" title="Excluir" data-id="${c.id_cliente}">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
                tbodyClientes.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            tbodyClientes.innerHTML = '<tr><td colspan="6" style="color:red;text-align:center">Erro ao carregar clientes</td></tr>';
        }
    }

    // Evento do botão Consultar
    btnConsultarCliente.addEventListener('click', () => {
        carregarClientes(inputNome.value.trim(), inputCPF.value.trim());
    });

    carregarClientes();

    tbodyClientes.addEventListener("click", async (e) => {
        const btn = e.target.closest(".btn-editar");
        if (!btn) return;

        const id = btn.dataset.id;
        try {
            document.querySelectorAll(".tab-btn").forEach(b =>
                b.classList.remove("active")
            );
            document.querySelector('[data-tab="cadastroCli"]').classList.add("active");

            document.querySelectorAll(".tab-content").forEach(t =>
                t.classList.remove("active")
            );
            document.getElementById("cadastroCli").classList.add("active");


            const res = await fetch(`/project_vendas/api/index.php/clientes?id_cliente=${id}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await res.json();
            document.getElementById("idCliente").value = data.id_cliente;
            document.getElementById("txtNomeCliente").value = data.ds_nome;
            document.getElementById("txtCPFCliente").value = data.nr_cpf;
            document.getElementById("txtTelefone").value = data.nr_telefone ?? "";
            document.getElementById("txtCEPCliente").value = data.nr_cep;
            document.getElementById("txtEnderecoCliente").value = data.ds_endereco ?? "";
            document.getElementById("txtBairroCliente").value = data.ds_bairro ?? "";
            document.getElementById("txtNumeroCliente").value = data.nr_numero ?? "";
            document.getElementById("txtEmailCliente").value = data.ds_email ?? "";

            document.getElementById("txtCPFCliente").readOnly = true;
            document.querySelector("#formCliente button[type=submit]").textContent = "Atualizar Cliente";

        } catch (err) {
            console.error(err);
            Swal.fire("Erro", "Erro ao carregar cliente", "error");
        }
    });

    tbodyClientes.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-excluir');
        if (!btn) return;
        resetFormCliente();
        const id = btn.dataset.id;

        Swal.fire({
            title: "Deletar Cliente!",
            text: "Tem certeza que deseja excluir este cliente?",
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
                        const res = await fetch(`/project_vendas/api/index.php/clientes`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_cliente: id })
                        });

                        const data = await res.json();

                        if (data.success) {
                            btn.closest('tr').remove();
                            Swal.fire('Deletado!', data.message, 'success');
                            carregarClientes()
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Erro', 'Erro ao excluir cliente.', 'error');
                    }
                })();
            }
        });
    });

    function resetFormCliente() {
        document.getElementById("formCliente").reset();
        document.getElementById("idCliente").value = "";
        document.getElementById("txtCPFCliente").readOnly = false;
        document.querySelector("#formCliente button[type=submit]").textContent = "Cadastrar Cliente";
    }

})();
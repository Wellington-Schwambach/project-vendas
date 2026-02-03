(async () => {
    const cards = document.querySelectorAll('.transfer-card');

    // Vendas Mensais
    function parseDateBR(data) {
        if (!data) return null;
        const [dia, mes, ano] = data.split('/');
        return new Date(ano, mes - 1, dia);
    }

    async function getVendasMensais() {
        try {
            const res = await fetch('/project-vendas/api/index.php/vendas', {
                credentials: 'include'
            });

            const vendas = await res.json();
            const vendasMes = Array.isArray(vendas) ? vendas : [];
            const now = new Date();

            const total = vendasMes
                .filter(v => {
                    const dt = parseDateBR(v.dt_venda);
                    if (!dt || isNaN(dt)) return false;

                    return dt.getMonth() === now.getMonth()
                        && dt.getFullYear() === now.getFullYear();
                })
                .reduce((sum, v) => sum + Number(v.nr_totalvenda || 0), 0);

            cards[0].querySelector('.card-amount').textContent = formatReal(total);
        } catch (err) {
            console.error(err);
            cards[0].querySelector('.card-amount').textContent = 'Erro';
        }
    }

    // Clientes Ativos
    async function getClientesAtivos() {
        try {
            const res = await fetch('/project-vendas/api/index.php/clientes', { credentials: 'include' });
            const clientes = await res.json();

            const clientesArray = Array.isArray(clientes) ? clientes : [clientes];
            cards[1].querySelector('.card-amount').textContent = clientesArray.length;
        } catch (err) {
            cards[1].querySelector('.card-amount').textContent = 'Erro';
        }
    }

    // Produtos em Estoque
    async function getProdutosEstoque() {
        try {
            const res = await fetch('/project-vendas/api/index.php/produtos', { credentials: 'include' });
            const produtos = await res.json();

            const produtosArray = Array.isArray(produtos) ? produtos : [produtos];
            const totalEstoque = produtosArray.reduce((sum, p) => sum + Number(p.nr_quantidade), 0);

            cards[2].querySelector('.card-amount').textContent = totalEstoque;
        } catch (err) {
            cards[2].querySelector('.card-amount').textContent = 'Erro';
        }
    }

    await getVendasMensais();
    await getClientesAtivos();
    await getProdutosEstoque();
})();

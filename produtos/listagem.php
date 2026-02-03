<div class="search-container">
    <div class="form-group">
        <div style="display: block;">
            <label for="txtProdutoLista">Nome do Produto:</label>
            <input type="text" id="txtProdutoLista" placeholder="Digite o nome do Produto">

            <button style="margin-left: 30px;" class="btn-primary" id="btnConsultarProduto">Consultar</button>
        </div>
    </div>
</div>
<br>
<hr>
<br>
<table class="table-lista">
    <thead>
        <tr>
            <th>Nome do Produto</th>
            <th style="text-align: center">Quantidade</th>
            <th style="text-align: center">Valor da Unidade</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="listaProdutos">
    </tbody>
</table>
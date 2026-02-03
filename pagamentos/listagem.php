<div class="search-container">
    <div class="form-group">
        <div style="display: block;">
            <label for="txtPagamentoLista">Forma de pagamento:</label>
            <input type="text" id="txtPagamentoLista" placeholder="Digite a forma de pagamento">

            <button style="margin-left: 30px;" class="btn-primary" id="btnConsultarPagamento">Consultar</button>
        </div>
    </div>
</div>
<br>
<hr>
<br>
<table class="table-lista">
    <thead>
        <tr>
            <th>Forma de Pagamento</th>
            <th style="text-align: center">Parcelas</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="listaPagamentos">
    </tbody>
</table>
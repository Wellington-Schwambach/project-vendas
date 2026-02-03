<div class="search-container">
    <div class="form-group full">
        <div class="grid-50">
            <input type="text" id="txtClienteVenda" placeholder="Digite o nome do cliente">

            <button type="button" class="btn-primary" id="btnConsultarVendas">
                Consultar
            </button>
        </div>
    </div>
</div>
<br>
<hr>
<br>
<table class="table-lista">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th style="text-align:center">Data</th>
            <th style="text-align:center">Forma Pagamento</th>
            <th style="text-align:center">Total</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="tbodyVendas"></tbody>
</table>
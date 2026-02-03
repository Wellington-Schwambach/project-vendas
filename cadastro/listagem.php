<div class="search-container">
    <div class="form-group">
        <div style="display: block;">
            <label for="txtClienteLista">Nome:</label>
            <input type="text" id="txtClienteLista" placeholder="Digite o nome">

            <label for="txtCPFLista">CPF:</label>
            <input type="text" id="txtCPFLista" placeholder="Digite o CPF">

            <button style="margin-left: 30px;" class="btn-primary" id="btnConsultarCliente">Consultar</button>
        </div>
    </div>
</div>
<br>
<hr>
<br>
<table class="table-lista">
    <thead>
        <tr>
            <th>Nome</th>
            <th>CPF</th>
            <th>Telefone</th>
            <th>Endereço</th>
            <th>CEP</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="listaClientes">
    </tbody>
</table>
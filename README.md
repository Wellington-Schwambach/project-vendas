# Projeto Vendas - API e Frontend

Esta aplicação é um sistema de vendas simples, desenvolvido em PHP para o backend e JavaScript para o frontend.
Permite cadastrar Clientes, Produtos, Formas de Pagamento e registrar Vendas. O sistema atende aos requisitos obrigatórios de CRUD
para todos os módulos e listagem de vendas com filtro por cliente.

## Pré-requisitos

PHP 8+
Servidor web (Apache[XAMPP])
Banco de dados PostgreSQL
Extensão PDO habilitada

## Instalação

## Clone o projeto

<https://github.com/Wellington-Schwambach/project-vendas.git>
cd projeto-vendas

## Configure o banco de dados em api/config/database.php

"pgsql:host=localhost;port=5432;dbname=exemplo123", // Aqui deve ser inserido o nome do seu banco de dados
"postgres",
"exemplo123", // Aqui deve ser inserida a senha do seu banco de dados

## Importe o schema do banco de dados (exemplo PostgreSQL)

## clientes

CREATE TABLE IF NOT EXISTS public.tab_clientes
(
    id_cliente serial NOT NULL DEFAULT,
    ds_nome character varying(200) COLLATE pg_catalog."default" NOT NULL,
    nr_cpf character varying(14) COLLATE pg_catalog."default" NOT NULL,
    ds_endereco character varying(200) COLLATE pg_catalog."default",
    ds_bairro character varying(200) COLLATE pg_catalog."default",
    nr_numero character varying(20) COLLATE pg_catalog."default",
    nr_telefone character varying(16) COLLATE pg_catalog."default",
    nr_cep character varying(8) COLLATE pg_catalog."default",
    ds_email character varying(160) COLLATE pg_catalog."default",
    id_usuario integer,
    CONSTRAINT tab_clientes_pkey PRIMARY KEY (id_cliente)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.tab_clientes
    OWNER to postgres;

## produtos

CREATE TABLE IF NOT EXISTS public.tab_produtos
(
    id_produto serial NOT NULL DEFAULT,
    ds_produto character varying(200) COLLATE pg_catalog."default" NOT NULL,
    nr_quantidade integer NOT NULL,
    nr_valor numeric(14,2) NOT NULL,
    CONSTRAINT tab_produtos_pkey PRIMARY KEY (id_produto)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.tab_produtos
    OWNER to postgres;

## pagamentos

    CREATE TABLE IF NOT EXISTS public.tab_pagamentos
    (
        id_pagamento serial NOT NULL DEFAULT,
        ds_pagamento character varying(40) COLLATE pg_catalog."default",
        nr_parcelas integer,
        CONSTRAINT tab_pagamentos_pkey PRIMARY KEY (id_pagamento)
    )

    TABLESPACE pg_default;

    ALTER TABLE IF EXISTS public.tab_pagamentos
        OWNER to postgres;

## vendas

CREATE TABLE IF NOT EXISTS public.tab_vendas
(
    id_venda serial NOT NULL DEFAULT,
    id_pagamento integer NOT NULL,
    nr_totalvenda numeric(14,2) NOT NULL,
    id_cliente integer NOT NULL,
    dt_venda timestamp without time zone NOT NULL DEFAULT now(),
    id_usuario integer NOT NULL,
    CONSTRAINT tab_vendas_pkey PRIMARY KEY (id_venda)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.tab_vendas
    OWNER to postgres;

## produtos de cada venda

CREATE TABLE IF NOT EXISTS public.tab_produtos_venda
(
    id_produtos_venda serial NOT NULL DEFAULT,
    id_venda integer,
    id_produto integer,
    nr_qtd_venda integer,
    CONSTRAINT tab_produtos_venda_pkey PRIMARY KEY (id_produtos_venda)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.tab_produtos_venda
    OWNER to postgres;

## usuários

CREATE TABLE IF NOT EXISTS public.tab_usuarios
(
    id_user serial NOT NULL DEFAULT,
    ds_password character varying(255) COLLATE pg_catalog."default" NOT NULL,
    id_cliente integer NOT NULL,
    ds_usuario character varying(60) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT tab_usuarios_pkey PRIMARY KEY (id_user)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.tab_usuarios
    OWNER to postgres;

## Acesso do LOGIN

Usuário: admin
Senha: 123456

INSERT INTO tab_usuarios (ds_usuario, ds_password, id_cliente) VALUES ('admin', '$2y$10$kpLsC7/0XWj7Bdv3V3Df3e9hOpkPytIoduxxKSeyJ6yvEcedrNNfG', 1)

## Acesse o frontend

<http://localhost/project_vendas/index.html>

### Endpoints da API

## Clientes

> Listar clientes: GET /api/index.php/clientes
> Retorna todos os clientes. Pode filtrar por nome (?nome=...) ou CPF (?cpf=...).
> Obter cliente específico: GET /api/index.php/clientes?id_cliente=...
> Retorna os dados de um cliente específico pelo ID.
> Cadastrar cliente: POST /api/index.php/clientes
> Campos: nome, CPF, telefone, CEP, endereço, bairro, número, email.
> Atualizar cliente: PUT /api/index.php/clientes
> Campos: mesmos do cadastro, incluindo id_cliente.
> Excluir cliente: DELETE /api/index.php/clientes
> Enviar id_cliente no corpo da requisição.

## Produtos

> Listar produtos: GET /api/index.php/produtos
> Retorna todos os produtos. Pode filtrar por nome (?nomeProduto=...).
> Obter produto específico: GET /api/index.php/produtos?id_produto=...
> Cadastrar produto: POST /api/index.php/produtos
> Campos: nomeProduto, qtdProduto, valorProduto.
> Atualizar produto: PUT /api/index.php/produtos
> Campos: mesmos do cadastro, incluindo id_produto.
> Excluir produto: DELETE /api/index.php/produtos
> Enviar id_produto no corpo da requisição.

## Formas de Pagamento

> Listar formas de pagamento: GET /api/index.php/pagamentos
> Retorna todas as formas de pagamento. Pode filtrar por formaPagamento.
> Obter forma específica: GET /api/index.php/pagamentos?id_pagamento=...
> Cadastrar forma de pagamento: POST /api/index.php/pagamentos
> Campos: formaPagamento, qtdParcelas.
> Atualizar forma de pagamento: PUT /api/index.php/pagamentos
> Campos: mesmos do cadastro, incluindo id_pagamento.
> Excluir forma de pagamento: DELETE /api/index.php/pagamentos
> Enviar id_pagamento no corpo da requisição.

## Vendas

> Listar vendas: GET /api/index.php/vendas
> Retorna todas as vendas. Pode filtrar por cliente com ds_cliente.
> Cadastrar venda: POST /api/index.php/vendas
> Campos: id_cliente, forma_pagamento, produtos (array com id_produto, qtd, valorVenda).
> Excluir venda: DELETE /api/index.php/vendas
> Enviar id_venda no corpo da requisição.

### Explicação

Um cliente pode ter várias vendas.
Cada venda possui exatamente uma forma de pagamento.
Cada venda pode ter vários produtos, registrados em venda_produto com quantidade e valor unitário.
Ao cadastrar uma venda, o estoque do produto deve será reduzido de acordo com a quantidade vendida.

### Observações

As vendas validam o estoque no frontend. A baixa real deve ocorrer no backend.
É possível filtrar vendas por cliente.
Integração com CEP via viacep para preenchimento automático de endereço.
Formatação de CPF, telefone e valores aplicada no frontend.
Tabelas e código são organizados para fácil manutenção e extensibilidade.

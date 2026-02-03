<?php

class SellController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function checkAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $this->response(false, "Usuário não autenticado");
            exit;
        }

        return $_SESSION['user_id'];
    }

    public function store(): void
    {
        $idUsuario = $this->checkAuth();

        $data = json_decode(file_get_contents('php://input'), true);

        if (
            empty($data['id_cliente']) ||
            empty($data['forma_pagamento']) ||
            empty($data['produtos'])
        ) {
            $this->response(false, 'Dados da venda inválidos');
            return;
        }

        try {
            /* Insere a venda */
            $stmtVenda = $this->db->prepare("INSERT INTO tab_vendas
                                                (id_cliente, id_pagamento, nr_totalvenda, id_usuario)
                                            VALUES (:cliente, :forma, 0, :id_usuario)
                                                RETURNING id_venda");
            $stmtVenda->execute([
                ':cliente'      => $data['id_cliente'],
                ':forma'        => $data['forma_pagamento'],
                ':id_usuario'   => $idUsuario
            ]);

            $idVenda = $stmtVenda->fetchColumn();
            $totalVenda = 0;

            /* Insert Tab Vendas/Produtos */
            $stmtProdutoVenda = $this->db->prepare("INSERT INTO tab_produtos_venda
                                                        (id_venda, id_produto, nr_qtd_venda)
                                                    VALUES (:venda, :produto, :qtd)");

            /* Seleciona o valor/estoque */
            $stmtProduto = $this->db->prepare("SELECT nr_valor, nr_quantidade
                                                FROM tab_produtos
                                                    WHERE id_produto = :produto");

            $stmtBaixa = $this->db->prepare("UPDATE tab_produtos
                                                SET nr_quantidade = nr_quantidade - :qtd
                                            WHERE id_produto = :produto");
            /* Produtos */
            foreach ($data['produtos'] as $p) {

                $stmtProduto->execute([':produto' => $p['id_produto']]);
                $produto = $stmtProduto->fetch(PDO::FETCH_OBJ);

                if (!$produto || $produto->nr_quantidade < $p['qtd']) {
                    throw new Exception('Estoque insuficiente');
                }

                $subtotal = $produto->nr_valor * $p['qtd'];
                $totalVenda += $subtotal;

                $stmtProdutoVenda->execute([
                    ':venda'   => $idVenda,
                    ':produto' => $p['id_produto'],
                    ':qtd'     => $p['qtd']
                ]);

                $stmtBaixa->execute([
                    ':produto' => $p['id_produto'],
                    ':qtd'     => $p['qtd']
                ]);
            }

            /* Atualiza total */
            $stmtTotal = $this->db->prepare("
                UPDATE tab_vendas
                   SET nr_totalvenda = :total
                 WHERE id_venda = :venda
            ");
            $stmtTotal->execute([
                ':total' => $totalVenda,
                ':venda' => $idVenda
            ]);

            $this->response(true, 'Venda realizada com sucesso', [
                'id_venda' => $idVenda,
                'total' => number_format($totalVenda, 2, ',', '.')
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->response(false, $e->getMessage());
        }
    }

    private function response(bool $success, string $message, array $extra = [])
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    public function show(): void
    {
        $this->checkAuth();
        $ds_cliente = trim($_GET['ds_cliente'] ?? '');
        $id_venda   = $_GET['id_venda'] ?? null;

        try {
            /* Edição */
            if ($id_venda) {
                $stmt = $this->db->prepare("SELECT
                                                v.id_venda,
                                                v.id_cliente,
                                                v.id_pagamento
                                            FROM tab_vendas v
                                                WHERE v.id_venda = :id_venda");
                $stmt->execute([':id_venda' => $id_venda]);
                $venda = $stmt->fetch(PDO::FETCH_OBJ);
                if (!$venda) {
                    $this->response(false, 'Venda não encontrada');
                    return;
                }

                /* Produtos da venda */
                $stmt = $this->db->prepare("SELECT
                                                pv.id_produto,
                                                p.ds_produto,
                                                pv.nr_qtd_venda,
                                                p.nr_valor AS nr_valor_unitario
                                            FROM tab_produtos_venda pv
                                                INNER JOIN tab_produtos p ON p.id_produto = pv.id_produto
                                            WHERE pv.id_venda = :id_venda");
                $stmt->execute([':id_venda' => $id_venda]);
                $produtos = $stmt->fetchAll(PDO::FETCH_OBJ);
                echo json_encode([
                    'id_venda'     => $venda->id_venda,
                    'id_cliente'   => $venda->id_cliente,
                    'id_pagamento' => $venda->id_pagamento,
                    'produtos'     => $produtos
                ]);
                return;
            }

            /* Listagem de Dados */
            $selectVendas = "SELECT 
                                v.id_venda,
                                c.ds_nome,
                                to_char(v.dt_venda, 'DD/MM/YYYY') as dt_venda,
                                p.ds_pagamento,
                                v.nr_totalvenda
                            FROM tab_vendas v
                                INNER JOIN tab_clientes c ON v.id_cliente = c.id_cliente
                                INNER JOIN tab_pagamentos p ON v.id_pagamento = p.id_pagamento
                                WHERE 1=1";
            $params = [];
            if ($ds_cliente !== '') {
                $selectVendas .= " AND UPPER(c.ds_nome) LIKE UPPER(:ds_cliente)";
                $params[':ds_cliente'] = "%{$ds_cliente}%";
            }
            $selectVendas .= " ORDER BY c.ds_nome ASC";

            $stmt = $this->db->prepare($selectVendas);
            $stmt->execute($params);
            $vendas = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (empty($vendas)) {
                $this->response(false, "Nenhuma venda encontrada");
                return;
            }

            echo json_encode($vendas);
        } catch (PDOException $e) {
            $this->response(false, "Erro ao buscar vendas", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function delete(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);
        $id_venda = intval($data['id_venda'] ?? 0);

        if ($id_venda <= 0) {
            $this->response(false, "ID da venda inválido");
            return;
        }

        try {
            $this->db->beginTransaction();
            /* Buscar produtos da venda */
            $stmt = $this->db->prepare("SELECT id_produto, nr_qtd_venda
                                            FROM tab_produtos_venda
                                        WHERE id_venda = :id_venda");
            $stmt->bindValue(':id_venda', $id_venda, PDO::PARAM_INT);
            $stmt->execute();
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /* Devolver estoque */
            foreach ($produtos as $p) {
                $updateEstoque = $this->db->prepare("UPDATE tab_produtos
                                                        SET nr_quantidade = nr_quantidade + :qtd
                                                    WHERE id_produto = :id_produto");
                $updateEstoque->bindValue(':qtd', $p['nr_qtd_venda'], PDO::PARAM_INT);
                $updateEstoque->bindValue(':id_produto', $p['id_produto'], PDO::PARAM_INT);
                $updateEstoque->execute();
            }

            /* Delete da venda */
            $stmt = $this->db->prepare("DELETE FROM tab_produtos_venda WHERE id_venda = :id");
            $stmt->bindValue(':id', $id_venda, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM tab_vendas WHERE id_venda = :id");
            $stmt->bindValue(':id', $id_venda, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();

            $this->response(true, "Venda excluída e estoque restaurado com sucesso");
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->response(false, "Erro ao excluir venda", [
                "error" => $e->getMessage()
            ]);
        }
    }
}

<?php

class PaymentController
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
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['formaPagamento']) ||
            empty($data['qtdParcelas'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        try {
            /* 🔎 Verifica duplicidade */
            $checkSql = "SELECT 1
                            FROM tab_pagamentos
                                WHERE ds_pagamento = :formaPagamento
                            LIMIT 1";
            $check = $this->db->prepare($checkSql);
            $check->bindValue(':formaPagamento', $data['formaPagamento']);
            $check->execute();

            if ($check->fetch()) {
                $this->response(false, "Forma de pagemento já cadastrada com este nome");
                return;
            }

            if (strtoupper($data['formaPagamento']) === 'PIX' && $data['qtdParcelas'] > 1) {
                $this->response(false, "Forma de pagemento só pode ser cadastrada com uma parcela!");
                return;
            }

            if ((strtoupper($data['formaPagamento']) === 'DÉBITO' || strtoupper($data['formaPagamento']) === 'CARTÃO DE DÉBITO') && $data['qtdParcelas'] > 1) {
                $this->response(false, "Forma de pagamento só pode ser cadastrada com uma parcela!");
            }

            /* 📝 Insert */
            $insertPagamento = "INSERT INTO tab_pagamentos
                            (
                                ds_pagamento,
                                nr_parcelas
                            )
                            VALUES
                            (
                                UPPER(:formaPagamento),
                                :qtdParcelas
                            )";
            $stmt = $this->db->prepare($insertPagamento);
            $stmt->bindValue(':formaPagamento', $data['formaPagamento']);
            $stmt->bindValue(':qtdParcelas', $data['qtdParcelas']);

            $stmt->execute();
            $this->response(true, "Forma de pagamento cadastrada com sucesso");
        } catch (PDOException $e) {

            if ($e->getCode() === '23505') {
                $this->response(false, "Forma de pagamento já cadastrada (Nome duplicado)");
                return;
            }

            $this->response(false, "Erro ao cadastrar forma de pagamento", [
                "error" => $e->getMessage()
            ]);
        }
    }

    private function response(bool $success, string $message, array $extra = []): void
    {
        echo json_encode(array_merge([
            "success" => $success,
            "message" => $message
        ], $extra));
        exit;
    }

    public function index()
    {
        $this->checkAuth();

        $formaPagamento = $_GET['formaPagamento'] ?? '';

        $selectPagamentos = "SELECT id_pagamento, ds_pagamento, nr_parcelas
                                FROM tab_pagamentos 
                                    WHERE 1=1";
        $params = [];
        if (!empty($formaPagamento)) {
            $selectPagamentos .= " AND UPPER(ds_pagamento) LIKE UPPER(:formaPagamento)";
            $params[':formaPagamento'] = "%$formaPagamento%";
        }
        $selectPagamentos .= " ORDER BY ds_pagamento ASC";

        try {
            $stmt = $this->db->prepare($selectPagamentos);
            $stmt->execute($params);
            $pagamentos = $stmt->fetchAll(PDO::FETCH_OBJ);

            echo json_encode($pagamentos);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao listar formas de pagamento",
                "error" => $e->getMessage()
            ]);
        }
    }


    public function delete(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);
        $id_pagamento = intval($data['id_pagamento'] ?? 0);

        if ($id_pagamento <= 0) {
            $this->response(false, "ID de pagamento inválido");
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM tab_pagamentos WHERE id_pagamento = :id");
            $stmt->bindValue(':id', $id_pagamento, PDO::PARAM_INT);
            $stmt->execute();

            $this->response(true, "Forma de pagamento excluída com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao excluir forma de pagamento", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function show(): void
    {
        $this->checkAuth();

        $id_pagamento = intval($_GET['id_pagamento'] ?? 0);

        try {
            $selectPagamento = "SELECT 
                                    id_pagamento,
                                    ds_pagamento,
                                    nr_parcelas
                                FROM tab_pagamentos
                                    WHERE id_pagamento = :id";
            $stmt = $this->db->prepare($selectPagamento);
            $stmt->bindValue(':id', $id_pagamento, PDO::PARAM_INT);
            $stmt->execute();

            $pagamento = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$pagamento) {
                $this->response(false, "Forma de pagamento não encontrada");
                return;
            }

            echo json_encode($pagamento);
        } catch (PDOException $e) {
            $this->response(false, "Erro ao buscar formas de pagamento", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function update(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['id_pagamento']) ||
            empty($data['formaPagamento']) ||
            empty($data['qtdParcelas'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        if (strtoupper($data['formaPagamento']) === 'PIX' && $data['qtdParcelas'] > 1) {
            $this->response(false, "Forma de pagemento só pode ser cadastrada com uma parcela!");
            return;
        }

        if ((strtoupper($data['formaPagamento']) === 'DÉBITO' || strtoupper($data['formaPagamento']) === 'CARTÃO DE DÉBITO') && $data['qtdParcelas'] > 1) {
            $this->response(false, "Forma de pagamento só pode ser cadastrada com uma parcela!");
        }

        try {
            $updatePagamento = "UPDATE tab_pagamentos SET
                        ds_pagamento     = UPPER(:formaPagamento),
                        nr_parcelas  = :qtdParcelas
                    WHERE id_pagamento = :id";
            $stmt = $this->db->prepare($updatePagamento);
            $stmt->execute([
                ':formaPagamento'     => $data['formaPagamento'],
                ':qtdParcelas' => $data['qtdParcelas'] ?? null,
                ':id'       => $data['id_pagamento']
            ]);

            $this->response(true, "Forma de pagamento atualizada com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao atualizar forma de pagamento", [
                "error" => $e->getMessage()
            ]);
        }
    }
}

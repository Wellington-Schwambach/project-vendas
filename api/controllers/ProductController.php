<?php

class ProductController
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

    private function formatValor(string $valor): float
    {
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        return $valor;
    }

    public function store(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['nomeProduto']) ||
            empty($data['qtdProduto']) ||
            empty($data['valorProduto'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        try {
            /* Verifica duplicidade */
            $checkSql = "SELECT 1
                            FROM tab_produtos
                                WHERE ds_produto = :nomeProduto
                            LIMIT 1";
            $check = $this->db->prepare($checkSql);
            $check->bindValue(':nomeProduto', $data['nomeProduto']);
            $check->execute();

            if ($check->fetch()) {
                $this->response(false, "Produto já cadastrado com este Nome");
                return;
            }

            $valorProduto = $this->formatValor($data['valorProduto']);
            if ($data['qtdProduto'] < 0) {
                $this->response(false, "Quantidade inválida");
            }
            if ($valorProduto < 0) {
                $this->response(false, "Valor inválido");
            }

            /* Insert */
            $insertProduto = "INSERT INTO tab_produtos
                            (
                                ds_produto,
                                nr_quantidade,
                                nr_valor
                            )
                            VALUES
                            (
                                UPPER(:nomeProduto),
                                :qtdProduto,
                                :valorProduto
                            )";
            $stmt = $this->db->prepare($insertProduto);
            $stmt->bindValue(':nomeProduto', $data['nomeProduto']);
            $stmt->bindValue(':qtdProduto', $data['qtdProduto']);
            $stmt->bindValue(':valorProduto', $valorProduto);

            $stmt->execute();
            $this->response(true, "Produto cadastrado com sucesso");
        } catch (PDOException $e) {

            if ($e->getCode() === '23505') {
                $this->response(false, "Produto já cadastrado (Nome duplicado)");
                return;
            }

            $this->response(false, "Erro ao cadastrar produto", [
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

        $nomeProduto = $_GET['nomeProduto'] ?? '';
        $selectProdutos = "SELECT id_produto, ds_produto, nr_quantidade, nr_valor
                                FROM tab_produtos 
                                    WHERE 1=1";
        $params = [];
        if (!empty($nomeProduto)) {
            $selectProdutos .= " AND UPPER(ds_produto) LIKE UPPER(:nomeProduto)";
            $params[':nomeProduto'] = "%$nomeProduto%";
        }
        $selectProdutos .= " ORDER BY ds_produto ASC";

        try {
            $stmt = $this->db->prepare($selectProdutos);
            $stmt->execute($params);
            $produtos = $stmt->fetchAll(PDO::FETCH_OBJ);

            echo json_encode($produtos);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao listar produtos",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function delete(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);
        $id_produto = intval($data['id_produto'] ?? 0);

        if ($id_produto <= 0) {
            $this->response(false, "ID de produto inválido");
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM tab_produtos WHERE id_produto = :id");
            $stmt->bindValue(':id', $id_produto, PDO::PARAM_INT);
            $stmt->execute();

            $this->response(true, "Produto excluído com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao excluir produto", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function show(): void
    {
        $this->checkAuth();

        $id_produto = intval($_GET['id_produto'] ?? 0);

        try {
            $selectProduto = "SELECT 
                                id_produto,
                                ds_produto,
                                nr_quantidade,
                                nr_valor
                            FROM tab_produtos
                                WHERE id_produto = :id";
            $stmt = $this->db->prepare($selectProduto);
            $stmt->bindValue(':id', $id_produto, PDO::PARAM_INT);
            $stmt->execute();

            $produto = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$produto) {
                $this->response(false, "Produto não encontrado");
                return;
            }

            echo json_encode($produto);
        } catch (PDOException $e) {
            $this->response(false, "Erro ao buscar produtos", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function update(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['id_produto']) ||
            empty($data['nomeProduto']) ||
            empty($data['qtdProduto']) ||
            empty($data['valorProduto'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        $valorProduto = $this->formatValor($data['valorProduto']);
        if ($data['qtdProduto'] < 0) {
            $this->response(false, "Quantidade inválida");
        }
        if ($valorProduto < 0) {
            $this->response(false, "Valor inválido");
        }

        try {
            $updateProduto = "UPDATE tab_produtos SET
                        ds_produto     = UPPER(:nomeProduto),
                        nr_quantidade  = :qtdProduto,
                        nr_valor       = :valorProduto
                    WHERE id_produto = :id";
            $stmt = $this->db->prepare($updateProduto);
            $stmt->execute([
                ':nomeProduto'     => $data['nomeProduto'],
                ':qtdProduto' => $data['qtdProduto'] ?? 0,
                ':valorProduto'      => $valorProduto,
                ':id'       => $data['id_produto']
            ]);

            $this->response(true, "Produto atualizado com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao atualizar produto", [
                "error" => $e->getMessage()
            ]);
        }
    }
}

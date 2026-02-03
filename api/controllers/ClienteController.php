<?php

class ClienteController
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
        /* Sessão obrigatória */
        $idUsuario = $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['nome']) ||
            empty($data['cpf']) ||
            empty($data['cep'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        try {
            /* 🔎 Verifica duplicidade */
            $checkSql = "SELECT 1
                            FROM tab_clientes
                                WHERE nr_cpf = :cpf
                            LIMIT 1";
            $check = $this->db->prepare($checkSql);
            $check->bindValue(':cpf', $data['cpf']);
            $check->execute();

            if ($check->fetch()) {
                $this->response(false, "Cliente já cadastrado com este CPF");
                return;
            }

            /* Validações de Váriavel */
            $numero = isset($data['numero']) && trim($data['numero']) !== ''
                ? $data['numero']
                : 'S/N';

            /* 📝 Insert */
            $insertCliente = "INSERT INTO tab_clientes
                            (
                                id_usuario,
                                ds_nome,
                                nr_cpf,
                                ds_endereco,
                                ds_bairro,
                                nr_numero,
                                nr_telefone,
                                nr_cep,
                                ds_email
                            )
                            VALUES
                            (
                                :id_usuario,
                                UPPER(:nome),
                                :cpf,
                                UPPER(:endereco),
                                UPPER(:bairro),
                                :numero,
                                :telefone,
                                :cep,
                                :email
                            )";
            $stmt = $this->db->prepare($insertCliente);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $data['nome']);
            $stmt->bindValue(':cpf', $data['cpf']);
            $stmt->bindValue(':endereco', $data['endereco'] ?? null);
            $stmt->bindValue(':bairro', $data['bairro'] ?? null);
            $stmt->bindValue(':numero', $numero);
            $stmt->bindValue(':telefone', $data['telefone'] ?? null);
            $stmt->bindValue(':cep', $data['cep']);
            $stmt->bindValue(':email', $data['email'] ?? null);

            $stmt->execute();
            $this->response(true, "Cliente cadastrado com sucesso");
        } catch (PDOException $e) {

            if ($e->getCode() === '23505') {
                $this->response(false, "Cliente já cadastrado (CPF ou Nome duplicado)");
                return;
            }

            $this->response(false, "Erro ao cadastrar cliente", [
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
        $nome = $_GET['nome'] ?? '';
        $cpf  = $_GET['cpf'] ?? '';

        $sql = "SELECT id_cliente, ds_nome, nr_cpf, nr_telefone, 
                        ds_bairro||', '||ds_endereco||' - '||nr_numero as ds_endereco,
                        nr_cep
                    FROM tab_clientes 
                        WHERE 1=1";

        $params = [];
        if (!empty($nome)) {
            $sql .= " AND ds_nome LIKE UPPER(:nome)";
            $params[':nome'] = "%$nome%";
        }
        if (!empty($cpf)) {
            $sql .= " AND REPLACE(REPLACE(nr_cpf, '.', ''), '-', '') LIKE REPLACE(REPLACE(:cpf, '.', ''), '-', '')";
            $params[':cpf'] = "%$cpf%";
        }
        $sql .= " ORDER BY ds_nome ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_OBJ);

            echo json_encode($clientes);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao listar clientes",
                "error" => $e->getMessage()
            ]);
        }
    }


    public function delete(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_cliente = intval($data['id_cliente'] ?? 0);

        if ($id_cliente <= 0) {
            $this->response(false, "ID de cliente inválido");
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM tab_clientes WHERE id_cliente = :id");
            $stmt->bindValue(':id', $id_cliente, PDO::PARAM_INT);
            $stmt->execute();

            $this->response(true, "Cliente excluído com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao excluir cliente", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function show(): void
    {
        $id_cliente = intval($_GET['id_cliente'] ?? 0);

        try {
            $selectCliente = "SELECT 
                                id_cliente,
                                ds_nome,
                                nr_cpf,
                                nr_telefone,
                                nr_cep,
                                ds_endereco,
                                ds_bairro,
                                nr_numero,
                                ds_email
                            FROM tab_clientes
                                WHERE id_cliente = :id";
            $stmt = $this->db->prepare($selectCliente);
            $stmt->bindValue(':id', $id_cliente, PDO::PARAM_INT);
            $stmt->execute();

            $cliente = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$cliente) {
                $this->response(false, "Cliente não encontrado");
                return;
            }

            echo json_encode($cliente);
        } catch (PDOException $e) {
            $this->response(false, "Erro ao buscar cliente", [
                "error" => $e->getMessage()
            ]);
        }
    }

    public function update(): void
    {
        $idUsuario = $this->checkAuth();

        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['id_cliente']) ||
            empty($data['nome']) ||
            empty($data['cpf']) ||
            empty($data['cep'])
        ) {
            $this->response(false, "Campos obrigatórios não preenchidos");
            return;
        }

        $numero = isset($data['numero']) && trim($data['numero']) !== ''
            ? $data['numero']
            : 'S/N';
        try {
            $updateCliente = "UPDATE tab_clientes SET
                        ds_nome     = UPPER(:nome),
                        nr_telefone = :telefone,
                        nr_cep      = :cep,
                        ds_endereco = UPPER(:endereco),
                        ds_bairro   = UPPER(:bairro),
                        nr_numero   = :numero,
                        ds_email    = :email,
                        id_usuario  = :idUsuario
                    WHERE id_cliente = :id";
            $stmt = $this->db->prepare($updateCliente);
            $stmt->execute([
                ':nome'     => $data['nome'],
                ':telefone' => $data['telefone'] ?? null,
                ':cep'      => $data['cep'],
                ':endereco' => $data['endereco'] ?? null,
                ':bairro'   => $data['bairro'] ?? null,
                ':numero'   => $numero,
                ':email'    => $data['email'] ?? null,
                ':id'       => $data['id_cliente'],
                ':idUsuario'=> $idUsuario
            ]);

            $this->response(true, "Cliente atualizado com sucesso");
        } catch (PDOException $e) {
            $this->response(false, "Erro ao atualizar cliente", [
                "error" => $e->getMessage()
            ]);
        }
    }
}

<?php

require_once __DIR__ . "/../config/database.php";

class AuthService
{
    public function authenticate($usuario, $senha)
    {
        $db = Database::getConnection();

        $selectUsuario = "SELECT id_user, ds_usuario, ds_password, id_cliente
                                FROM tab_usuarios
                            WHERE ds_usuario = :usuario
                                LIMIT 1";
        $stmt = $db->prepare($selectUsuario);
        $stmt->bindValue(":usuario", $usuario, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            return [
                "success" => false,
                "message" => "Usuário não encontrado"
            ];
        }

        if (!password_verify($senha, $user->ds_password)) {
            return [
                "success" => false,
                "message" => "Senha inválida"
            ];
        }
        session_regenerate_id(true);

        $_SESSION["user_id"]   = $user->id_user;
        $_SESSION["usuario"]   = $user->ds_usuario;
        $_SESSION["cliente"]   = $user->id_cliente;

        return [
            "success" => true,
            "message" => "Login realizado com sucesso"
        ];
    }
}

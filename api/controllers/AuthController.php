<?php

require_once __DIR__ . "/../services/AuthService.php";

class AuthController
{
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->usuario) || empty($data->senha)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Usuário e senha são obrigatórios"
            ]);
            return;
        }

        $authService = new AuthService();
        $result = $authService->authenticate(
            $data->usuario,
            $data->senha
        );

        echo json_encode($result);
    }
}

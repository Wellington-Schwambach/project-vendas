<?php

class Database
{
    private static $conn;

    public static function getConnection()
    {
        if (!self::$conn) {
            self::$conn = new PDO(
                "pgsql:host=localhost;port=5432;dbname=project_zucchetti", // Aqui deve ser inserido o nome do seu banco de dados
                "postgres",
                "exemplo123", // Aqui deve ser inserida a senha do seu banco de dados
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                ]
            );
        }

        return self::$conn;
    }
}

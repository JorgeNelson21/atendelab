<?php
// app/Controllers/PessoasController.php

class PessoasController 
{
    private PDO $pdo;

    public function __construct()
    {
        // Importa o arquivo de configuração e injeta a conexão PDO
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // 1. Operação: Listar todas as pessoas
    public function listar(): void
    {
        header("Content-Type: application/json; charset=utf-8");
        
        $sql = 'SELECT id, nome, documento, telefone, curso, periodo, status FROM pessoas ORDER BY id DESC';
        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
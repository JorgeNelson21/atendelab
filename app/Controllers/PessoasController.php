<?php
// app/Controllers/PessoasController.php

class PessoasController 
{
    private PDO $pdo;

    public function __construct()
    {
        // Importa o arquivo de configuração e injeta a conexão PDO (Aula 02 - Item 9)
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // 1. Operação: Listar todas as pessoas (Aula 02 - Atividade Item 20)
    public function listar(): void
    {
        header("Content-Type: application/json; charset=utf-8");
        
        $sql = 'SELECT id, nome, documento, telefone, curso, periodo, status FROM pessoas ORDER BY id DESC';
        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // 2. Operação: Buscar uma pessoa específica por ID
    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        // Lê e valida o ID recebido por GET (Aula 02 - Item 9)
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        // Consulta parametrizada para evitar SQL Injection (Aula 02 - Item 9)
        $sql = 'SELECT id, nome, documento, telefone, curso, periodo, status FROM pessoas WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // 3. Operação: Cadastrar uma nova pessoa (POST)
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Coleta e limpa os dados recebidos via $_POST (Aula 02 - Item 9)
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        // Validação de campos obrigatórios específicos (Regra de Negócio RN12)
        if ($nome === '' || $documento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e Documento (CPF/Matrícula) são obrigatórios.']);
            return;
        }

        // Whitelist para garantir integridade do campo status (Aula 02 - Item 13)
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, documento, telefone, curso, periodo, status) 
                    VALUES (:nome, :documento, :telefone, :curso, :periodo, :status)';
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':curso', $curso);
            $stmt->bindValue(':periodo', $periodo);
            $stmt->bindValue(':status', $status);

            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            // Trata erro de documento duplicado (Chave UNIQUE definida no banco)
            if ($e->getCode() == 23000) {
                echo json_encode(['erro' => 'Este documento já está cadastrado no sistema.']);
            } else {
                echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
            }
        }
    }

    // 4. Operação: Atualizar os dados de uma pessoa (POST)
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $documento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome e documento são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas 
                    SET nome = :nome, documento = :documento, telefone = :telefone, 
                        curso = :curso, periodo = :periodo, status = :status 
                    WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':curso', $curso);
            $stmt->bindValue(':periodo', $periodo);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode(['mensagem' => 'Dados da pessoa atualizados com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            if ($e->getCode() == 23000) {
                echo json_encode(['erro' => 'Este documento já está sendo usado por outra pessoa.']);
            } else {
                echo json_encode(['erro' => 'Erro ao atualizar dados.']);
            }
        }
    }

    // 5. Operação: Inativar/Excluir pessoa (POST)
    // Aplicação estrita da Regra de Negócio RN11 (Inativação em vez de exclusão física)
    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            // Mudando o status para 'inativo' para preservar o histórico (RN11)
            $sql = "UPDATE pessoas SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso no histórico.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao alterar o status da pessoa.']);
        }
    }
}
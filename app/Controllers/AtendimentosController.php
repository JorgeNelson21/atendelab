<?php
// app/Controllers/AtendimentosController.php

class AtendimentosController 
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // 1. Operação: Listar atendimentos utilizando JOIN (Requisito RF10)
    public function listar(): void
    {
        header("Content-Type: application/json; charset=utf-8");
        
        // Query com JOIN para buscar os nomes reais ao invés de IDs soltos
        $sql = 'SELECT 
                    a.id, 
                    p.nome AS pessoa_atendida, 
                    t.nome AS tipo_atendimento, 
                    u.nome AS usuario_responsavel, 
                    a.data_atendimento, 
                    a.hora_atendimento, 
                    a.descricao, 
                    a.observacao, 
                    a.status 
                FROM atendimentos a
                JOIN pessoas p ON a.pessoa_id = p.id
                JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
                JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.id DESC';
                
        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // 2. Operação: Visualizar detalhes de um atendimento específico
    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT a.*, p.nome AS pessoa_nome, t.nome AS tipo_nome, u.nome AS usuario_nome 
                FROM atendimentos a
                JOIN pessoas p ON a.pessoa_id = p.id
                JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = :id';
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // 3. Operação: Registrar novo atendimento (POST)
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Recebe os IDs das tabelas que já populamos anteriormente
        $pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipo_atendimento_id = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        
        $data_atendimento = $_POST['data_atendimento'] ?? date('Y-m-d');
        $hora_atendimento = $_POST['hora_atendimento'] ?? date('H:i:s');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'aberto';

        // Validações obrigatórias (RN02, RN03, RN04 e RN12)
        if (!$pessoa_id || !$tipo_atendimento_id || !$usuario_id || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Pessoa, Tipo, Usuário responsável e Descrição são obrigatórios.']);
            return;
        }

        // Validação de status permitidos (RN05)
        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos (pessoa_id, tipo_atendimento_id, usuario_id, data_atendimento, hora_atendimento, descricao, status) 
                    VALUES (:pessoa_id, :tipo_atendimento_id, :usuario_id, :data_atendimento, :hora_atendimento, :descricao, :status)';
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':data_atendimento', $data_atendimento);
            $stmt->bindValue(':hora_atendimento', $hora_atendimento);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);

            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento. Verifique se os IDs informados existem.']);
        }
    }

    // 4. Operação: Alterar apenas o Status/Observação do atendimento (POST - RF11 / RN06)
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $observacao = trim($_POST['observacao'] ?? '');

        if (!$id || $status === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e Status são obrigatórios para atualização.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos SET status = :status, observacao = :observacao WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':observacao', $observacao);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status do atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status do atendimento.']);
        }
    }
}
<?php
// routes.php - Roteador principal do AtendeLab

// 1. Carrega todos os controllers estruturados
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';

// 2. Define o controller e a ação pela URL
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// 3. Roteamento de Usuários
if ($controller === 'usuarios') {
    $usuariosController = new UsuariosController();
    switch ($action) {
        case 'listar': $usuariosController->listar(); break;
        case 'buscar': $usuariosController->buscarPorId(); break;
        case 'criar': $usuariosController->criar(); break;
        case 'atualizar': $usuariosController->atualizar(); break;
        case 'excluir': $usuariosController->excluir(); break;
        default: http_response_code(404); echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// 4. Roteamento de Pessoas
} else if ($controller === 'pessoas') {
    $pessoasController = new PessoasController();
    switch ($action) {
        case 'listar': $pessoasController->listar(); break;
        case 'buscar': $pessoasController->buscarPorId(); break;
        case 'criar': $pessoasController->criar(); break;
        case 'atualizar': $pessoasController->atualizar(); break;
        case 'excluir': $pessoasController->excluir(); break;
        default: http_response_code(404); echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// 5. Roteamento de Tipos de Atendimentos
} else if ($controller === 'tipos-atendimento') {
    $tiposController = new TiposAtendimentosController();
    switch ($action) {
        case 'listar': $tiposController->listar(); break;
        case 'buscar': $tiposController->buscarPorId(); break;
        case 'criar': $tiposController->criar(); break;
        case 'atualizar': $tiposController->atualizar(); break;
        case 'excluir': $tiposController->excluir(); break;
        default: http_response_code(404); echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// 6. Roteamento do Módulo Principal de Atendimentos
} else if ($controller === 'atendimentos') {
    $atendimentosController = new AtendimentosController();
    switch ($action) {
        case 'listar': $atendimentosController->listar(); break;
        case 'buscar': $atendimentosController->buscarPorId(); break;
        case 'criar': $atendimentosController->criar(); break;
        case 'atualizar': $atendimentosController->atualizar(); break; // Atualiza status/observação
        default: http_response_code(404); echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// 7. Rota Padrão
} else {
    echo '<h1>AtendeLab</h1>';
    echo '<p>Projeto em execução. Use as rotas do Thunder Client para realizar os testes do CRUD.</p>';
}
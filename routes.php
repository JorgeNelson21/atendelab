<?php
// routes.php - Roteador principal do AtendeLab

// 1. Carrega os controllers necessários
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';

// 2. Define o controller e a ação pela query string da URL
// Exemplo: ?controller=usuarios&action=listar
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// 3. Roteamento de Usuários
if ($controller === 'usuarios') {
    $usuariosController = new UsuariosController();
    
    switch ($action) {
        case 'listar':
            $usuariosController->listar();
            break;
        case 'buscar':
            $usuariosController->buscarPorId();
            break;
        case 'criar':
            $usuariosController->criar();
            break;
        case 'atualizar':
            $usuariosController->atualizar();
            break;
        case 'excluir':
            $usuariosController->excluir();
            break;
        default:
            http_response_code(404);
            echo json_encode(['erro' => 'Ação de usuários não encontrada.']);
            break;
    }

// 4. Roteamento de Pessoas (Desafio da Aula 02)
} else if ($controller === 'pessoas') {
    $pessoasController = new PessoasController();
    
    switch ($action) {
        case 'listar':
            $pessoasController->listar();
            break;
        case 'buscar':
            $pessoasController->buscarPorId();
            break;
        case 'criar':
            $pessoasController->criar();
            break;
        case 'atualizar':
            $pessoasController->atualizar();
            break;
        case 'excluir':
            $pessoasController->excluir();
            break;
        default:
            http_response_code(404);
            echo json_encode(['erro' => 'Ação de pessoas não encontrada.']);
            break;
    }

// 5. Rota Padrão (Home / Tela de Entrada Básica)
} else {
    echo '<h1>AtendeLab</h1>';
    echo '<p>Projeto em execução. Use os parâmetros na URL para testar os endpoints.</p>';
}
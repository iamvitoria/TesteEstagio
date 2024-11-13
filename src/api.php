<?php

require_once 'task.php';
header('Content-Type: application/json');

// Rota para obter todas as tarefas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/tasks') {
    $tasks = Task::getAllTasks();
    echo json_encode($tasks);
}

// Rota para obter uma tarefa específica pelo ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/\/tasks\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $task = Task::getTaskById($matches[1]);
    echo json_encode($task);
}

// Rota para adicionar uma nova tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/tasks') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        $nome = $data['nome'];
        $custo = $data['custo'];
        $data_limite = $data['data_limite'];
        $ordem = 1;  // Defina a ordem como 1 ou outra lógica
        $id = Task::addTask($nome, $custo, $data_limite, $ordem);
        echo json_encode(['id' => $id, 'message' => 'Tarefa adicionada com sucesso']);
    } else {
        echo json_encode(['error' => 'Dados inválidos']);
    }
}

// Rota para editar uma tarefa
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('/\/tasks\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        $id = $matches[1];
        $nome = $data['nome'];
        $custo = $data['custo'];
        $data_limite = $data['data_limite'];
        Task::editTask($id, $nome, $custo, $data_limite);
        echo json_encode(['message' => 'Tarefa atualizada com sucesso']);
    } else {
        echo json_encode(['error' => 'Dados inválidos']);
    }
}

// Rota para deletar uma tarefa
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/\/tasks\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    Task::deleteTask($matches[1]);
    echo json_encode(['message' => 'Tarefa deletada']);
}

// Rota para reordenar tarefas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('/\/tasks\/reorder\/(\d+)\/(up|down)/', $_SERVER['REQUEST_URI'], $matches)) {
    $id = $matches[1];
    $direction = $matches[2];
    Task::reorderTask($id, $direction);
    echo json_encode(['message' => 'Tarefa reordenada com sucesso']);
}

?>

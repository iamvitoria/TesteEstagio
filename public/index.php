<?php
// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'sistema_de_tarefas';
$username = 'root';  // Substitua pelo seu usuário do banco, se necessário
$password = '';  // Substitua pela sua senha do banco, se necessário

try {
    // Estabelecendo conexão com o banco de dados usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Definindo o tipo de conteúdo como JSON
    header('Content-Type: application/json');

    // Ação recebida via URL
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'tasks') {
            // Recuperar todas as tarefas
            $stmt = $pdo->query('SELECT * FROM tarefa ORDER BY ordem');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
        } elseif (isset($_GET['id'])) {
            // Recuperar uma tarefa específica
            $stmt = $pdo->prepare('SELECT * FROM tarefa WHERE idTarefa = :id');
            $stmt->execute(['id' => $_GET['id']]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($task);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Adicionar uma nova tarefa
        if ($action === 'tasks') {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare('INSERT INTO tarefa (nome, custo, dataLimite, ordem) VALUES (:nome, :custo, :dataLimite, (SELECT IFNULL(MAX(ordem), 0) + 1 FROM tarefa))');
            $stmt->execute([
                'nome' => $data['nome'],
                'custo' => $data['custo'],
                'dataLimite' => $data['data_limite']
            ]);
            echo json_encode(['status' => 'success']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Atualizar uma tarefa
        if (isset($_GET['id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare('UPDATE tarefa SET nome = :nome, custo = :custo, dataLimite = :dataLimite WHERE idTarefa = :id');
            $stmt->execute([
                'nome' => $data['nome'],
                'custo' => $data['custo'],
                'dataLimite' => $data['data_limite'],
                'id' => $_GET['id']
            ]);
            echo json_encode(['status' => 'success']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Excluir uma tarefa
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('DELETE FROM tarefa WHERE idTarefa = :id');
            $stmt->execute(['id' => $_GET['id']]);
            echo json_encode(['status' => 'success']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'reorder') {
        // Reordenar as tarefas
        if (isset($_GET['id']) && isset($_GET['direction'])) {
            $taskId = $_GET['id'];
            $direction = $_GET['direction'];
    
            try {
                // Lógica de reordenamento (ajustada para simplificar)
                if ($direction === 'up') {
                    // Mover para cima
                    $stmt = $pdo->prepare('UPDATE tarefa SET ordem = ordem - 1 WHERE idTarefa = :id');
                    $stmt->execute(['id' => $taskId]);
                } elseif ($direction === 'down') {
                    // Mover para baixo
                    $stmt = $pdo->prepare('UPDATE tarefa SET ordem = ordem + 1 WHERE idTarefa = :id');
                    $stmt->execute(['id' => $taskId]);
                }
    
                // Retornar uma resposta de sucesso
                echo json_encode(['status' => 'success']);
            } catch (PDOException $e) {
                // Se ocorrer um erro, retornar uma resposta de erro
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos']);
        }
    }    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

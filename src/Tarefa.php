<?php

require_once 'config.php';

class Task {
    // Função para obter todas as tarefas
    public static function getAllTasks() {
        global $pdo;
        $sql = "SELECT * FROM tarefa ORDER BY ordem";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Retorna todas as tarefas como um array
    }

    // Função para obter uma tarefa específica
    public static function getTaskById($id) {
        global $pdo;
        $sql = "SELECT * FROM tarefa WHERE idTarefa = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Retorna a tarefa como um array
    }

    // Função para adicionar uma nova tarefa
    public static function addTask($nome, $custo, $data_limite, $ordem) {
        global $pdo;
        $sql = "INSERT INTO tarefa (nome, custo, dataLimite, ordem) VALUES (:nome, :custo, :data_limite, :ordem)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':custo', $custo);
        $stmt->bindParam(':data_limite', $data_limite);
        $stmt->bindParam(':ordem', $ordem);
        $stmt->execute();
        return $pdo->lastInsertId();  // Retorna o ID da tarefa recém-criada
    }

    // Função para editar uma tarefa
    public static function editTask($id, $nome, $custo, $data_limite) {
        global $pdo;
        $sql = "UPDATE tarefa SET nome = :nome, custo = :custo, dataLimite = :data_limite WHERE idTarefa = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':custo', $custo);
        $stmt->bindParam(':data_limite', $data_limite);
        $stmt->execute();
    }

    // Função para deletar uma tarefa
    public static function deleteTask($id) {
        global $pdo;
        $sql = "DELETE FROM tarefa WHERE idTarefa = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Função para reordenar as tarefas
    public static function reorderTask($id, $direction) {
        global $pdo;
        if ($direction === 'up') {
            $sql = "SELECT * FROM tarefa WHERE ordem < (SELECT ordem FROM tarefa WHERE idTarefa = :id) ORDER BY ordem DESC LIMIT 1";
        } else {
            $sql = "SELECT * FROM tarefa WHERE ordem > (SELECT ordem FROM tarefa WHERE idTarefa = :id) ORDER BY ordem ASC LIMIT 1";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            $sql = "UPDATE tarefa SET ordem = :new_ordem WHERE idTarefa = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':new_ordem', $task['ordem']);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $sql = "UPDATE tarefa SET ordem = :old_ordem WHERE idTarefa = :task_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':old_ordem', $task['ordem']);
            $stmt->bindParam(':task_id', $task['idTarefa']);
            $stmt->execute();
        }
    }
}
?>

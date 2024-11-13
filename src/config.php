<?php

$host = 'localhost';  // Ou a URL do seu banco de dados
$dbname = 'sistema_de_tarefas';
$username = 'root@localhost';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Echo para verificar se a conexão foi bem-sucedida
    echo "Conectado ao banco de dados!";
} catch (PDOException $e) {
    echo "Erro ao conectar: " . $e->getMessage();
}
?>

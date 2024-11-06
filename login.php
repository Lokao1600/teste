<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Adicionando log para debug
    error_log("Tentativa de login - Username: " . $username);

    try {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Log para verificar se encontrou o usuário
        error_log("Usuário encontrado: " . ($user ? "Sim" : "Não"));
        
        if ($user) {
            // Log da senha armazenada
            error_log("Senha armazenada: " . $user['password']);
            
            // Verificação de senha em texto puro
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login realizado com sucesso!'
                ]);
            } else {
                error_log("Senha incorreta para o usuário: " . $username);
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuário ou senha incorretos!'
                ]);
            }
        } else {
            error_log("Usuário não encontrado: " . $username);
            echo json_encode([
                'success' => false,
                'message' => 'Usuário ou senha incorretos!'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao realizar login: ' . $e->getMessage()
        ]);
    }
}
?>
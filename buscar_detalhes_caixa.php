<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT SUM(quantidade) as quantidade_total
            FROM pecas
            WHERE caixa_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'quantidade_total' => (int)$resultado['quantidade_total']
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID não fornecido'
    ]);
}
?>
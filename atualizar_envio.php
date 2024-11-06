<?php
require_once 'config.php'; // Arquivo com a configuração da conexão com o banco de dados

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['envio'])) {
    $id = $data['id'];
    $envio = $data['envio'];

    try {
        $stmt = $conn->prepare("UPDATE caixas SET envio = :envio WHERE id = :id");
        $stmt->bindParam(':envio', $envio);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}
?>
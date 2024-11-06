<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Inserir caixa
        $stmt = $conn->prepare("INSERT INTO caixas (numero_caixa) VALUES (?)");
        $stmt->execute([$_POST['numerocaixa']]);
        $caixa_id = $conn->lastInsertId();

        // Inserir peças
        $stmt = $conn->prepare("INSERT INTO pecas (caixa_id, numero_peca, tamanho, quantidade, localizacao) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['pecas'] as $peca) {
            $peca_data = json_decode($peca, true);
            $stmt->execute([
                $caixa_id,
                $peca_data['numero'],
                $peca_data['tamanho'],
                $peca_data['quantidade'],
                $peca_data['localizacao']
            ]);
        }

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
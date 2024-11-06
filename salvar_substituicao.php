<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['caixa_id']) || !isset($data['pecas'])) {
        throw new Exception('Dados inválidos recebidos');
    }

    $conn->beginTransaction();

    // Inserir na tabela substituicoes
    $stmt = $conn->prepare("INSERT INTO substituicoes (caixa_id) VALUES (:caixa_id)");
    $stmt->execute(['caixa_id' => $data['caixa_id']]);
    $substituicao_id = $conn->lastInsertId();

    // Inserir as peças substituídas
    $stmt = $conn->prepare("INSERT INTO pecas_substituidas (substituicao_id, numero_peca, tamanho, quantidade, localizacao) VALUES (:substituicao_id, :numero_peca, :tamanho, :quantidade, :localizacao)");

    foreach ($data['pecas'] as $peca) {
        $stmt->execute([
            'substituicao_id' => $substituicao_id,
            'numero_peca' => $peca['numero'],
            'tamanho' => $peca['tamanho'],
            'quantidade' => $peca['quantidade'],
            'localizacao' => $peca['localizacao']
        ]);
    }

    // Buscar os dados atualizados da caixa
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.numero_caixa,
            c.envio,
            GROUP_CONCAT(ps.numero_peca) as numeros_pecas,
            GROUP_CONCAT(ps.tamanho) as tamanhos,
            GROUP_CONCAT(ps.quantidade) as quantidades,
            GROUP_CONCAT(ps.localizacao) as localizacoes
        FROM caixas c
        JOIN substituicoes s ON c.id = s.caixa_id
        JOIN pecas_substituidas ps ON s.id = ps.substituicao_id
        WHERE c.id = :caixa_id AND s.id = :substituicao_id
        GROUP BY c.id, c.numero_caixa, c.envio
    ");
    
    $stmt->execute([
        'caixa_id' => $data['caixa_id'],
        'substituicao_id' => $substituicao_id
    ]);
    
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caixa) {
        throw new Exception('Não foi possível recuperar os dados da caixa após a substituição');
    }

    // Formatar os dados da caixa
    $caixaFormatada = [
        'id' => $caixa['id'],
        'numero_caixa' => $caixa['numero_caixa'],
        'envio' => $caixa['envio'] ?? 'Não',
        'pecas' => array_map(function($numero, $tamanho, $quantidade, $localizacao) {
            return [
                'numero' => $numero,
                'tamanho' => $tamanho,
                'quantidade' => $quantidade,
                'localizacao' => $localizacao
            ];
        }, 
        explode(',', $caixa['numeros_pecas']),
        explode(',', $caixa['tamanhos']),
        explode(',', $caixa['quantidades']),
        explode(',', $caixa['localizacoes']))
    ];

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Substituição registrada com sucesso',
        'caixa' => $caixaFormatada
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    error_log('Erro ao registrar substituição: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao registrar substituição: ' . $e->getMessage()
    ]);
}
?>
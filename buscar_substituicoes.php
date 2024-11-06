<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Primeiro, vamos buscar as caixas com suas peças originais
    $sql = "SELECT 
                s.id as substituicao_id,
                c.id as caixa_id,
                c.numero_caixa,
                c.envio,
                GROUP_CONCAT(p.numero_peca) as pecas_originais_numero,
                GROUP_CONCAT(p.tamanho) as pecas_originais_tamanho,
                GROUP_CONCAT(p.quantidade) as pecas_originais_quantidade,
                GROUP_CONCAT(p.localizacao) as pecas_originais_localizacao
            FROM substituicoes s
            JOIN caixas c ON s.caixa_id = c.id
            JOIN pecas p ON c.id = p.caixa_id
            GROUP BY s.id, c.id, c.numero_caixa, c.envio";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $caixasOriginais = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agora, vamos buscar as peças substituídas
    $sql2 = "SELECT 
                s.id as substituicao_id,
                ps.numero_peca,
                ps.tamanho,
                ps.quantidade,
                ps.localizacao
            FROM substituicoes s
            JOIN pecas_substituidas ps ON s.id = ps.substituicao_id";

    $stmt = $conn->prepare($sql2);
    $stmt->execute();
    $pecasSubstituidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar os dados
    $caixas = [];
    foreach ($caixasOriginais as $caixa) {
        $caixas[$caixa['substituicao_id']] = [
            'id' => $caixa['caixa_id'],
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
                explode(',', $caixa['pecas_originais_numero']),
                explode(',', $caixa['pecas_originais_tamanho']),
                explode(',', $caixa['pecas_originais_quantidade']),
                explode(',', $caixa['pecas_originais_localizacao'])
            ),
            'pecas_substituidas' => []
        ];
    }

    // Adicionar peças substituídas
    foreach ($pecasSubstituidas as $peca) {
        if (isset($caixas[$peca['substituicao_id']])) {
            $caixas[$peca['substituicao_id']]['pecas_substituidas'][] = [
                'numero' => $peca['numero_peca'],
                'tamanho' => $peca['tamanho'],
                'quantidade' => $peca['quantidade'],
                'localizacao' => $peca['localizacao']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'dados' => array_values($caixas)
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar substituições: ' . $e->getMessage()
    ]);
}
?>
<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $sql = "SELECT 
                c.id,
                c.numero_caixa,
                c.envio,
                c.data_cadastro,
                GROUP_CONCAT(p.numero_peca) as numeros_pecas,
                GROUP_CONCAT(p.tamanho) as tamanhos,
                GROUP_CONCAT(p.quantidade) as quantidades,
                GROUP_CONCAT(p.localizacao) as localizacoes
            FROM caixas c
            LEFT JOIN pecas p ON c.id = p.caixa_id
            WHERE c.id NOT IN (SELECT caixa_id FROM substituicoes)
            GROUP BY c.id, c.numero_caixa, c.envio, c.data_cadastro
            ORDER BY c.data_cadastro DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = array_map(function($row) {
        return [
            'id' => $row['id'],
            'numero_caixa' => $row['numero_caixa'],
            'envio' => $row['envio'] ?? 'Não',
            'data_cadastro' => date('d/m/Y H:i', strtotime($row['data_cadastro'])),
            'pecas' => [
                'numeros' => $row['numeros_pecas'] ? explode(',', $row['numeros_pecas']) : [],
                'tamanhos' => $row['tamanhos'] ? explode(',', $row['tamanhos']) : [],
                'quantidades' => $row['quantidades'] ? explode(',', $row['quantidades']) : [],
                'localizacoes' => $row['localizacoes'] ? explode(',', $row['localizacoes']) : []
            ]
        ];
    }, $dados);

    echo json_encode([
        'success' => true,
        'dados' => $resultado
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
?>
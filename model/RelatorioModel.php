<?php
require_once __DIR__ . '/../service/conexao.php';

class RelatorioModel {
    private $conn;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
    }

    public function getProdutosMaisVendidos($limit = 10) {
        $sql = "SELECT p.id_produto, p.nome, p.imagem, SUM(ip.quantidade) as total_vendido, SUM(ip.subtotal) as receita_total
                FROM produto p
                INNER JOIN item_do_pedido ip ON p.id_produto = ip.id_produto
                INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido
                WHERE ped.status != 'Cancelado'
                GROUP BY p.id_produto
                ORDER BY total_vendido DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientesTopCompradores($limit = 10) {
        $sql = "SELECT pe.IDpessoa, pe.nome, pe.CPF, COUNT(p.id_pedido) as total_pedidos, SUM(p.total) as valor_total
                FROM pessoa pe
                INNER JOIN pedido p ON pe.IDpessoa = p.IDpessoa
                WHERE p.status != 'Cancelado'
                GROUP BY pe.IDpessoa
                ORDER BY valor_total DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFaturamentoPorPeriodo($inicio, $fim) {
        $sql = "SELECT DATE(data_pedido) as data, SUM(total) as faturamento
                FROM pedido
                WHERE status != 'Cancelado' AND DATE(data_pedido) BETWEEN ? AND ?
                GROUP BY DATE(data_pedido)
                ORDER BY data ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$inicio, $fim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFaturamentoMensal($ano = null) {
        if ($ano === null) {
            $ano = date('Y');
        }
        $sql = "SELECT MONTH(data_pedido) as mes, SUM(total) as faturamento
                FROM pedido
                WHERE status != 'Cancelado' AND YEAR(data_pedido) = ?
                GROUP BY MONTH(data_pedido)
                ORDER BY mes ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvaliacoesPorProduto($limit = 10) {
        $sql = "SELECT p.id_produto, p.nome, AVG(a.nota) as nota_media, COUNT(a.id_avaliacao) as total_avaliacoes
                FROM produto p
                LEFT JOIN avaliacao a ON p.id_produto = a.id_produto
                GROUP BY p.id_produto
                HAVING total_avaliacoes > 0
                ORDER BY nota_media DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusPedidos() {
        $sql = "SELECT status, COUNT(*) as quantidade
                FROM pedido
                GROUP BY status
                ORDER BY quantidade DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVendasPorCategoria() {
        $sql = "SELECT c.nome as categoria, COUNT(ip.id_item_do_pedido) as quantidade_vendida, SUM(ip.subtotal) as receita
                FROM categoria c
                INNER JOIN produto p ON c.id_categoria = p.categoria
                INNER JOIN item_do_pedido ip ON p.id_produto = ip.id_produto
                INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido
                WHERE ped.status != 'Cancelado'
                GROUP BY c.id_categoria
                ORDER BY receita DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUsuarios() {
        $sql = "SELECT COUNT(*) as total FROM usuario";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}

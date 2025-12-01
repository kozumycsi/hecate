<?php
require_once __DIR__ . '/../service/conexao.php';

class PedidoModel {
    private $conn;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
    }

    public function getPedidos($statusFilter = null, $dataInicio = null, $dataFim = null, $valorMin = null, $valorMax = null, $clienteTerm = null) {
        $sql = "SELECT p.*, pe.nome as cliente_nome, pe.CPF as cliente_cpf,
                       pg.metodo_pagamento, pg.status_pagamento
                FROM pedido p
                LEFT JOIN pessoa pe ON p.IDpessoa = pe.IDpessoa
                LEFT JOIN pagamento pg ON p.id_pedido = pg.id_pedido
                WHERE 1=1";
        
        $params = [];
        
        if ($statusFilter !== null && $statusFilter !== '') {
            $sql .= " AND p.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($dataInicio !== null && $dataInicio !== '') {
            $sql .= " AND DATE(p.data_pedido) >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim !== null && $dataFim !== '') {
            $sql .= " AND DATE(p.data_pedido) <= ?";
            $params[] = $dataFim;
        }

        if ($valorMin !== null && $valorMin !== '') {
            $sql .= " AND p.total >= ?";
            $params[] = (float)$valorMin;
        }
        if ($valorMax !== null && $valorMax !== '') {
            $sql .= " AND p.total <= ?";
            $params[] = (float)$valorMax;
        }
        if ($clienteTerm !== null && $clienteTerm !== '') {
            $sql .= " AND (pe.nome LIKE ? OR pe.CPF LIKE ?)";
            $like = '%' . $clienteTerm . '%';
            $params[] = $like;
            $params[] = $like;
        }
        
        $sql .= " ORDER BY p.data_pedido DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidoById($id) {
        $sql = "SELECT p.*, pe.nome as cliente_nome, pe.CPF as cliente_cpf, pe.TELEFONE as cliente_telefone,
                       e.logradouro, e.cidade, e.estado, e.cep,
                       pg.metodo_pagamento, pg.status_pagamento, pg.data_pagamento
                FROM pedido p
                LEFT JOIN pessoa pe ON p.IDpessoa = pe.IDpessoa
                LEFT JOIN endereco e ON pe.IDpessoa = e.IDpessoa
                LEFT JOIN pagamento pg ON p.id_pedido = pg.id_pedido
                WHERE p.id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItensDoPedido($pedidoId) {
        $sql = "SELECT ip.*, pr.nome as produto_nome, pr.imagem
                FROM item_do_pedido ip
                LEFT JOIN produto pr ON ip.id_produto = pr.id_produto
                WHERE ip.id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE pedido SET status = ? WHERE id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$status, $id]);
        
        // Se o pedido foi confirmado (não cancelado), atualiza total_vendas dos produtos
        if ($result && $status !== 'Cancelado') {
            $this->updateProductSales($id);
        }
        
        return $result;
    }
    
    /**
     * Atualiza o total_vendas dos produtos de um pedido
     */
    private function updateProductSales($pedidoId) {
        if (!$this->tableExists('item_do_pedido') || !$this->columnExists('produto', 'total_vendas')) {
            return;
        }
        
        require_once __DIR__ . '/ProductModel.php';
        $prodModel = new ProductModel();
        
        $itens = $this->getItensDoPedido($pedidoId);
        foreach ($itens as $item) {
            if (!empty($item['id_produto'])) {
                $prodModel->updateTotalVendas((int)$item['id_produto']);
            }
        }
    }
    
    /**
     * Verifica se uma coluna existe na tabela
     */
    private function columnExists(string $table, string $column): bool {
        try {
            $stmt = $this->conn->query("SELECT DATABASE()");
            $dbName = $stmt->fetchColumn();
            if (!$dbName) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $stmt->execute([$dbName, $table, $column]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Verifica se uma tabela existe
     */
    private function tableExists(string $table): bool {
        try {
            $stmt = $this->conn->query('SELECT DATABASE()');
            $dbName = $stmt->fetchColumn();
            if (!$dbName) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
            $stmt->execute([$dbName, $table]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function cancelPedido($id) {
        return $this->updateStatus($id, 'Cancelado');
    }

    public function countPedidos() {
        $sql = "SELECT COUNT(*) as total FROM pedido";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getTotalVendas() {
        $sql = "SELECT SUM(total) as total_vendas FROM pedido WHERE status != 'Cancelado'";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_vendas'] ?? 0;
    }

    public function getPedidosRecentes($limit = 5) {
        $sql = "SELECT p.*, pe.nome as cliente_nome 
                FROM pedido p
                LEFT JOIN pessoa pe ON p.IDpessoa = pe.IDpessoa
                ORDER BY p.data_pedido DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca pedidos de um usuário específico
     */
    public function getPedidosByUsuario($userId) {
        $sql = "SELECT p.*, pe.nome as cliente_nome,
                       pg.metodo_pagamento, pg.status_pagamento
                FROM pedido p
                LEFT JOIN pessoa pe ON p.IDpessoa = pe.IDpessoa
                LEFT JOIN pagamento pg ON p.id_pedido = pg.id_pedido
                WHERE p.IDpessoa = (SELECT IDpessoa FROM pessoa WHERE idusuario = ?)
                ORDER BY p.data_pedido DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um pedido completo (pedido, itens, endereço, pagamento)
     */
    public function createPedidoCompleto($userId, $produtos, $endereco, $pagamento, $valores) {
        // Configurar timeout para evitar locks (via SQL)
        $this->conn->exec("SET SESSION innodb_lock_wait_timeout = 30");
        
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                // Definir isolamento de transação ANTES de iniciar a transação
                $this->conn->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
                
                $this->conn->beginTransaction();

                // Buscar IDpessoa do usuário
                $sqlPessoa = "SELECT IDpessoa FROM pessoa WHERE idusuario = ? LIMIT 1";
                $stmtPessoa = $this->conn->prepare($sqlPessoa);
                $stmtPessoa->execute([$userId]);
                $pessoa = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
                
                if (!$pessoa) {
                    throw new Exception('Pessoa não encontrada para o usuário');
                }
                
                $idPessoa = (int)$pessoa['IDpessoa'];

                // Criar ou atualizar endereço (fora da transação principal se possível)
                $this->createOrUpdateEndereco($idPessoa, $endereco);

                // Criar pedido
                $sqlPedido = "INSERT INTO pedido (IDpessoa, total, status, data_pedido) VALUES (?, ?, 'Pendente', NOW())";
                $stmtPedido = $this->conn->prepare($sqlPedido);
                $stmtPedido->execute([$idPessoa, $valores['total']]);
                $pedidoId = $this->conn->lastInsertId();

                // Preparar todos os itens de uma vez para inserção em lote
                $itensValues = [];
                $itensParams = [];
                $produtoIds = [];
                
                foreach ($produtos as $index => $produto) {
                    $precoUnitario = (float)$produto['preco'];
                    $quantidade = (int)$produto['quantidade'];
                    $subtotal = $precoUnitario * $quantidade;
                    $produtoId = (int)$produto['id_produto'];
                    
                    $itensValues[] = "(?, ?, ?, ?)";
                    $itensParams[] = $pedidoId;
                    $itensParams[] = $produtoId;
                    $itensParams[] = $quantidade;
                    $itensParams[] = $subtotal;
                    
                    $produtoIds[] = $produtoId;
                }
                
                // Inserir todos os itens de uma vez
                if (!empty($itensValues)) {
                    $sqlItens = "INSERT INTO item_do_pedido (id_pedido, id_produto, quantidade, subtotal) VALUES " . implode(', ', $itensValues);
                    $stmtItens = $this->conn->prepare($sqlItens);
                    $stmtItens->execute($itensParams);
                }

                // Atualizar estoque de todos os produtos de uma vez (mais eficiente)
                foreach ($produtos as $produto) {
                    $produtoId = (int)$produto['id_produto'];
                    $quantidade = (int)$produto['quantidade'];
                    
                    // Usar UPDATE com WHERE para evitar locks desnecessários
                    $sqlEstoque = "UPDATE produto SET estoque = estoque - ? WHERE id_produto = ? AND estoque >= ?";
                    $stmtEstoque = $this->conn->prepare($sqlEstoque);
                    $result = $stmtEstoque->execute([$quantidade, $produtoId, $quantidade]);
                    
                    if (!$result || $stmtEstoque->rowCount() === 0) {
                        throw new Exception("Estoque insuficiente para o produto ID: {$produtoId}");
                    }
                }

                // Criar pagamento
                $sqlPagamento = "INSERT INTO pagamento (id_pedido, metodo_pagamento, status_pagamento, data_pagamento) 
                               VALUES (?, ?, 'Pendente', NOW())";
                $stmtPagamento = $this->conn->prepare($sqlPagamento);
                $metodoPagamento = ucfirst($pagamento['metodo']);
                $stmtPagamento->execute([$pedidoId, $metodoPagamento]);

                // Commit da transação principal
                $this->conn->commit();
                
                // Atualizar total_vendas FORA da transação principal (para evitar locks)
                try {
                    require_once __DIR__ . '/ProductModel.php';
                    $prodModel = new ProductModel();
                    foreach ($produtos as $produto) {
                        $prodModel->updateTotalVendas((int)$produto['id_produto']);
                    }
                } catch (\Throwable $e) {
                    // Log mas não falha o pedido se a atualização de vendas falhar
                    error_log("Erro ao atualizar total_vendas (não crítico): " . $e->getMessage());
                }

                return $pedidoId;
                
            } catch (\PDOException $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                
                // Se for lock timeout, tenta novamente
                if (strpos($e->getMessage(), 'Lock wait timeout') !== false || 
                    strpos($e->getMessage(), '1205') !== false) {
                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        usleep(500000); // Espera 0.5 segundos antes de tentar novamente
                        continue;
                    }
                }
                
                error_log("Erro ao criar pedido completo: " . $e->getMessage());
                throw $e;
            } catch (\Throwable $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log("Erro ao criar pedido completo: " . $e->getMessage());
                throw $e;
            }
        }
        
        throw new Exception('Não foi possível criar o pedido após várias tentativas. Tente novamente.');
    }

    /**
     * Cria ou atualiza endereço do usuário
     */
    private function createOrUpdateEndereco($idPessoa, $endereco) {
        // Verifica se já existe endereço
        $sqlCheck = "SELECT id_endereco FROM endereco WHERE IDpessoa = ? LIMIT 1";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([$idPessoa]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Prepara o logradouro incluindo número e complemento se fornecidos
        $logradouroCompleto = $endereco['logradouro'];
        if (!empty($endereco['numero'])) {
            $logradouroCompleto .= ', ' . $endereco['numero'];
        }
        if (!empty($endereco['complemento'])) {
            $logradouroCompleto .= ' - ' . $endereco['complemento'];
        }
        if (!empty($endereco['bairro'])) {
            $logradouroCompleto .= ' - ' . $endereco['bairro'];
        }

        if ($existing) {
            // Atualiza endereço existente (usando apenas as colunas que existem na tabela)
            $sql = "UPDATE endereco SET 
                    logradouro = ?, 
                    cidade = ?, 
                    estado = ?, 
                    cep = ?
                    WHERE IDpessoa = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $logradouroCompleto,
                $endereco['cidade'],
                $endereco['estado'],
                $endereco['cep'],
                $idPessoa
            ]);
            return (int)$existing['id_endereco'];
        } else {
            // Cria novo endereço (usando apenas as colunas que existem na tabela)
            $sql = "INSERT INTO endereco (IDpessoa, logradouro, cidade, estado, cep) 
                   VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $idPessoa,
                $logradouroCompleto,
                $endereco['cidade'],
                $endereco['estado'],
                $endereco['cep']
            ]);
            return $this->conn->lastInsertId();
        }
    }
}

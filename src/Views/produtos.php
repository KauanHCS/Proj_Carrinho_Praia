<?php
// Verificar função do usuário para controle de acesso
$user = json_decode($_SESSION['user'] ?? '{}', true);
$tipoUsuario = $user['tipo'] ?? $user['tipo_usuario'] ?? 'administrador';
$funcaoUsuario = $user['funcao'] ?? $user['funcao_funcionario'] ?? '';
$isEmployee = ($tipoUsuario === 'funcionario' && ($funcaoUsuario === 'anotar_pedido' || $funcaoUsuario === 'ambos'));
$isAdmin = ($tipoUsuario === 'administrador' || (!$funcaoUsuario && $tipoUsuario !== 'funcionario'));
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam"></i> <?php echo $isEmployee ? 'Reabastecimento de Produtos' : 'Gestão de Produtos'; ?></span>
        <?php if ($isAdmin): ?>
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
            <i class="bi bi-plus"></i> Novo Produto
        </button>
        <?php endif; ?>
    </div>
    
    <?php if ($isEmployee): ?>
    <div class="alert alert-info m-3">
        <i class="bi bi-info-circle"></i>
        <strong>Acesso Limitado:</strong> Você pode apenas reabastecer produtos. Para editar ou criar novos produtos, entre em contato com o administrador.
    </div>
    <?php endif; ?>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Limite Mínimo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="listaProdutos">
                    <?php
                    $conn = getConnection();
                    // Obter o ID do usuário da sessão (session_start já foi chamado no index.php)
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if ($usuarioId) {
                        $sql = "SELECT * FROM produtos WHERE usuario_id = ? ORDER BY nome";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $usuarioId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        // Se não há usuário logado, não mostrar produtos
                        $result = null;
                    }

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $lowStock = $row["quantidade"] <= $row["limite_minimo"] ? 'low-stock' : '';
                            echo '<tr class="' . $lowStock . '">';
                            echo '<td>' . $row["nome"] . '</td>';
                            echo '<td>' . $row["categoria"] . '</td>';
                            echo '<td>R$ ' . number_format($row["preco_venda"], 2, ',', '.') . '</td>';
                            echo '<td>' . $row["quantidade"] . '</td>';
                            echo '<td>' . $row["limite_minimo"] . '</td>';
                            echo '<td>';
                            // Mostrar apenas botão de reabastecer para funcionários que anotam pedidos
                            if ($isEmployee) {
                                echo '<button class="btn btn-sm btn-outline-success" onclick="reabastecerProduto(' . $row["id"] . ')" title="Reabastecer">';
                                echo '<i class="bi bi-arrow-repeat"></i> Reabastecer';
                                echo '</button>';
                            } else {
                                // Admin tem acesso completo
                                echo '<button class="btn btn-sm btn-outline-primary" onclick="editarProduto(' . $row["id"] . ')" title="Editar">';
                                echo '<i class="bi bi-pencil"></i>';
                                echo '</button> ';
                                echo '<button class="btn btn-sm btn-outline-success" onclick="reabastecerProduto(' . $row["id"] . ')" title="Reabastecer">';
                                echo '<i class="bi bi-arrow-repeat"></i>';
                                echo '</button> ';
                                echo '<button class="btn btn-sm btn-outline-danger" onclick="excluirProduto(' . $row["id"] . ')" title="Excluir">';
                                echo '<i class="bi bi-trash"></i>';
                                echo '</button>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center text-muted">Nenhum produto cadastrado</td></tr>';
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
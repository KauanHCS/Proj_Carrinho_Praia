<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam"></i> Gestão de Produtos</span>
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
            <i class="bi bi-plus"></i> Novo Produto
        </button>
    </div>
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
                            echo '<button class="btn btn-sm btn-outline-primary" onclick="editarProduto(' . $row["id"] . ')">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</button>';
                            echo '<button class="btn btn-sm btn-outline-success" onclick="reabastecerProduto(' . $row["id"] . ')">';
                            echo '<i class="bi bi-arrow-repeat"></i>';
                            echo '</button>';
                            echo '<button class="btn btn-sm btn-outline-danger" onclick="excluirProduto(' . $row["id"] . ')">';
                            echo '<i class="bi bi-trash"></i>';
                            echo '</button>';
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
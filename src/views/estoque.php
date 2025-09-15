<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Alertas de Estoque
            </div>
            <div class="card-body">
                <div id="alertasEstoque">
                    <?php
                    $conn = getConnection();
                    // Obter o ID do usuário da sessão (session_start já foi chamado no index.php)
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if ($usuarioId) {
                        $sql = "SELECT * FROM produtos WHERE quantidade <= limite_minimo AND quantidade >= 0 AND usuario_id = ? ORDER BY quantidade ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $usuarioId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $result = null;
                    }

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $alertClass = $row["quantidade"] == 0 ? 'alert-danger' : 'alert-warning';
                            $icon = $row["quantidade"] == 0 ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-triangle';
                            $message = $row["quantidade"] == 0 ? 
                                'SEM ESTOQUE: <strong>' . $row["nome"] . '</strong>' :
                                'Só restam ' . $row["quantidade"] . ' unidades de <strong>' . $row["nome"] . '</strong> (mínimo: ' . $row["limite_minimo"] . ')';
                                
                            echo '<div class="alert ' . $alertClass . '">';
                            echo '<i class="bi ' . $icon . '"></i> ' . $message;
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-muted">Nenhum produto com estoque baixo</p>';
                    }
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Histórico de Movimentações
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="historicoMovimentacoes">
                            <?php
                            $conn = getConnection();
                            // Obter o ID do usuário da sessão (session_start já foi chamado no index.php)
                            $usuarioId = $_SESSION['usuario_id'] ?? null;
                            
                            if ($usuarioId) {
                                $sql = "SELECT m.*, p.nome as produto_nome FROM movimentacoes m 
                                        JOIN produtos p ON m.produto_id = p.id 
                                        WHERE p.usuario_id = ?
                                        ORDER BY m.data DESC LIMIT 10";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $usuarioId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                            } else {
                                $result = null;
                            }

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . date('d/m/Y H:i', strtotime($row["data"])) . '</td>';
                                    echo '<td>' . $row["produto_nome"] . '</td>';
                                    echo '<td>' . ($row["tipo"] == 'entrada' ? 'Entrada' : 'Saída') . '</td>';
                                    echo '<td>' . $row["quantidade"] . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center text-muted">Nenhuma movimentação registrada</td></tr>';
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
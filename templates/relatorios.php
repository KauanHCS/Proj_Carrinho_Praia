<div class="row">
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="card-body">
                <h6><i class="bi bi-currency-dollar"></i> Vendas do Dia</h6>
                <h2 id="totalVendasDia">R$ <?php
                $conn = getConnection();
                $sql = "SELECT SUM(total) as total FROM vendas WHERE DATE(data) = CURDATE()";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo number_format($row['total'] ?? 0, 2, ',', '.');
                $conn->close();
                ?></h2>
                <small><span id="quantidadeItensDia">
                <?php
                $conn = getConnection();
                $sql = "SELECT SUM(iv.quantidade) as total_itens FROM itens_venda iv 
                        JOIN vendas v ON iv.venda_id = v.id 
                        WHERE DATE(v.data) = CURDATE()";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['total_itens'] ?? 0;
                $conn->close();
                ?>
                </span> itens vendidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #0066cc, #0099ff);">
            <div class="card-body">
                <h6><i class="bi bi-graph-up"></i> Lucro Estimado</h6>
                <h2 id="lucroEstimado">R$ <?php
                $conn = getConnection();
                $sql = "SELECT SUM(total) as total FROM vendas WHERE DATE(data) = CURDATE()";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $lucro = ($row['total'] ?? 0) * 0.5; // Margem de 50%
                echo number_format($lucro, 2, ',', '.');
                $conn->close();
                ?></h2>
                <small>Margem média estimada</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #dc3545, #c92066);">
            <div class="card-body">
                <h6><i class="bi bi-box"></i> Estoque Total</h6>
                <h2 id="totalEstoque">
                <?php
                $conn = getConnection();
                $sql = "SELECT SUM(quantidade) as total FROM produtos";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['total'] ?? 0;
                $conn->close();
                ?>
                </h2>
                <small>
                <?php
                $conn = getConnection();
                $sql = "SELECT COUNT(*) as count FROM produtos WHERE quantidade <= limite_minimo";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['count'] ?? 0;
                $conn->close();
                ?>
                produtos abaixo do limite</small>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <i class="bi bi-bar-chart"></i> Produtos Mais Vendidos
    </div>
    <div class="card-body">
        <canvas id="graficoVendas"></canvas>
    </div>
</div>
<?php
set_time_limit(5); // Timeout de 5 segundos
session_start();

echo "<h2>Teste de Sessão e API de Pedidos</h2>";

echo "<h3>1. Dados da Sessão Atual:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['usuario_id'])) {
    echo "<p style='color: red;'>❌ SESSÃO NÃO INICIADA - Faça login primeiro!</p>";
    echo "<p><a href='public/login.php'>Ir para Login</a></p>";
} else {
    echo "<p style='color: green;'>✓ Sessão ativa - Usuário ID: {$_SESSION['usuario_id']}</p>";
    echo "<p><strong>Sessão ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
}

echo "<hr>";
echo "<h3>3. Testar diretamente via JavaScript:</h3>";
?>
<button onclick="testarAPI()">Testar API via JavaScript</button>
<div id="resultado"></div>

<script>
function testarAPI() {
    const resultado = document.getElementById('resultado');
    resultado.innerHTML = '<p>Testando...</p>';
    
    fetch('../src/Controllers/actions.php?action=listarPedidos', {
        method: 'GET',
        credentials: 'same-origin' // IMPORTANTE: incluir cookies/sessão
    })
    .then(response => response.json())
    .then(data => {
        resultado.innerHTML = `
            <h4>Resposta via JavaScript:</h4>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
        
        if (data.success) {
            resultado.innerHTML += `<p style="color: green;">✓ API funcionando! Pedidos: ${data.data.length}</p>`;
        } else {
            resultado.innerHTML += `<p style="color: red;">❌ Erro: ${data.message}</p>`;
        }
    })
    .catch(error => {
        resultado.innerHTML = `<p style="color: red;">❌ Erro de conexão: ${error}</p>`;
    });
}
</script>

<p><a href="public/index.php">Voltar ao Sistema</a></p>

# Script PowerShell para corrigir WAMP automaticamente
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "CORRIGINDO CONFIGURACOES DO WAMP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$httpdConf = "C:\wamp64\bin\apache\apache2.4.62.1\conf\httpd.conf"

if (-not (Test-Path $httpdConf)) {
    Write-Host "ERRO: Arquivo httpd.conf nao encontrado!" -ForegroundColor Red
    Write-Host "Caminho: $httpdConf" -ForegroundColor Yellow
    Read-Host "Pressione Enter para sair"
    exit 1
}

Write-Host "Arquivo encontrado: $httpdConf" -ForegroundColor Green
Write-Host ""

# Fazer backup
$backup = "$httpdConf.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $httpdConf $backup
Write-Host "Backup criado: $backup" -ForegroundColor Green
Write-Host ""

# Ler conteúdo
$content = Get-Content $httpdConf -Raw

# Corrigir Require local para Require all granted
$content = $content -replace 'Require local', 'Require all granted'

# Garantir que o diretório www tem Require all granted
$content = $content -replace '(?s)(<Directory\s+"\$\{INSTALL_DIR\}/www/">.*?)(Require all denied)', '$1# Require all denied`r`n    Require all granted'

# Salvar
Set-Content $httpdConf -Value $content -NoNewline

Write-Host "Configuracao corrigida com sucesso!" -ForegroundColor Green
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PROXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. Reinicie o Apache (WAMP -> Apache -> Service -> Restart)" -ForegroundColor White
Write-Host "2. Habilite mod_headers (WAMP -> Apache -> Apache modules -> headers_module)" -ForegroundColor White
Write-Host "3. Teste no celular: http://SEU_IP/Proj_Carrinho_Praia/public/test-api.php" -ForegroundColor White
Write-Host ""
Read-Host "Pressione Enter para sair"



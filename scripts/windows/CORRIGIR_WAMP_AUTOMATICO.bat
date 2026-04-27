@echo off
echo ========================================
echo CORRIGINDO CONFIGURACOES DO WAMP
echo ========================================
echo.

REM Encontrar o arquivo httpd.conf
set APACHE_DIR=C:\wamp64\bin\apache
for /d %%i in ("%APACHE_DIR%\apache*") do set APACHE_CONF=%%i\conf\httpd.conf

if not exist "%APACHE_CONF%" (
    echo ERRO: Arquivo httpd.conf nao encontrado!
    echo Procurando em: %APACHE_DIR%
    pause
    exit /b 1
)

echo Arquivo encontrado: %APACHE_CONF%
echo.

REM Fazer backup
echo Fazendo backup do httpd.conf...
copy "%APACHE_CONF%" "%APACHE_CONF%.backup" >nul
echo Backup criado: %APACHE_CONF%.backup
echo.

REM Verificar se ja foi corrigido
findstr /C:"Require all granted" "%APACHE_CONF%" >nul
if %errorlevel% == 0 (
    echo AVISO: Configuracao ja existe!
    echo.
) else (
    echo Corrigindo configuracao...
    
    REM Criar arquivo temporario com PowerShell
    powershell -Command "$content = Get-Content '%APACHE_CONF%' -Raw; $content = $content -replace 'Require all denied', '# Require all denied`r`n    Require all granted'; Set-Content '%APACHE_CONF%' -Value $content -NoNewline"
    
    if %errorlevel% == 0 (
        echo Configuracao corrigida com sucesso!
    ) else (
        echo ERRO ao corrigir configuracao!
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo CONFIGURACAO CONCLUIDA!
echo ========================================
echo.
echo IMPORTANTE: Agora voce precisa:
echo 1. Reiniciar o Apache (WAMP -^> Apache -^> Service -^> Restart)
echo 2. Habilitar mod_headers (WAMP -^> Apache -^> Apache modules -^> headers_module)
echo.
pause



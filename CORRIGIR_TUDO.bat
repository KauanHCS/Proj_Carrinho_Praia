@echo off
echo ========================================
echo CORRIGINDO TUDO AUTOMATICAMENTE
echo ========================================
echo.

REM Verificar se está como administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Execute como Administrador!
    echo Clique com botao direito -^> Executar como administrador
    pause
    exit /b 1
)

echo Executando correcoes...
echo.

REM Corrigir WAMP
powershell -ExecutionPolicy Bypass -File "%~dp0CORRIGIR_WAMP.ps1"

echo.
echo ========================================
echo CORRECOES CONCLUIDAS!
echo ========================================
echo.
echo IMPORTANTE:
echo 1. Reinicie o Apache no WAMP
echo 2. Habilite mod_headers
echo 3. Teste a API no celular
echo.
pause



@echo off
echo ========================================
echo TESTANDO API
echo ========================================
echo.

REM Obter IP
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set IP=%%a
    set IP=!IP:~1!
    goto :found
)

:found
echo IP encontrado: %IP%
echo.
echo Testando API...
echo.

REM Abrir no navegador
start http://localhost/Proj_Carrinho_Praia/public/test-api.php

echo.
echo ========================================
echo TESTE NO CELULAR:
echo ========================================
echo http://%IP%/Proj_Carrinho_Praia/public/test-api.php
echo.
echo Copie este link e cole no navegador do celular
echo (mesma rede Wi-Fi)
echo.
pause



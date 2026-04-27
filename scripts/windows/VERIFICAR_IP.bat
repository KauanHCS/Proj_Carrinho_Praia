@echo off
echo ========================================
echo VERIFICANDO IP DA MAQUINA
echo ========================================
echo.

ipconfig | findstr /i "IPv4"

echo.
echo ========================================
echo Use este IP no arquivo api.js
echo ========================================
echo.
echo Arquivo: CarrinhoPraiaMobile\src\services\api.js
echo Linha 6: const API_BASE_URL = 'http://SEU_IP_AQUI/...'
echo.
pause



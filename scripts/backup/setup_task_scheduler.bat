@echo off
REM ============================================
REM Script para configurar backup automático
REM no Task Scheduler do Windows
REM ============================================

echo.
echo ========================================
echo   Configuracao de Backup Automatico
echo ========================================
echo.

REM Definir variáveis
SET TASK_NAME=CarrinhoDePreiaBackup
SET SCRIPT_PATH=%~dp0run_backup.php
SET PHP_PATH=C:\wamp64\bin\php\php8.2.13\php.exe
SET SCHEDULE_TIME=02:00

echo Configurando tarefa agendada...
echo.
echo Nome da tarefa: %TASK_NAME%
echo Script: %SCRIPT_PATH%
echo PHP: %PHP_PATH%
echo Horario: %SCHEDULE_TIME% (diario)
echo.

REM Verificar se o PHP existe
if not exist "%PHP_PATH%" (
    echo [ERRO] PHP nao encontrado em: %PHP_PATH%
    echo.
    echo Por favor, ajuste o caminho do PHP no arquivo setup_task_scheduler.bat
    echo Exemplos de caminhos comuns:
    echo   - C:\wamp64\bin\php\php8.x.x\php.exe
    echo   - C:\xampp\php\php.exe
    echo.
    pause
    exit /b 1
)

REM Verificar se o script existe
if not exist "%SCRIPT_PATH%" (
    echo [ERRO] Script nao encontrado em: %SCRIPT_PATH%
    echo.
    pause
    exit /b 1
)

echo [INFO] Verificando se a tarefa ja existe...
schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Tarefa existente encontrada. Removendo...
    schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1
)

echo [INFO] Criando nova tarefa agendada...
schtasks /create /tn "%TASK_NAME%" /tr "\"%PHP_PATH%\" \"%SCRIPT_PATH%\"" /sc daily /st %SCHEDULE_TIME% /ru SYSTEM /f

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   Configuracao concluida com sucesso!
    echo ========================================
    echo.
    echo A tarefa foi criada com as seguintes configuracoes:
    echo   - Nome: %TASK_NAME%
    echo   - Frequencia: Diaria
    echo   - Horario: %SCHEDULE_TIME%
    echo   - Usuario: SYSTEM
    echo.
    echo Para gerenciar a tarefa:
    echo   1. Abra o "Agendador de Tarefas" do Windows
    echo   2. Procure por "%TASK_NAME%"
    echo   3. Voce pode editar, executar ou desabilitar a tarefa
    echo.
    echo Para testar o backup manualmente, execute:
    echo   php "%SCRIPT_PATH%"
    echo.
) else (
    echo.
    echo [ERRO] Falha ao criar tarefa agendada!
    echo.
    echo Execute este script como Administrador:
    echo   1. Clique com botao direito no arquivo .bat
    echo   2. Selecione "Executar como administrador"
    echo.
)

pause

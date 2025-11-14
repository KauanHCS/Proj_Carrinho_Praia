#!/bin/bash

# ============================================
# Script para configurar backup automático
# via cron job no Linux
# ============================================

echo ""
echo "========================================"
echo "  Configuração de Backup Automático"
echo "========================================"
echo ""

# Definir variáveis
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
BACKUP_SCRIPT="$SCRIPT_DIR/run_backup.php"
CRON_TIME="0 2 * * *"  # 02:00 todos os dias

echo "Configurando cron job..."
echo ""
echo "Script: $BACKUP_SCRIPT"
echo "Horário: $CRON_TIME (02:00 diariamente)"
echo ""

# Verificar se o script existe
if [ ! -f "$BACKUP_SCRIPT" ]; then
    echo "[ERRO] Script não encontrado em: $BACKUP_SCRIPT"
    exit 1
fi

# Verificar se PHP está disponível
if ! command -v php &> /dev/null; then
    echo "[ERRO] PHP não encontrado no PATH"
    echo "Instale o PHP ou adicione ao PATH do sistema"
    exit 1
fi

echo "PHP encontrado: $(which php)"
echo "Versão: $(php -v | head -n 1)"
echo ""

# Criar linha do cron
CRON_LINE="$CRON_TIME cd $PROJECT_ROOT && php $BACKUP_SCRIPT >> $PROJECT_ROOT/logs/backup.log 2>&1"

# Verificar se a linha já existe no crontab
if crontab -l 2>/dev/null | grep -q "$BACKUP_SCRIPT"; then
    echo "[INFO] Entrada existente encontrada no crontab"
    echo "Deseja substituir? (s/n)"
    read -r response
    
    if [[ "$response" != "s" && "$response" != "S" ]]; then
        echo "Operação cancelada"
        exit 0
    fi
    
    # Remover linha antiga
    crontab -l 2>/dev/null | grep -v "$BACKUP_SCRIPT" | crontab -
    echo "[INFO] Entrada antiga removida"
fi

# Adicionar nova linha
(crontab -l 2>/dev/null; echo "$CRON_LINE") | crontab -

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "  Configuração concluída com sucesso!"
    echo "========================================"
    echo ""
    echo "Cron job configurado:"
    echo "  Comando: $CRON_LINE"
    echo ""
    echo "O backup será executado diariamente às 02:00"
    echo "Logs serão salvos em: $PROJECT_ROOT/logs/backup.log"
    echo ""
    echo "Para gerenciar o cron job:"
    echo "  - Ver crontab: crontab -l"
    echo "  - Editar crontab: crontab -e"
    echo "  - Remover crontab: crontab -r"
    echo ""
    echo "Para testar o backup manualmente:"
    echo "  php $BACKUP_SCRIPT"
    echo ""
else
    echo ""
    echo "[ERRO] Falha ao configurar cron job"
    echo "Verifique as permissões e tente novamente"
    exit 1
fi

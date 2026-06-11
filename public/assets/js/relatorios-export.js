// Export functions moved here (relatorios-export.js)
// exportarVendas(), exportarVendasPDF() e exportarProdutos() geram CSV/PDF.

async function exportarVendas() {
    const start = document.getElementById('exportStartDate').value;
    const end = document.getElementById('exportEndDate').value;

    try {
        const resp = await fetch('../src/Controllers/actions.php?action=listarVendasFinanceiro');
        const json = await resp.json();
        if (!json.success) { mostrarAlerta('Erro ao obter vendas para exportação', 'danger'); return; }
        let vendas = json.data || [];

        // Filtro por período (opcional)
        if (start && end) {
            const s = new Date(start); const e = new Date(end);
            vendas = vendas.filter(v => { const d = new Date(v.data); return d >= s && d <= e; });
        } else if (start) {
            const s = new Date(start);
            vendas = vendas.filter(v => new Date(v.data).toDateString() === s.toDateString());
        }

        // Cabeçalho e linhas – usando ; que o Excel em pt-BR interpreta melhor
        const header = [
            'ID','Data','Vendedor','Cliente','Total (R$)','Forma Pagamento','Status','Produtos'
        ];

        const rows = vendas.map(v => [
            v.id,
            v.data,
            v.vendedor_nome || '',
            v.cliente_nome || '',
            (parseFloat(v.total) || 0).toFixed(2).replace('.', ','),
            v.forma_pagamento || '',
            v.status || '',
            (v.produtos_info || '').replace(/\n|\r|;/g, ' ')
        ]);

        let csv = header.join(';') + '\r\n';
        rows.forEach(r => {
            csv += r.map(f => '"' + String(f).replace(/"/g,'""') + '"').join(';') + '\r\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'vendas_export.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);

        mostrarAlerta && mostrarAlerta('Exportação de vendas gerada com sucesso', 'success');
    } catch (err) {
        console.error(err);
        mostrarAlerta && mostrarAlerta('Erro ao exportar vendas', 'danger');
    }
}

function exportarVendasPDF() {
    const start = document.getElementById('exportStartDate').value;
    const end = document.getElementById('exportEndDate').value;
    (async () => {
        try {
        const resp = await fetch('../src/Controllers/actions.php?action=listarVendasRelatorio');
            const json = await resp.json();
            if (!json.success) { mostrarAlerta('Erro ao obter vendas', 'danger'); return; }
            let vendas = json.data || [];

            if (start && end) {
                const s = new Date(start); const e = new Date(end);
                vendas = vendas.filter(v => { const d = new Date(v.data); return d >= s && d <= e; });
            } else if (start) {
                const s = new Date(start);
                vendas = vendas.filter(v => new Date(v.data).toDateString() === s.toDateString());
            }

            let html = '<html><head><title>Export Vendas</title><style>' +
                'body{font-family:Arial,Helvetica,sans-serif}table{width:100%;border-collapse:collapse}' +
                'th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#f4f4f4}' +
                '</style></head><body>';
            html += `<h3>Relatório de Vendas ${start || ''}${end ? ' - ' + end : ''}</h3>`;
            html += '<table><thead><tr>' +
                '<th>ID</th><th>Data</th><th>Vendedor</th><th>Cliente</th><th>Total</th>' +
                '<th>Forma</th><th>Status</th><th>Produtos</th>' +
                '</tr></thead><tbody>';

            vendas.forEach(v => {
                const produtos = (v.produtos_info || '').replace(/\n|\r/g,' ');
                html += `<tr>` +
                    `<td>${v.id}</td>` +
                    `<td>${v.data}</td>` +
                    `<td>${v.vendedor_nome || ''}</td>` +
                    `<td>${v.cliente_nome || ''}</td>` +
                    `<td>R$ ${(parseFloat(v.total) || 0).toFixed(2).replace('.', ',')}</td>` +
                    `<td>${v.forma_pagamento || ''}</td>` +
                    `<td>${v.status || ''}</td>` +
                    `<td>${produtos}</td>` +
                    `</tr>`;
            });

            html += '</tbody></table></body></html>';
            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(() => { w.print(); }, 500);
        } catch (err) {
            console.error(err);
            mostrarAlerta && mostrarAlerta('Erro ao gerar PDF', 'danger');
        }
    })();
}

// Exportar cadastro de produtos em CSV
async function exportarProdutos() {
    try {
        const resp = await fetch('../src/Controllers/actions.php?action=listar_produtos');
        const json = await resp.json();
        if (!json.success) {
            mostrarAlerta && mostrarAlerta('Erro ao obter produtos para exportação', 'danger');
            return;
        }
        const produtos = json.data || [];
        if (!produtos.length) {
            mostrarAlerta && mostrarAlerta('Nenhum produto encontrado para exportar', 'warning');
            return;
        }

        const header = [
            'ID','Nome','Categoria','Preço Compra (R$)','Preço Venda (R$)',
            'Quantidade','Limite Mínimo','Validade','Observações','Usuário ID','Ativo'
        ];

        const rows = produtos.map(p => [
            p.id,
            p.nome || '',
            p.categoria || '',
            (parseFloat(p.preco_compra ?? p.preco) || 0).toFixed(2).replace('.', ','),
            (parseFloat(p.preco_venda ?? p.preco) || 0).toFixed(2).replace('.', ','),
            p.quantidade ?? 0,
            p.limite_minimo ?? 0,
            p.validade || '',
            (p.observacoes || '').replace(/\n|\r|;/g,' '),
            p.usuario_id ?? '',
            typeof p.ativo !== 'undefined' ? p.ativo : 1
        ]);

        let csv = header.join(';') + '\r\n';
        rows.forEach(r => {
            csv += r.map(f => '"' + String(f).replace(/"/g,'""') + '"').join(';') + '\r\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'produtos_export.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);

        mostrarAlerta && mostrarAlerta('Exportação de produtos gerada com sucesso', 'success');
    } catch (err) {
        console.error(err);
        mostrarAlerta && mostrarAlerta('Erro ao exportar produtos', 'danger');
    }
}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Clientes</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; margin:0; background:#fff; color:#111;">
    <div style="max-width:1200px; margin:24px auto; padding:0 14px;">
        <h1 style="margin-bottom:8px;">Painel Admin</h1>
        <p style="margin-top:0; color:#555;">Visão geral dos clientes do SaaS</p>

        <div style="margin:16px 0;">
            <a href="/dashboard">Voltar ao dashboard</a>
        </div>

        <table style="width:100%; border-collapse:collapse;">
            <tr style="text-align:left; border-bottom:1px solid #ddd;">
                <th style="padding:10px 8px;">ID</th>
                <th style="padding:10px 8px;">Empresa</th>
                <th style="padding:10px 8px;">Segmento</th>
                <th style="padding:10px 8px;">Região</th>
                <th style="padding:10px 8px;">Responsável</th>
                <th style="padding:10px 8px;">Email</th>
                <th style="padding:10px 8px;">Budget mensal</th>
                <th style="padding:10px 8px;">Usuários</th>
                <th style="padding:10px 8px;">Métricas</th>
                <th style="padding:10px 8px;">Recomendações</th>
                <th style="padding:10px 8px;">Ações</th>
            </tr>

            @forelse($companies as $company)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px 8px;">{{ $company['id'] }}</td>
                    <td style="padding:10px 8px;">{{ $company['name'] }}</td>
                    <td style="padding:10px 8px;">{{ $company['segment'] ?: '—' }}</td>
                    <td style="padding:10px 8px;">{{ $company['region'] ?: '—' }}</td>
                    <td style="padding:10px 8px;">{{ $company['owner_name'] ?: '—' }}</td>
                    <td style="padding:10px 8px;">{{ $company['owner_email'] ?: '—' }}</td>
                    <td style="padding:10px 8px;">R$ {{ number_format($company['monthly_budget'], 2, ',', '.') }}</td>
                    <td style="padding:10px 8px;">{{ $company['users_count'] }}</td>
                    <td style="padding:10px 8px;">{{ $company['metrics_count'] }}</td>
                    <td style="padding:10px 8px;">{{ $company['recommendations_count'] }}</td>
                    <td style="padding:10px 8px;">
                        <a href="/admin/company/{{ $company['id'] }}">Ver cliente</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="padding:16px 8px;">Nenhuma empresa encontrada.</td>
                </tr>
            @endforelse
        </table>
    </div>
</body>
</html>

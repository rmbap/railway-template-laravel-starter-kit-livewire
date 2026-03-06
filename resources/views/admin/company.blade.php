<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cliente</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; margin:0; background:#fff; color:#111;">
    <div style="max-width:1100px; margin:24px auto; padding:0 14px;">
        <p><a href="/admin">← Voltar ao admin</a></p>

        <h1 style="margin-bottom:8px;">Cliente: {{ $company->name }}</h1>

        <div style="padding:14px; border:1px solid #ddd; border-radius:10px; margin-top:16px;">
            <div style="font-size:18px; font-weight:700; margin-bottom:10px;">Perfil da empresa</div>
            <div><b>Segmento:</b> {{ $company->segment ?: '—' }}</div>
            <div><b>Região:</b> {{ $company->region ?: '—' }}</div>
            <div><b>Ticket médio:</b> R$ {{ number_format((float) ($company->avg_ticket ?? 0), 2, ',', '.') }}</div>
            <div><b>Objetivo principal:</b> {{ $company->primary_goal ?: '—' }}</div>
            <div><b>Budget mensal:</b> R$ {{ number_format((float) ($company->monthly_budget ?? 0), 2, ',', '.') }}</div>
        </div>

        <h2 style="margin-top:24px;">Usuários</h2>
        <ul>
            @forelse($users as $user)
                <li>{{ $user->name }} — {{ $user->email }}</li>
            @empty
                <li>Nenhum usuário encontrado.</li>
            @endforelse
        </ul>

        <h2 style="margin-top:24px;">Métricas recentes</h2>
        <table style="width:100%; border-collapse:collapse;">
            <tr style="text-align:left; border-bottom:1px solid #ddd;">
                <th style="padding:10px 8px;">Data</th>
                <th style="padding:10px 8px;">Canal</th>
                <th style="padding:10px 8px;">Spend</th>
                <th style="padding:10px 8px;">Leads</th>
                <th style="padding:10px 8px;">Receita</th>
            </tr>
            @forelse($metrics as $metric)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px 8px;">{{ $metric->date }}</td>
                    <td style="padding:10px 8px;">{{ $metric->channel }}</td>
                    <td style="padding:10px 8px;">R$ {{ number_format((float) $metric->spend, 2, ',', '.') }}</td>
                    <td style="padding:10px 8px;">{{ $metric->conversions }}</td>
                    <td style="padding:10px 8px;">R$ {{ number_format((float) ($metric->revenue ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:16px 8px;">Nenhuma métrica encontrada.</td>
                </tr>
            @endforelse
        </table>

        <h2 style="margin-top:24px;">Recomendações recentes</h2>
        <table style="width:100%; border-collapse:collapse;">
            <tr style="text-align:left; border-bottom:1px solid #ddd;">
                <th style="padding:10px 8px;">ID</th>
                <th style="padding:10px 8px;">Decisão</th>
                <th style="padding:10px 8px;">Leads estimados</th>
                <th style="padding:10px 8px;">Faturamento estimado</th>
            </tr>
            @forelse($recommendations as $recommendation)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px 8px;">{{ $recommendation->id }}</td>
                    <td style="padding:10px 8px;">{{ $recommendation->decision_text }}</td>
                    <td style="padding:10px 8px;">{{ number_format((float) ($recommendation->expected_leads_gain ?? 0), 2, ',', '.') }}</td>
                    <td style="padding:10px 8px;">R$ {{ number_format((float) ($recommendation->expected_revenue_gain ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:16px 8px;">Nenhuma recomendação encontrada.</td>
                </tr>
            @endforelse
        </table>
    </div>
</body>
</html>

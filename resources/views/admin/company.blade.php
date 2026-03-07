<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:26px;font-weight:700;">Empresa: {{ $company->name }}</div>
            <div style="color:#555;margin-top:4px;">
                Visão detalhada da empresa no sistema
            </div>
        </div>

        <div>
            <a href="/admin"
               style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">
                Voltar ao admin
            </a>
        </div>
    </div>

    <div style="margin-top:18px; display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
        <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
            <div style="font-size:13px;color:#666;">ID da empresa</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ $company->id }}</div>
        </div>

        <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
            <div style="font-size:13px;color:#666;">Qtd. métricas carregadas</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ count($metrics) }}</div>
        </div>

        <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
            <div style="font-size:13px;color:#666;">Qtd. recomendações</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ count($recommendations) }}</div>
        </div>
    </div>

    <div style="margin-top:18px; display:grid; grid-template-columns:1fr; gap:14px;">

        <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
            <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Métricas recentes</div>

            @if(count($metrics) === 0)
                <div>Nenhuma métrica encontrada.</div>
            @else
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #eee;text-align:left;">
                        <th style="padding:10px 8px;">Data</th>
                        <th style="padding:10px 8px;">Canal</th>
                        <th style="padding:10px 8px;">Spend</th>
                        <th style="padding:10px 8px;">Conversões</th>
                        <th style="padding:10px 8px;">Receita</th>
                    </tr>

                    @foreach($metrics as $metric)
                        <tr style="border-bottom:1px solid #f2f2f2;">
                            <td style="padding:10px 8px;">{{ $metric->date }}</td>
                            <td style="padding:10px 8px;">{{ $metric->channel }}</td>
                            <td style="padding:10px 8px;">R$ {{ number_format((float)$metric->spend, 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">{{ $metric->conversions }}</td>
                            <td style="padding:10px 8px;">R$ {{ number_format((float)($metric->revenue ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
            <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Recomendações recentes</div>

            @if(count($recommendations) === 0)
                <div>Nenhuma recomendação encontrada.</div>
            @else
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #eee;text-align:left;">
                        <th style="padding:10px 8px;">Data</th>
                        <th style="padding:10px 8px;">Decisão</th>
                        <th style="padding:10px 8px;">Leads estimados</th>
                        <th style="padding:10px 8px;">Receita estimada</th>
                    </tr>

                    @foreach($recommendations as $rec)
                        <tr style="border-bottom:1px solid #f2f2f2;">
                            <td style="padding:10px 8px;">{{ $rec->created_at }}</td>
                            <td style="padding:10px 8px;">{{ $rec->decision_text }}</td>
                            <td style="padding:10px 8px;">{{ number_format((float)($rec->expected_leads_gain ?? 0), 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">R$ {{ number_format((float)($rec->expected_revenue_gain ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

    </div>

</div>

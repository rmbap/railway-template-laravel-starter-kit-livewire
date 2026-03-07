<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:26px;font-weight:700;">Análise de canais</div>
            <div style="color:#555;margin-top:4px;">Janela: últimos {{ $windowDays }} dias ({{ $start }} → {{ $end }})</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/metrics/create" style="text-decoration:none;padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;">Registrar métricas</a>
            <a href="/dashboard" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">Voltar ao dashboard</a>
        </div>
    </div>

    <div style="margin-top:18px; display:grid; grid-template-columns:1fr; gap:14px;">

        @if(count($channels) === 0)
            <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                <b>Nenhuma métrica encontrada.</b>
                <div style="margin-top:6px;">Clique em "Registrar métricas" para começar.</div>
            </div>
        @else
            <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Resumo por canal</div>

                <table style="width:100%;border-collapse:collapse;">
                    <tr style="text-align:left;border-bottom:1px solid #eee;">
                        <th style="padding:10px 8px;">Canal</th>
                        <th style="padding:10px 8px;">Spend</th>
                        <th style="padding:10px 8px;">Leads</th>
                        <th style="padding:10px 8px;">CPA</th>
                        <th style="padding:10px 8px;">Receita</th>
                        <th style="padding:10px 8px;">ROAS</th>
                        <th style="padding:10px 8px;">Receita / Lead</th>
                    </tr>

                    @foreach($channels as $c)
                        <tr style="border-bottom:1px solid #f2f2f2;">
                            <td style="padding:10px 8px;">{{ $c['channel'] }}</td>
                            <td style="padding:10px 8px;">R$ {{ number_format($c['spend'], 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">{{ $c['conv'] }}</td>
                            <td style="padding:10px 8px;">{{ $c['cpa'] === null ? '—' : 'R$ '.number_format($c['cpa'], 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">R$ {{ number_format($c['revenue'], 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">{{ $c['roas'] === null ? '—' : number_format($c['roas'], 2, ',', '.') }}</td>
                            <td style="padding:10px 8px;">{{ $c['value_per_conversion'] === null ? '—' : 'R$ '.number_format($c['value_per_conversion'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            {!! $recommendationHtml !!}
        @endif

    </div>
</div>

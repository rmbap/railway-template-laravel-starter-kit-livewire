<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">
    <h1>Planner de orçamento</h1>

    <p>
        <b>Objetivo:</b>
        {{ $goal === 'revenue' ? 'Maximizar receita' : 'Maximizar leads' }}
    </p>

    <p><b>Orçamento total:</b> R$ {{ number_format($totalBudget, 2, ',', '.') }}</p>

    <div style="padding:14px;border:1px solid #ddd;border-radius:10px;margin-top:16px;">
        <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Plano sugerido</div>

        <table style="width:100%;border-collapse:collapse;">
            <tr style="text-align:left;border-bottom:1px solid #eee;">
                <th style="padding:10px 8px;">Canal</th>
                <th style="padding:10px 8px;">% do orçamento</th>
                <th style="padding:10px 8px;">Budget sugerido</th>
                <th style="padding:10px 8px;">Receita estimada</th>
                <th style="padding:10px 8px;">Leads estimados</th>
            </tr>

            @foreach($channels as $channel)
                <tr style="border-bottom:1px solid #f2f2f2;">
                    <td style="padding:10px 8px;">{{ $channel['channel'] }}</td>
                    <td style="padding:10px 8px;">{{ number_format($channel['allocation_pct'], 2, ',', '.') }}%</td>
                    <td style="padding:10px 8px;">R$ {{ number_format($channel['allocated_budget'], 2, ',', '.') }}</td>
                    <td style="padding:10px 8px;">R$ {{ number_format($channel['estimated_revenue'], 2, ',', '.') }}</td>
                    <td style="padding:10px 8px;">{{ number_format($channel['estimated_leads'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div style="padding:14px;border:1px solid #ddd;border-radius:10px;margin-top:16px;">
        <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Projeção consolidada</div>
        <div>Leads estimados: <b>{{ number_format($estimatedTotalLeads, 2, ',', '.') }}</b></div>
        <div>Receita estimada: <b>R$ {{ number_format($estimatedTotalRevenue, 2, ',', '.') }}</b></div>
    </div>

    <p style="margin-top:16px;">
        <a href="/planner">Fazer nova simulação</a>
    </p>
</div>

<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:26px;font-weight:700;">Resultado do planner</div>
            <div style="color:#555;margin-top:4px;">
                Objetivo:
                {{ $goal === 'revenue' ? 'Maximizar receita' : 'Maximizar leads' }}
                · Orçamento total: R$ {{ number_format($totalBudget, 2, ',', '.') }}
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/planner" style="text-decoration:none;padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;">
                Nova simulação
            </a>

            <a href="/dashboard" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">
                Voltar ao dashboard
            </a>
        </div>
    </div>

    <div style="margin-top:18px; display:grid; grid-template-columns:1fr; gap:14px;">

        @if(empty($planner['valid']))
            <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                <b>Não foi possível calcular com os dados atuais.</b>
                <div style="margin-top:6px;">Verifique se há métricas suficientes cadastradas para os canais.</div>
            </div>
        @else

            <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Plano sugerido</div>

                <table style="width:100%;border-collapse:collapse;">
                    <tr style="text-align:left;border-bottom:1px solid #eee;">
                        <th style="padding:10px 8px;">Canal</th>
                        <th style="padding:10px 8px;">% do orçamento</th>
                        <th style="padding:10px 8px;">Budget sugerido</th>
                        <th style="padding:10px 8px;">Receita estimada</th>
                        <th style="padding:10px 8px;">Leads estimados</th>
                    </tr>

                    @foreach($planner['channels'] as $channel)
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

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <div style="font-size:13px;color:#666;">Leads estimados</div>
                    <div style="font-size:24px;font-weight:700;margin-top:6px;">
                        {{ number_format($planner['estimated_total_leads'], 2, ',', '.') }}
                    </div>
                </div>

                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <div style="font-size:13px;color:#666;">Receita estimada</div>
                    <div style="font-size:24px;font-weight:700;margin-top:6px;">
                        R$ {{ number_format($planner['estimated_total_revenue'], 2, ',', '.') }}
                    </div>
                </div>
            </div>

        @endif

    </div>

</div>

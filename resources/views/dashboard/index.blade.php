<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:1000px; margin:auto; padding:24px 14px;">

    <h1>Marketing Decision Engine</h1>

    <p>Período analisado: {{ $window_start }} → {{ $window_end }}</p>

    @if($latestEngineRecommendation)
        <div style="margin:20px 0; padding:18px; border:1px solid #ddd; border-radius:14px; background:#fafafa;">
            <div style="font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#666; margin-bottom:8px;">
                Oportunidade detectada pela engine
            </div>

            <div style="font-size:22px; font-weight:700; margin-bottom:8px;">
                {{ $latestEngineRecommendation->decision_text }}
            </div>

            <div style="margin-bottom:6px;">
                <strong>Impacto estimado:</strong>
            </div>

            <div style="font-size:18px; margin-bottom:4px;">
                +{{ number_format((float)($latestEngineRecommendation->expected_leads_gain ?? 0), 2, ',', '.') }} leads
            </div>

            <div style="font-size:18px; margin-bottom:12px;">
                +R$ {{ number_format((float)($latestEngineRecommendation->expected_revenue_gain ?? 0), 2, ',', '.') }} de receita
            </div>

            <a href="/analysis" style="display:inline-block; padding:10px 14px; border-radius:10px; background:#111; color:#fff; text-decoration:none;">
                Ver análise detalhada
            </a>
        </div>
    @endif

    <h2>Resumo por canal</h2>

    <table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;">
        <tr>
            <th>Canal</th>
            <th>Spend</th>
            <th>Leads</th>
            <th>CPA</th>
            <th>Receita</th>
            <th>ROAS</th>
            <th>Receita / Lead</th>
        </tr>

        @foreach($channels as $c)
            <tr>
                <td>{{ $c['channel'] }}</td>
                <td>R$ {{ number_format($c['spend'], 2, ',', '.') }}</td>
                <td>{{ $c['conv'] }}</td>
                <td>
                    @if($c['cpa'])
                        R$ {{ number_format($c['cpa'], 2, ',', '.') }}
                    @else
                        —
                    @endif
                </td>
                <td>R$ {{ number_format($c['revenue'], 2, ',', '.') }}</td>
                <td>
                    @if($c['roas'])
                        {{ number_format($c['roas'], 2, ',', '.') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($c['value_per_conversion'])
                        R$ {{ number_format($c['value_per_conversion'], 2, ',', '.') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <h2 style="margin-top:24px;">Resumo executivo</h2>

    <p>
        Melhor CPA:
        <strong>
            @if($summary['best_cpa'])
                {{ $summary['best_cpa']['channel'] }}
            @else
                —
            @endif
        </strong>
    </p>

    <p>
        Melhor ROAS:
        <strong>
            @if($summary['best_roas'])
                {{ $summary['best_roas']['channel'] }}
            @else
                —
            @endif
        </strong>
    </p>

    <p>
        Maior receita por lead:
        <strong>
            @if($summary['best_value_per_lead'])
                {{ $summary['best_value_per_lead']['channel'] }}
            @else
                —
            @endif
        </strong>
    </p>

    <h2 style="margin-top:24px;">Recomendação instantânea</h2>

    @if($recommendation['has_recommendation'])
        <p>
            Mover verba de
            <strong>{{ $recommendation['worst']['channel'] }}</strong>
            para
            <strong>{{ $recommendation['best']['channel'] }}</strong>
        </p>

        <p>
            Leads adicionais estimados:
            <strong>{{ number_format($recommendation['expected_leads_gain'], 2, ',', '.') }}</strong>
        </p>

        <p>
            Receita adicional estimada:
            <strong>R$ {{ number_format($recommendation['expected_revenue_gain'], 2, ',', '.') }}</strong>
        </p>
    @else
        <p>{{ $recommendation['decision_text'] }}</p>
    @endif

    <br><br>

    <a href="/metrics/create">Registrar métricas</a>
    <br>
    <a href="/analysis">Ver análise detalhada</a>
    <br>
    <a href="/planner">Planner de orçamento</a>
    <br>
    <a href="/admin">Admin</a>

</body>
</html>

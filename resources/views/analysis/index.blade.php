<h1>Análise</h1>

<p>
    Janela analisada:
    {{ $window_start ?? '—' }} → {{ $window_end ?? '—' }}
</p>

@if(!empty($recommendation['has_recommendation']) && !empty($recommendation['best']) && !empty($recommendation['worst']))
    <h2>Recomendação</h2>

    <p>
        Mover verba de <strong>{{ $recommendation['worst']['channel'] }}</strong>
        para <strong>{{ $recommendation['best']['channel'] }}</strong>
    </p>

    <p>
        Leads estimados:
        +{{ number_format((float)($recommendation['expected_leads_gain'] ?? 0), 2, ',', '.') }}
    </p>

    <p>
        Receita estimada:
        +R$ {{ number_format((float)($recommendation['expected_revenue_gain'] ?? 0), 2, ',', '.') }}
    </p>
@else
    <h2>Recomendação</h2>
    <p>{{ $recommendation['decision_text'] ?? 'Sem recomendação no momento.' }}</p>
@endif

<h2>Resumo por canal</h2>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%;">
    <tr>
        <th>Canal</th>
        <th>Spend</th>
        <th>Leads</th>
        <th>CPA</th>
        <th>Receita</th>
        <th>ROAS</th>
    </tr>

    @forelse($channels as $c)
        <tr>
            <td>{{ $c['channel'] }}</td>
            <td>R$ {{ number_format((float)$c['spend'], 2, ',', '.') }}</td>
            <td>{{ $c['conv'] }}</td>
            <td>
                @if(!empty($c['cpa']))
                    R$ {{ number_format((float)$c['cpa'], 2, ',', '.') }}
                @else
                    —
                @endif
            </td>
            <td>R$ {{ number_format((float)$c['revenue'], 2, ',', '.') }}</td>
            <td>
                @if(!empty($c['roas']))
                    {{ number_format((float)$c['roas'], 2, ',', '.') }}
                @else
                    —
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">Nenhuma métrica encontrada.</td>
        </tr>
    @endforelse
</table>

<p style="margin-top:16px;">
    <a href="/dashboard">Voltar ao dashboard</a> |
    <a href="/metrics/create">Registrar métricas</a>
</p>

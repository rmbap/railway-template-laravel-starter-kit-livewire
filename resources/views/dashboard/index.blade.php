@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Marketing Decision Engine</h1>

        <p class="page-subtitle">
            Janela analisada: {{ $window_start }} → {{ $window_end }}
        </p>
    </div>

    <div class="actions">
        <a href="/metrics/create" class="btn btn-primary">
            Registrar métricas
        </a>

        <a href="/analysis" class="btn">
            Ver análise
        </a>
    </div>
</div>


@if($latestEngineRecommendation)

<div class="card opportunity" style="margin-bottom:16px;">

    <span class="pill pill-success">
        Oportunidade detectada pela engine
    </span>

    <div class="opportunity-headline">
        {{ $latestEngineRecommendation->decision_text }}
    </div>

    <div class="opportunity-copy">
        A engine identificou uma possível realocação de verba com impacto positivo estimado.
    </div>

    <div class="impact-row">

        <div class="impact-box">
            <div class="label">
                Leads adicionais
            </div>

            <div class="value">
                +{{ number_format((float)($latestEngineRecommendation->expected_leads_gain ?? 0),2,',','.') }}
            </div>
        </div>

        <div class="impact-box">
            <div class="label">
                Receita adicional
            </div>

            <div class="value">
                +R$ {{ number_format((float)($latestEngineRecommendation->expected_revenue_gain ?? 0),2,',','.') }}
            </div>
        </div>

    </div>

</div>

@endif


<div class="grid grid-3" style="margin-bottom:16px;">

    <div class="card">

        <div class="metric-label">
            Melhor CPA
        </div>

        <div class="metric-value">
            {{ $summary['best_cpa']['channel'] ?? '—' }}
        </div>

    </div>


    <div class="card">

        <div class="metric-label">
            Melhor ROAS
        </div>

        <div class="metric-value">
            {{ $summary['best_roas']['channel'] ?? '—' }}
        </div>

    </div>


    <div class="card">

        <div class="metric-label">
            Maior valor por lead
        </div>

        <div class="metric-value">
            {{ $summary['best_value_per_lead']['channel'] ?? '—' }}
        </div>

    </div>

</div>


<div class="card">

    <h2 class="card-title">
        Performance por canal
    </h2>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Canal</th>
                <th>Spend</th>
                <th>Leads</th>
                <th>CPA</th>
                <th>Receita</th>
                <th>ROAS</th>
                <th>Valor por Lead</th>
            </tr>

            @foreach($channels as $c)

            <tr>

                <td>
                    {{ $c['channel'] }}
                </td>

                <td>
                    R$ {{ number_format($c['spend'],2,',','.') }}
                </td>

                <td>
                    {{ $c['conv'] }}
                </td>

                <td>
                    @if($c['cpa'])
                        R$ {{ number_format($c['cpa'],2,',','.') }}
                    @else
                        —
                    @endif
                </td>

                <td>
                    R$ {{ number_format($c['revenue'],2,',','.') }}
                </td>

                <td>
                    @if($c['roas'])
                        {{ number_format($c['roas'],2,',','.') }}
                    @else
                        —
                    @endif
                </td>

                <td>
                    @if($c['value_per_conversion'])
                        R$ {{ number_format($c['value_per_conversion'],2,',','.') }}
                    @else
                        —
                    @endif
                </td>

            </tr>

            @endforeach

        </table>

    </div>

</div>


<div class="card section-space">

    <h2 class="card-title">
        Recomendação instantânea
    </h2>

    @if($recommendation['has_recommendation'])

        <p>
            Mover orçamento de
            <strong>{{ $recommendation['worst']['channel'] }}</strong>
            para
            <strong>{{ $recommendation['best']['channel'] }}</strong>
        </p>

        <p>
            Leads adicionais estimados:
            <strong>
                {{ number_format($recommendation['expected_leads_gain'],2,',','.') }}
            </strong>
        </p>

        <p>
            Receita adicional estimada:
            <strong>
                R$ {{ number_format($recommendation['expected_revenue_gain'],2,',','.') }}
            </strong>
        </p>

    @else

        <p class="muted">
            {{ $recommendation['decision_text'] }}
        </p>

    @endif

</div>

@endsection

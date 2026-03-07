@extends('layouts.app-shell')

@section('content')

<div class="page-header">
    @if(!empty($latestEngineRecommendation))

<div class="card opportunity">

    <span class="pill pill-success">
        Oportunidade detectada pela engine
    </span>

    <div class="opportunity-headline">
        {{ $latestEngineRecommendation->decision_text }}
    </div>

    <div class="impact-row">

        <div class="impact-box">
            <div class="label">
                Leads adicionais estimados
            </div>

            <div class="value">
                +{{ number_format((float)($latestEngineRecommendation->expected_leads_gain ?? 0),2,',','.') }}
            </div>
        </div>

        <div class="impact-box">
            <div class="label">
                Receita adicional estimada
            </div>

            <div class="value">
                +R$ {{ number_format((float)($latestEngineRecommendation->expected_revenue_gain ?? 0),2,',','.') }}
            </div>
        </div>

    </div>

</div>

@endif
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">
            Janela analisada: {{ $window_start ?? '—' }} → {{ $window_end ?? '—' }}
        </p>
    </div>

    <div class="actions">
        <a href="/metrics/create" class="btn btn-primary">Registrar métricas</a>
        <a href="/analysis" class="btn">Ver análise</a>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Resumo rápido</h2>

    <p>Total de canais: <strong>{{ count($channels ?? []) }}</strong></p>

    <p>
        Melhor CPA:
        <strong>{{ $summary['best_cpa']['channel'] ?? '—' }}</strong>
    </p>

    <p>
        Melhor ROAS:
        <strong>{{ $summary['best_roas']['channel'] ?? '—' }}</strong>
    </p>

    <p>
        Maior valor por lead:
        <strong>{{ $summary['best_value_per_lead']['channel'] ?? '—' }}</strong>
    </p>
</div>

@if(!empty($latestEngineRecommendation))
    <div class="card" style="margin-top:16px;">
        <h2 class="card-title">Última recomendação da engine</h2>

        <p>{{ $latestEngineRecommendation->decision_text }}</p>

        <p>
            +{{ number_format((float)($latestEngineRecommendation->expected_leads_gain ?? 0), 2, ',', '.') }} leads
        </p>

        <p>
            +R$ {{ number_format((float)($latestEngineRecommendation->expected_revenue_gain ?? 0), 2, ',', '.') }}
        </p>
    </div>
@endif

@endsection

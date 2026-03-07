@extends('layouts.app-shell')

@section('content')

<div class="page-header">

    <div>
        <h1 class="page-title">
            Dashboard
        </h1>

        <p class="page-subtitle">
            Visão geral da performance de marketing.
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



@if(!empty($latestEngineRecommendation))

<div class="card opportunity" style="margin-bottom:16px;">

    <span class="pill pill-success">
        Oportunidade detectada pela engine
    </span>

    <div class="opportunity-headline">
        {{ $latestEngineRecommendation->decision_text }}
    </div>

    <div class="impact-row">

        <div class="impact-box">
            <div class="label">
                Leads estimados
            </div>

            <div class="value">
                +{{ number_format((float)($latestEngineRecommendation->expected_leads_gain ?? 0),2,',','.') }}
            </div>
        </div>

        <div class="impact-box">
            <div class="label">
                Receita estimada
            </div>

            <div class="value">
                +R$ {{ number_format((float)($latestEngineRecommendation->expected_revenue_gain ?? 0),2,',','.') }}
            </div>
        </div>

    </div>

</div>

@endif



@if(!empty($channels))

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

                    @if(!empty($c['cpa']))
                        R$ {{ number_format($c['cpa'],2,',','.') }}
                    @else
                        —
                    @endif

                </td>

                <td>
                    R$ {{ number_format($c['revenue'],2,',','.') }}
                </td>

                <td>

                    @if(!empty($c['roas']))
                        {{ number_format($c['roas'],2,',','.') }}
                    @else
                        —
                    @endif

                </td>

            </tr>

            @endforeach

        </table>

    </div>

</div>

@endif


@endsection

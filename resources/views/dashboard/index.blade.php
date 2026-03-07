@extends('layouts.app')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
        <div style="font-size:26px;font-weight:700;">Marketing Decision Engine</div>
        <div style="color:#555;margin-top:4px;">Janela: últimos {{ $windowDays }} dias ({{ $start }} → {{ $end }})</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/metrics/create" style="text-decoration:none;padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;">Registrar métricas</a>
        <a href="/analysis" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">Ver análise</a>
    </div>
</div>

@if(count($channels) > 0)
    <div style="margin-top:18px; display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
        <div class="card">
            <div style="font-size:13px;color:#666;">Melhor CPA</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ $best ? $best['channel'] : '—' }}</div>
        </div>

        <div class="card">
            <div style="font-size:13px;color:#666;">Melhor ROAS</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ $bestRoas ? $bestRoas['channel'] : '—' }}</div>
        </div>

        <div class="card">
            <div style="font-size:13px;color:#666;">Maior receita por lead</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">{{ $bestValuePerLead ? $bestValuePerLead['channel'] : '—' }}</div>
        </div>
    </div>
@endif

<div style="margin-top:18px; display:grid; grid-template-columns:1fr; gap:14px;">

    @if(count($channels) === 0)
        <div class="card">
            <b>Nenhuma métrica encontrada.</b>
            <div style="margin-top:6px;">Clique em "Registrar métricas" para começar.</div>
        </div>
    @else
        <div class="card">
            <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Resumo por canal</div>

            <table style="width:100%;border-collapse:collapse;">
                <tr style="text-align:left;border-bottom:1px solid #eee;">
                    <th style="padding:10px 8px;">Canal</th>
                    <th style="padding:10px 8px;">Spend</th>
                    <th style="padding:10px 8px;">Leads</th>
                    <th style="padding:10px 8px;">CPA</th>
                    <th style="padding:10px 8px;">Receita</th>
                    <th style="padding:10px 8px;">ROAS</th>
                </tr>

                @foreach($channels as $c)
                    <tr style="border-bottom:1px solid #f2f2f2;">
                        <td style="padding:10px 8px;">{{ $c['channel'] }}</td>
                        <td style="padding:10px 8px;">R$ {{ number_format($c['spend'], 2, ',', '.') }}</td>
                        <td style="padding:10px 8px;">{{ $c['conv'] }}</td>
                        <td style="padding:10px 8px;">{{ $c['cpa'] === null ? '—' : 'R$ '.number_format($c['cpa'], 2, ',', '.') }}</td>
                        <td style="padding:10px 8px;">R$ {{ number_format($c['revenue'], 2, ',', '.') }}</td>
                        <td style="padding:10px 8px;">{{ $c['roas'] === null ? '—' : number_format($c['roas'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="card">
            {!! $recommendationHtml !!}
        </div>

        @if($lastRecommendation)
            <div class="card">
                <div style="font-size:18px;"><b>Última recomendação salva</b></div>
                <div style="margin-top:6px;">{{ $lastRecommendation->decision_text }}</div>
                <div style="margin-top:8px;">+ {{ number_format((float) ($lastRecommendation->expected_leads_gain ?? 0), 2, ',', '.') }} leads estimados</div>
                <div>+ R$ {{ number_format((float) ($lastRecommendation->expected_revenue_gain ?? 0), 2, ',', '.') }} de faturamento estimado</div>
            </div>
        @endif
    @endif

</div>

<div style="margin-top:18px;color:#666;">
    <a href="/settings" style="color:#666;">Configurações</a>
</div>

@endsection

<h1>Dashboard</h1>

<h2>Resumo geral</h2>

@php
$totalSpend = 0;
$totalLeads = 0;

foreach($channels as $c){
    $totalSpend += $c['spend'];
    $totalLeads += $c['conv'];
}

$cpa = $totalLeads > 0 ? $totalSpend / $totalLeads : null;
@endphp

<ul>
    <li><strong>Total spend:</strong> R$ {{ number_format($totalSpend,2,',','.') }}</li>
    <li><strong>Total leads:</strong> {{ $totalLeads }}</li>
    <li>
        <strong>CPA médio:</strong>
        @if($cpa)
            R$ {{ number_format($cpa,2,',','.') }}
        @else
            —
        @endif
    </li>
</ul>

<hr>

<h2>Performance por canal</h2>

<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
<tr>
<th>Canal</th>
<th>Spend</th>
<th>Leads</th>
<th>CPA</th>
</tr>

@foreach($channels as $c)

<tr>
<td>{{ $c['channel'] }}</td>
<td>R$ {{ number_format($c['spend'],2,',','.') }}</td>
<td>{{ $c['conv'] }}</td>
<td>
@if($c['cpa'])
R$ {{ number_format($c['cpa'],2,',','.') }}
@endif
</td>
</tr>

@endforeach

</table>

<hr>

<h2>Ações</h2>

<ul>
<li><a href="/metrics/create">Registrar métricas</a></li>
<li><a href="/analysis">Ver análise</a></li>
<li><a href="/planner">Planejar orçamento</a></li>
<li><a href="/admin">Admin</a></li>
</ul>

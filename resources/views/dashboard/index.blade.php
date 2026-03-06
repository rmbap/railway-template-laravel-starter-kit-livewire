<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
</head>

<body style="font-family:Arial; max-width:1000px; margin:auto;">

<h1>Marketing Decision Engine</h1>

<p>Período analisado: {{ $window_start }} → {{ $window_end }}</p>

<h2>Resumo por canal</h2>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
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

<td>R$ {{ number_format($c['spend'],2,',','.') }}</td>

<td>{{ $c['conv'] }}</td>

<td>
@if($c['cpa'])
R$ {{ number_format($c['cpa'],2,',','.') }}
@endif
</td>

<td>
R$ {{ number_format($c['revenue'],2,',','.') }}
</td>

<td>
@if($c['roas'])
{{ number_format($c['roas'],2,',','.') }}
@endif
</td>

<td>
@if($c['value_per_conversion'])
R$ {{ number_format($c['value_per_conversion'],2,',','.') }}
@endif
</td>

</tr>

@endforeach

</table>


<h2>Resumo executivo</h2>

<p>
Melhor CPA:
<strong>
@if($summary['best_cpa'])
{{ $summary['best_cpa']['channel'] }}
@endif
</strong>
</p>

<p>
Melhor ROAS:
<strong>
@if($summary['best_roas'])
{{ $summary['best_roas']['channel'] }}
@endif
</strong>
</p>

<p>
Maior receita por lead:
<strong>
@if($summary['best_value_per_lead'])
{{ $summary['best_value_per_lead']['channel'] }}
@endif
</strong>
</p>


<h2>Recomendação</h2>

@if($recommendation['has_recommendation'])

<p>
Mover verba de
<strong>{{ $recommendation['worst']['channel'] }}</strong>
para
<strong>{{ $recommendation['best']['channel'] }}</strong>
</p>

<p>
Leads adicionais estimados:
<strong>{{ number_format($recommendation['expected_leads_gain'],2,',','.') }}</strong>
</p>

<p>
Receita adicional estimada:
<strong>
R$ {{ number_format($recommendation['expected_revenue_gain'],2,',','.') }}
</strong>
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

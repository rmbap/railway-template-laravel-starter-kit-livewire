<h1>Dashboard</h1>

@if(!$hasCompany)

<p>Você ainda não cadastrou uma empresa.</p>

<p>
<a href="/company/create">Criar empresa</a>
</p>

@else

<h2>Oportunidade detectada</h2>

@if($recommendation['has_recommendation'])

<p>
Mover verba de
<strong>{{ $recommendation['worst']['channel'] }}</strong>
para
<strong>{{ $recommendation['best']['channel'] }}</strong>
</p>

<p>
Leads estimados:
+{{ number_format($recommendation['expected_leads_gain'],2,',','.') }}
</p>

<p>
Receita estimada:
+R$ {{ number_format($recommendation['expected_revenue_gain'],2,',','.') }}
</p>

@else

<p>{{ $recommendation['decision_text'] }}</p>

@endif

<hr>

<h2>Canais</h2>

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

@endif

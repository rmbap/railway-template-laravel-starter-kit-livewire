<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">

<h1>Análise</h1>

<p>Janela: {{ $start }} até {{ $end }}</p>

<table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">

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
<td>{{ $c['channel'] }}</td>
<td>R$ {{ number_format($c['spend'],2,',','.') }}</td>
<td>{{ $c['conv'] }}</td>
<td>
@if($c['cpa'])
R$ {{ number_format($c['cpa'],2,',','.') }}
@else
—
@endif
</td>
<td>R$ {{ number_format($c['revenue'],2,',','.') }}</td>
<td>
@if($c['roas'])
{{ number_format($c['roas'],2,',','.') }}
@else
—
@endif
</td>
</tr>

@endforeach

</table>

<p style="margin-top:16px;">
<a href="/metrics/create">Registrar métricas</a> |
<a href="/dashboard">Voltar</a>
</p>

</div>

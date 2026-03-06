<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">

<h1>Simulação</h1>

<p>Mover {{ $pct }}% de <b>{{ $from }}</b> para <b>{{ $to }}</b></p>

<h2>Antes</h2>

Spend {{ $from }}: R$ {{ number_format($data[$from]['spend'],2,',','.') }}<br>
Spend {{ $to }}: R$ {{ number_format($data[$to]['spend'],2,',','.') }}<br>
Receita combinada: R$ {{ number_format($beforeRevenue,2,',','.') }}

<h2>Depois</h2>

Spend {{ $from }}: R$ {{ number_format($fromNew,2,',','.') }}<br>
Spend {{ $to }}: R$ {{ number_format($toNew,2,',','.') }}<br>
Receita combinada: R$ {{ number_format($afterRevenue,2,',','.') }}

<h2>Impacto estimado</h2>

Leads antes: {{ number_format($beforeLeads,2,',','.') }}<br>
Leads depois: {{ number_format($afterLeads,2,',','.') }}<br>
<b>Δ Leads: {{ number_format($deltaLeads,2,',','.') }}</b><br><br>

Faturamento antes: R$ {{ number_format($beforeRevenue,2,',','.') }}<br>
Faturamento depois: R$ {{ number_format($afterRevenue,2,',','.') }}<br>
<b>Δ Faturamento: R$ {{ number_format($deltaRevenue,2,',','.') }}</b>

<p style="margin-top:16px;">
<a href="/analysis">Voltar</a>
</p>

</div>

<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">

<h1>Registrar métricas</h1>

<form method="POST" action="/metrics/create" style="margin-top:14px;">
@csrf

<label>Data</label><br>
<input type="date" name="date" required style="padding:10px;border:1px solid #ddd;border-radius:10px;">
<br><br>

<label>Canal</label><br>
<input name="channel" placeholder="Google / Meta / TikTok"
required
style="padding:10px;width:100%;max-width:420px;border:1px solid #ddd;border-radius:10px;">
<br><br>

<label>Conversions / Leads</label><br>
<input name="conversions" type="number"
required
style="padding:10px;width:200px;border:1px solid #ddd;border-radius:10px;">
<br><br>

<label>Spend</label><br>
<input name="spend" type="number" step="0.01"
required
style="padding:10px;width:200px;border:1px solid #ddd;border-radius:10px;">
<br><br>

<label>Revenue / Faturamento</label><br>
<input name="revenue" type="number" step="0.01"
style="padding:10px;width:200px;border:1px solid #ddd;border-radius:10px;">
<br><br>

<button type="submit"
style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;">
Salvar
</button>

</form>

<p style="margin-top:16px;">
<a href="/dashboard">Voltar ao dashboard</a> |
<a href="/analysis">Ver análise</a>
</p>

</div>

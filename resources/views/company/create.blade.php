<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">

<h1>Criar empresa</h1>

<form method="POST" action="/company/create" style="margin-top:14px;">
@csrf

<label>Nome da empresa</label><br>

<input 
name="name" 
required 
style="padding:10px; width:100%; max-width:420px; border:1px solid #ddd; border-radius:10px;"
>

<br><br>

<button type="submit"
style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
Salvar
</button>

</form>

</div>

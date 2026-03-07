<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">
    <h1>Planner de orçamento</h1>
    <p>Informe um orçamento total e o objetivo da otimização.</p>

    <form method="POST" action="/planner" style="margin-top:14px;">
        @csrf

        <label>Orçamento total</label><br>
        <input name="total_budget" type="number" step="0.01" required style="padding:10px; width:220px; border:1px solid #ddd; border-radius:10px;"><br><br>

        <label>Objetivo</label><br>
        <select name="goal" style="padding:10px; width:220px; border:1px solid #ddd; border-radius:10px;">
            <option value="revenue">Maximizar receita</option>
            <option value="leads">Maximizar leads</option>
        </select><br><br>

        <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
            Calcular plano
        </button>
    </form>
</div>

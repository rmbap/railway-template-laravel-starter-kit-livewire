<h1>Planner</h1>

<form method="POST" action="/planner">
    @csrf

    <p>
        <label>Orçamento total</label><br>
        <input type="number" step="0.01" name="total_budget" required>
    </p>

    <p>
        <label>Objetivo</label><br>
        <select name="goal" required>
            <option value="revenue">Maximizar receita</option>
            <option value="leads">Maximizar leads</option>
        </select>
    </p>

    <button type="submit">Calcular</button>
</form>

<p><a href="/dashboard">Voltar ao dashboard</a></p>

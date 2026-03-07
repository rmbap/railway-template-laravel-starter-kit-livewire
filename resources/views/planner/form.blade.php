<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:26px;font-weight:700;">Planejamento de orçamento</div>
            <div style="color:#555;margin-top:4px;">Simule como distribuir seu budget entre canais</div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="/dashboard" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">
                Voltar ao dashboard
            </a>
        </div>
    </div>

    <div style="margin-top:20px;padding:18px;border:1px solid #ddd;border-radius:12px;">

        <form method="POST" action="/planner">
            @csrf

            <div style="margin-bottom:14px;">
                <label>Orçamento total</label><br>
                <input type="number" name="budget" step="0.01" required
                style="padding:8px;width:220px;">
            </div>

            <div style="margin-bottom:14px;">
                <label>Canal A</label><br>
                <input type="text" name="channel_a" required>
            </div>

            <div style="margin-bottom:14px;">
                <label>CPA Canal A</label><br>
                <input type="number" name="cpa_a" step="0.01" required>
            </div>

            <div style="margin-bottom:14px;">
                <label>Canal B</label><br>
                <input type="text" name="channel_b" required>
            </div>

            <div style="margin-bottom:14px;">
                <label>CPA Canal B</label><br>
                <input type="number" name="cpa_b" step="0.01" required>
            </div>

            <button type="submit"
            style="margin-top:10px;padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
                Calcular distribuição
            </button>

        </form>

    </div>

</div>

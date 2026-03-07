<div style="background:#fff;border-bottom:1px solid #e5e7eb;">
    <div style="max-width:1000px;margin:0 auto;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">

        <div style="font-size:20px;font-weight:700;">
            Budget Engine
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">

            <a href="/dashboard" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Dashboard
            </a>

            <a href="/metrics/create" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Métricas
            </a>

            <a href="/analysis" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Análise
            </a>

            <a href="/planner" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Planner
            </a>

            <a href="/admin" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Admin
            </a>

        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">

            <a href="/company/create" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Empresa
            </a>

            <a href="/settings" style="text-decoration:none;color:#111;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Configurações
            </a>

            <form method="POST" action="/logout" style="margin:0;">
                @csrf
                <button type="submit" style="padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;">
                    Sair
                </button>
            </form>

        </div>

    </div>
</div>

<div style="background:#fff;border-bottom:1px solid #e5e7eb;">
    <div style="max-width:1000px;margin:0 auto;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:16px;">

        <div style="font-size:20px;font-weight:700;">
            Budget Engine
        </div>

        <button
            type="button"
            id="mobile-menu-toggle"
            onclick="toggleMobileMenu()"
            style="display:none;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:18px;"
        >
            ☰
        </button>

        <div id="desktop-nav" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex:1;flex-wrap:wrap;">

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

    <div id="mobile-menu" style="display:none;border-top:1px solid #e5e7eb;padding:12px 16px;max-width:1000px;margin:0 auto;">

        <div style="display:flex;flex-direction:column;gap:10px;">

            <a href="/dashboard" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Dashboard
            </a>

            <a href="/metrics/create" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Métricas
            </a>

            <a href="/analysis" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Análise
            </a>

            <a href="/planner" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Planner
            </a>

            <a href="/admin" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Admin
            </a>

            <a href="/company/create" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Empresa
            </a>

            <a href="/settings" style="text-decoration:none;color:#111;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;">
                Configurações
            </a>

            <form method="POST" action="/logout" style="margin:0;">
                @csrf
                <button type="submit" style="width:100%;text-align:left;padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;">
                    Sair
                </button>
            </form>

        </div>

    </div>
</div>

<script>
function toggleMobileMenu() {
    var menu = document.getElementById('mobile-menu');

    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
    } else {
        menu.style.display = 'none';
    }
}

function updateTopbarMode() {
    var desktopNav = document.getElementById('desktop-nav');
    var mobileToggle = document.getElementById('mobile-menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');

    if (window.innerWidth <= 768) {
        desktopNav.style.display = 'none';
        mobileToggle.style.display = 'inline-block';
    } else {
        desktopNav.style.display = 'flex';
        mobileToggle.style.display = 'none';
        mobileMenu.style.display = 'none';
    }
}

window.addEventListener('resize', updateTopbarMode);
window.addEventListener('load', updateTopbarMode);
</script>

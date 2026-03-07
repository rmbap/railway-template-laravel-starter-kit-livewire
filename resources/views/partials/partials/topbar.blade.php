<header class="topbar">
    <div class="topbar-inner">

        <a href="/dashboard" style="font-weight:800; text-decoration:none;">
            Budget Engine
        </a>

        <nav class="nav">
            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="/company/create" class="nav-link {{ request()->is('company/create') ? 'active' : '' }}">
                Empresa
            </a>

            <a href="/metrics/create" class="nav-link {{ request()->is('metrics/create') ? 'active' : '' }}">
                Métricas
            </a>

            <a href="/analysis" class="nav-link {{ request()->is('analysis') ? 'active' : '' }}">
                Análise
            </a>

            <a href="/planner" class="nav-link {{ request()->is('planner') ? 'active' : '' }}">
                Planner
            </a>

            <a href="/admin" class="nav-link {{ request()->is('admin') || request()->is('admin/company/*') ? 'active' : '' }}">
                Admin
            </a>
        </nav>

        <form method="POST" action="/logout" style="margin:0;">
            @csrf
            <button type="submit" class="btn">
                Sair
            </button>
        </form>

    </div>
</header>

@php
    $isDashboard = request()->is('dashboard');
    $isCompany = request()->is('company/create');
    $isMetrics = request()->is('metrics/create');
    $isAnalysis = request()->is('analysis');
    $isPlanner = request()->is('planner');
    $isAdmin = request()->is('admin') || request()->is('admin/company/*');
@endphp

<header class="topbar">
    <div class="topbar-inner">

        <div class="brand">
            <a href="/dashboard" class="brand" style="text-decoration:none;">
                <div class="brand-badge">BE</div>
                <div class="brand-copy">
                    <div class="brand-title">Budget Engine</div>
                    <div class="brand-subtitle">Motor de decisão de orçamento</div>
                </div>
            </a>
        </div>

        <nav class="nav">
            <a href="/dashboard" class="nav-link {{ $isDashboard ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="/company/create" class="nav-link {{ $isCompany ? 'active' : '' }}">
                Empresa
            </a>

            <a href="/metrics/create" class="nav-link {{ $isMetrics ? 'active' : '' }}">
                Métricas
            </a>

            <a href="/analysis" class="nav-link {{ $isAnalysis ? 'active' : '' }}">
                Análise
            </a>

            <a href="/planner" class="nav-link {{ $isPlanner ? 'active' : '' }}">
                Planner
            </a>

            <a href="/admin" class="nav-link {{ $isAdmin ? 'active' : '' }}">
                Admin
            </a>
        </nav>

        <div class="nav-actions">
            <a href="/settings/profile" class="nav-link">
                Configurações
            </a>

            <form method="POST" action="/logout" style="margin:0;">
                @csrf
                <button type="submit" class="btn">
                    Sair
                </button>
            </form>
        </div>

    </div>
</header>

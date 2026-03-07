@extends('layouts.app')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
        <div style="font-size:26px;font-weight:700;">Planner de orçamento</div>
        <div style="color:#555;margin-top:4px;">Informe o orçamento total e o objetivo da otimização</div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/dashboard" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">
            Voltar ao dashboard
        </a>
    </div>
</div>

<div style="margin-top:18px;padding:18px;border:1px solid #ddd;border-radius:12px;">
    <form method="POST" action="/planner">
        @csrf

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:6px;">Orçamento total</label>
            <input
                name="total_budget"
                type="number"
                step="0.01"
                required
                style="padding:10px;width:240px;border:1px solid #ddd;border-radius:8px;"
            >
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:6px;">Objetivo</label>
            <select
                name="goal"
                style="padding:10px;width:240px;border:1px solid #ddd;border-radius:8px;"
            >
                <option value="revenue">Maximizar receita</option>
                <option value="leads">Maximizar leads</option>
            </select>
        </div>

        <button
            type="submit"
            style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;"
        >
            Calcular plano
        </button>

    </form>
</div>

@endsection

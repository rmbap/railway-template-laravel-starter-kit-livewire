@extends('layouts.app')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
        <div style="font-size:26px;font-weight:700;">Admin</div>
        <div style="color:#555;margin-top:4px;">
            Empresas cadastradas no sistema
        </div>
    </div>

    <div>
        <a href="/dashboard"
           style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">
            Voltar ao dashboard
        </a>
    </div>
</div>

<div class="card" style="margin-top:18px;">

    @if(count($companies) === 0)

        <div>Nenhuma empresa cadastrada.</div>

    @else

        <table style="width:100%;border-collapse:collapse;">

            <tr style="border-bottom:1px solid #eee;text-align:left;">
                <th style="padding:10px 8px;">Empresa</th>
                <th style="padding:10px 8px;">Criada em</th>
                <th style="padding:10px 8px;">Ação</th>
            </tr>

            @foreach($companies as $company)

                <tr style="border-bottom:1px solid #f2f2f2;">
                    <td style="padding:10px 8px;">{{ $company->name }}</td>
                    <td style="padding:10px 8px;">{{ $company->created_at }}</td>
                    <td style="padding:10px 8px;">
                        <a href="/admin/company/{{ $company->id }}"
                           style="text-decoration:none;padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fff;">
                            Ver empresa
                        </a>
                    </td>
                </tr>

            @endforeach

        </table>

    @endif

</div>

@endsection

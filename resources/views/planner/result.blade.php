<h1>Resultado do Planner</h1>

<p><strong>Objetivo:</strong> {{ $goal === 'revenue' ? 'Maximizar receita' : 'Maximizar leads' }}</p>
<p><strong>Orçamento total:</strong> R$ {{ number_format($totalBudget, 2, ',', '.') }}</p>

@if(empty($planner['valid']))
    <p>Não foi possível calcular com os dados atuais.</p>
@else
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr>
            <th>Canal</th>
            <th>% do orçamento</th>
            <th>Budget sugerido</th>
            <th>Receita estimada</th>
            <th>Leads estimados</th>
        </tr>

        @foreach($planner['channels'] as $channel)
            <tr>
                <td>{{ $channel['channel'] }}</td>
                <td>{{ number_format($channel['allocation_pct'], 2, ',', '.') }}%</td>
                <td>R$ {{ number_format($channel['allocated_budget'], 2, ',', '.') }}</td>
                <td>R$ {{ number_format($channel['estimated_revenue'], 2, ',', '.') }}</td>
                <td>{{ number_format($channel['estimated_leads'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <p style="margin-top:16px;">
        <strong>Leads estimados:</strong>
        {{ number_format($planner['estimated_total_leads'], 2, ',', '.') }}
    </p>

    <p>
        <strong>Receita estimada:</strong>
        R$ {{ number_format($planner['estimated_total_revenue'], 2, ',', '.') }}
    </p>
@endif

<p><a href="/planner">Nova simulação</a></p>
<p><a href="/dashboard">Voltar ao dashboard</a></p>

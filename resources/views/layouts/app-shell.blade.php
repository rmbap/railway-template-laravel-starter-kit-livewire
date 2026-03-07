<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Budget Engine' }}</title>

    <style>
        :root{
            --bg: #f6f7fb;
            --surface: #ffffff;
            --surface-2: #f9fafb;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #111111;
            --primary-contrast: #ffffff;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #065f46;
            --shadow: 0 10px 30px rgba(17,24,39,.06);
            --radius: 16px;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        }

        body {
            line-height: 1.45;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            min-height: 100vh;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }

        .topbar-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-link, .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--text);
            font-size: 14px;
            font-weight: 600;
            transition: .18s ease;
            cursor: pointer;
        }

        .nav-link:hover, .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .nav-link.active,
        .btn-primary {
            background: var(--primary);
            color: var(--primary-contrast);
            border-color: var(--primary);
        }

        .container {
            max-width: 1180px;
            margin: 26px auto;
            padding: 0 18px 40px;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .page-title {
            margin: 0;
            font-size: 34px;
            line-height: 1.05;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .page-subtitle {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .card-title {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 800;
        }

        .metric-label {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .opportunity {
            background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
            border: 1px solid var(--success-border);
        }

        .pill {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pill-success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        .opportunity-headline {
            margin: 12px 0 8px;
            font-size: 26px;
            line-height: 1.12;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .impact-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .impact-box {
            min-width: 180px;
            flex: 1;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .impact-box .label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .impact-box .value {
            font-size: 24px;
            font-weight: 850;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: var(--surface-2);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--muted);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .muted {
            color: var(--muted);
        }

        .section-space {
            margin-top: 16px;
        }

        @@media (max-width: 980px) {
            .grid-3 {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 28px;
            }
        }

        @@media (max-width: 640px) {
            .container {
                padding: 0 14px 28px;
            }

            .page-title {
                font-size: 24px;
            }

            .nav {
                width: 100%;
            }

            .nav-link, .btn {
                width: 100%;
            }

            .actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        @include('partials.topbar')

        <main class="container">
            @yield('content')
        </main>
    </div>
</body>
</html>

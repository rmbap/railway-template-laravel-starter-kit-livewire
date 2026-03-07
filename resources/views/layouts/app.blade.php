<!DOCTYPE html>
<html lang="pt-BR">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Budget Engine</title>

<style>
body{
    font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial;
    margin:0;
    background:#f5f6f8;
}

.container{
    max-width:1000px;
    margin:40px auto;
    padding:0 16px;
}

.card{
    background:white;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:18px;
}
</style>

</head>

<body>

@include('partials.topbar')

<div class="container">
    @yield('content')
</div>

</body>

</html>

<!DOCTYPE html>
<html>
<head>
    <title>
        @section('title')
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield("style")
    {{ Html::style('/css/app.css') }}
    @yield("script")
    {{ Html::script('/js/app.js') }}
</head>
<body>
@yield("body")
@yield("js")
</body>
</html>
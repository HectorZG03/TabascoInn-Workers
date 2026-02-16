@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
<script src="https://unpkg.com/lottie-web@5.9.4/build/player/lottie.min.js"></script>

<div class="container text-center py-5">
    <div id="lottie-403" style="width: 300px; height: 300px; margin: 0 auto;"></div>

    <h1 class="display-4 text-danger">403 - Acceso denegado</h1>
    <p class="lead">No tienes permisos para acceder a este módulo.</p>
    @auth
        <p>Usuario: <strong>{{ auth()->user()->nombre }}</strong> ({{ auth()->user()->tipo }})</p>
    @endauth
    <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Regresar</a>
</div>

<script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-403'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json' // animación de "prohibido"
    });
</script>
@endsection

@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
<div class="container text-center py-5">
    <h1 class="display-4 text-danger">403 - Acceso denegado</h1>
    <p class="lead">No tienes permisos para acceder a este modulo .</p>
    @auth
        <p>Usuario: <strong>{{ auth()->user()->nombre }}</strong> ({{ auth()->user()->tipo }})</p>
    @endauth
    <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Regresar</a>
</div>
@endsection

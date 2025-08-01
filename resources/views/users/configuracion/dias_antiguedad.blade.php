{{-- resources/views/user/configuracion/dias_antiguedad.blade.php --}}
@extends('layouts.app')

@section('title', 'Días por Antigüedad')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-heart"></i> Días de Vacaciones por Antigüedad
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearDiaModal">
                        <i class="bi bi-plus-lg"></i> Nuevo Rango
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Antigüedad Mínima (años)</th>
                                    <th>Antigüedad Máxima (años)</th>
                                    <th>Días de Vacaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diasAntiguedad as $dia)
                                    <tr>
                                        <td>{{ $dia->antiguedad_min }}</td>
                                        <td>{{ $dia->antiguedad_max ?? 'En adelante' }}</td>
                                        <td>{{ $dia->dias }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarDiaModal{{ $dia->id }}">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </button>
                                            <form action="{{ route('configuracion.dias_antiguedad.destroy', $dia->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este rango?')">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal Editar -->
                                    <div class="modal fade" id="editarDiaModal{{ $dia->id }}" tabindex="-1" aria-labelledby="editarDiaModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('configuracion.dias_antiguedad.update', $dia->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editarDiaModalLabel">Editar Rango</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="antiguedad_min" class="form-label">Antigüedad Mínima (años)</label>
                                                            <input type="number" class="form-control" id="antiguedad_min" name="antiguedad_min" value="{{ $dia->antiguedad_min }}" min="0" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="antiguedad_max" class="form-label">Antigüedad Máxima (años) - Opcional</label>
                                                            <input type="number" class="form-control" id="antiguedad_max" name="antiguedad_max" value="{{ $dia->antiguedad_max }}" min="{{ $dia->antiguedad_min + 1 }}">
                                                            <div class="form-text">Dejar vacío para "en adelante"</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="dias" class="form-label">Días de Vacaciones</label>
                                                            <input type="number" class="form-control" id="dias" name="dias" value="{{ $dia->dias }}" min="1" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="crearDiaModal" tabindex="-1" aria-labelledby="crearDiaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('configuracion.dias_antiguedad.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="crearDiaModalLabel">Nuevo Rango</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="antiguedad_min" class="form-label">Antigüedad Mínima (años)</label>
                        <input type="number" class="form-control" id="antiguedad_min" name="antiguedad_min" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="antiguedad_max" class="form-label">Antigüedad Máxima (años) - Opcional</label>
                        <input type="number" class="form-control" id="antiguedad_max" name="antiguedad_max" min="1">
                        <div class="form-text">Dejar vacío para "en adelante"</div>
                    </div>
                    <div class="mb-3">
                        <label for="dias" class="form-label">Días de Vacaciones</label>
                        <input type="number" class="form-control" id="dias" name="dias" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
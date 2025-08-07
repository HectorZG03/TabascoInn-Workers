<?php

namespace App\Exports;

use App\Models\Trabajador;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class TrabajadoresEnPermisosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        // ✅ SOLO TRABAJADORES EN PERMISO (NO VACACIONES)
        return Trabajador::where('estatus', 'permiso')
            ->with(['fichaTecnica.categoria.area', 'permisoActivo', 'contactosEmergencia'])
            ->get();
    }

    public function title(): string
    {
        return 'Trabajadores en Permisos';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TRABAJADORES EN PERMISOS'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'ID',
                'Nombre Completo',
                'Área',
                'Categoría',
                'Tipo de Permiso',
                'Motivo',
                'Fecha Inicio',
                'Fecha Fin',
                'Duración (días)',
                'Por Horas',
                'Hora Inicio',
                'Hora Fin',
                'Estado Permiso',
                'Contacto Emergencia',
                'Teléfono Contacto',
                'Observaciones'
            ]
        ];
    }

    public function map($trabajador): array
    {
        // ✅ USAR LA RELACIÓN permisoActivo (singular)
        $permiso = $trabajador->permisoActivo;
        $contacto = $trabajador->contactosEmergencia->first();
        
        // Calcular duración si el permiso existe
        $duracion = 'N/A';
        if ($permiso && $permiso->fecha_inicio && $permiso->fecha_fin) {
            $duracion = $permiso->fecha_inicio->diffInDays($permiso->fecha_fin) + 1;
        }
        
        return [
            $trabajador->id_trabajador,
            $trabajador->nombre_completo,
            $trabajador->fichaTecnica?->categoria?->area?->nombre_area ?? 'N/A',
            $trabajador->fichaTecnica?->categoria?->nombre_categoria ?? 'N/A',
            $permiso?->tipo_permiso ?? 'Sin tipo',
            $permiso?->motivo ?? 'Sin motivo',
            $permiso?->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha',
            $permiso?->fecha_fin?->format('d/m/Y') ?? 'Sin fecha',
            $duracion,
            $permiso?->es_por_horas ? 'Sí' : 'No',
            $permiso?->hora_inicio ?? 'N/A',
            $permiso?->hora_fin ?? 'N/A',
            $permiso?->estatus_permiso ?? 'Sin estado',
            $contacto?->nombre_completo ?? 'Sin contacto',
            $contacto?->telefono_principal ?? 'Sin teléfono',
            $permiso?->observaciones ?? 'Sin observaciones'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->mergeCells('A1:P1'); // 16 columnas
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0066CC'] // Azul para permisos
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Estilo para la fecha de generación
        $sheet->mergeCells('A2:P2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Estilo para encabezados de columna
        $sheet->getStyle('A4:P4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4D94FF'] // Azul más claro para headers
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Resaltar fechas próximas a vencer
        $lastRow = $sheet->getHighestRow();
        for ($row = 5; $row <= $lastRow; $row++) {
            $endDateCell = 'H' . $row; // Columna H = Fecha Fin
            $endDateValue = $sheet->getCell($endDateCell)->getValue();
            
            if ($endDateValue !== 'N/A' && $endDateValue !== 'Sin fecha') {
                try {
                    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $endDateValue);
                    $daysDiff = now()->diffInDays($endDate, false);
                    
                    // Resaltar si vence en menos de 3 días
                    if ($daysDiff < 3 && $daysDiff >= 0) {
                        $sheet->getStyle($endDateCell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFF00']
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'CC0000']
                            ]
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignorar errores de formato de fecha
                }
            }

            // Aplicar bordes a todas las filas de datos
            $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D0D0']
                    ]
                ]
            ]);
        }

        // Congelar paneles
        $sheet->freezePane('A5');

        return [];
    }
}
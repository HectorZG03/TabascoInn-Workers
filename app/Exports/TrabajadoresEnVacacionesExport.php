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

class TrabajadoresEnVacacionesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        // ✅ SOLO TRABAJADORES EN VACACIONES
        return Trabajador::where('estatus', 'vacaciones')
            ->with(['fichaTecnica.categoria.area', 'vacacionActiva', 'contactosEmergencia'])
            ->get();
    }

    public function title(): string
    {
        return 'Trabajadores en Vacaciones';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TRABAJADORES EN VACACIONES'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'ID',
                'Nombre Completo',
                'Área',
                'Categoría',
                'Año Correspondiente',
                'Período Vacacional',
                'Días Correspondientes',
                'Días Solicitados',
                'Fecha Inicio',
                'Fecha Fin',
                'Fecha Reintegro',
                'Estado',
                'Contacto Emergencia',
                'Teléfono Contacto',
                'Observaciones'
            ]
        ];
    }

    public function map($trabajador): array
    {
        // ✅ USAR LA RELACIÓN vacacionActiva (singular)
        $vacacion = $trabajador->vacacionActiva;
        $contacto = $trabajador->contactosEmergencia->first();
        
        // Calcular duración si la vacación existe
        $duracion = 'N/A';
        if ($vacacion && $vacacion->fecha_inicio && $vacacion->fecha_fin) {
        }
        
        return [
            $trabajador->id_trabajador,
            $trabajador->nombre_completo,
            $trabajador->fichaTecnica?->categoria?->area?->nombre_area ?? 'N/A',
            $trabajador->fichaTecnica?->categoria?->nombre_categoria ?? 'N/A',
            $vacacion?->año_correspondiente ?? 'Sin año',
            $vacacion?->periodo_vacacional ?? 'Sin período',
            $vacacion?->dias_correspondientes ?? 0,
            $vacacion?->dias_solicitados ?? 0,
            $vacacion?->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha',
            $vacacion?->fecha_fin?->format('d/m/Y') ?? 'Sin fecha',
            $vacacion?->fecha_reintegro?->format('d/m/Y') ?? 'Sin fecha',
            $vacacion?->estado ?? 'Sin estado',
            $contacto?->nombre_completo ?? 'Sin contacto',
            $contacto?->telefono_principal ?? 'Sin teléfono',
            $vacacion?->observaciones ?? 'Sin observaciones'
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
                'startColor' => ['rgb' => '00B050'] // Verde para vacaciones
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
                'startColor' => ['rgb' => '70AD47'] // Verde más claro para headers
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

        // Resaltar fechas próximas a vencer (reintegro)
        $lastRow = $sheet->getHighestRow();
        for ($row = 5; $row <= $lastRow; $row++) {
            $reintegroDateCell = 'K' . $row; // Columna K = Fecha Reintegro
            $reintegroDateValue = $sheet->getCell($reintegroDateCell)->getValue();
            
            if ($reintegroDateValue !== 'N/A' && $reintegroDateValue !== 'Sin fecha') {
                try {
                    $reintegroDate = \Carbon\Carbon::createFromFormat('d/m/Y', $reintegroDateValue);
                    $daysDiff = now()->diffInDays($reintegroDate, false);
                    
                    // Resaltar si el reintegro es en menos de 3 días
                    if ($daysDiff < 3 && $daysDiff >= 0) {
                        $sheet->getStyle($reintegroDateCell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFF00']
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '0066CC']
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
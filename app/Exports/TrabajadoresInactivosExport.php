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

class TrabajadoresInactivosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        return Trabajador::whereIn('estatus', ['inactivo', 'suspendido'])
            ->with(['fichaTecnica.categoria.area', 'despidoActivo'])
            ->get();
    }

    public function title(): string
    {
        return 'Inactivos-Suspendidos';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TRABAJADORES INACTIVOS O SUSPENDIDOS'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'ID',
                'Nombre Completo',
                'CURP',
                'Área',
                'Categoría',
                'Estado',
                'Tipo de Baja',
                'Fecha de Baja',
                'Motivo de Baja',
                'Fecha de Ingreso',
                'Condición de Salida',
                'Observaciones'
            ]
        ];
    }

    public function map($trabajador): array
    {
        // ✅ VERIFICAR QUE EL DESPIDO ACTIVO EXISTA ANTES DE ACCEDER A SUS PROPIEDADES
        $despidoActivo = $trabajador->despidoActivo;
        
        return [
            $trabajador->id_trabajador,
            $trabajador->nombre_completo,
            $trabajador->curp,
            $trabajador->fichaTecnica?->categoria?->area?->nombre_area ?? 'N/A',
            $trabajador->fichaTecnica?->categoria?->nombre_categoria ?? 'N/A',
            $trabajador->estatus_texto,
            // ✅ VERIFICACIÓN SEGURA DEL DESPIDO ACTIVO
            $despidoActivo?->tipo_baja_texto ?? 'Sin información',
            $despidoActivo?->fecha_baja?->format('d/m/Y') ?? 'Sin fecha',
            $despidoActivo?->motivo ?? 'Sin motivo registrado',
            $trabajador->fecha_ingreso->format('d/m/Y'),
            $despidoActivo?->condicion_salida ?? 'Sin especificar',
            $despidoActivo?->observaciones ?? 'Sin observaciones'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->mergeCells('A1:L1'); // ✅ AJUSTADO PARA 12 COLUMNAS
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C00000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Estilo para la fecha de generación
        $sheet->mergeCells('A2:L2'); // ✅ AJUSTADO PARA 12 COLUMNAS
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
        $sheet->getStyle('A4:L4')->applyFromArray([ // ✅ AJUSTADO PARA 12 COLUMNAS
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF5050']
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

        // Estilo para datos de estado
        $lastRow = $sheet->getHighestRow();
        for ($row = 5; $row <= $lastRow; $row++) {
            $statusCell = 'F' . $row; // Columna del estado
            $statusValue = $sheet->getCell($statusCell)->getValue();
            
            $color = match($statusValue) {
                'Inactivo' => 'FF0000',
                'Suspendido' => 'FF9900',
                default => '000000'
            };
            
            $sheet->getStyle($statusCell)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => $color],
                    'bold' => true
                ]
            ]);

            // ✅ APLICAR BORDES A TODAS LAS CELDAS DE DATOS
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
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
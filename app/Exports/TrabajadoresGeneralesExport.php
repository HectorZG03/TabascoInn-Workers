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

class TrabajadoresGeneralesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        return Trabajador::with(['fichaTecnica.categoria.area'])->get();
    }

    public function title(): string
    {
        return 'Lista General';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TRABAJADORES - LISTA GENERAL'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'ID',
                'Nombre Completo',
                'CURP',
                'RFC',
                'Teléfono',
                'Correo',
                'Área',
                'Categoría',
                'Sueldo Diario',
                'Estado',
                'Fecha de Ingreso',
                'Antigüedad',
            ]
        ];
    }

    public function map($trabajador): array
    {
        return [
            $trabajador->id_trabajador,
            $trabajador->nombre_completo,
            $trabajador->curp,
            $trabajador->rfc,
            $trabajador->telefono,
            $trabajador->correo,
            $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A',
            $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'N/A',
            '$' . number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2),
            $trabajador->estatus_texto,
            $trabajador->fecha_ingreso->format('d/m/Y'),
            $trabajador->antiguedad_texto
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E75B5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Estilo para la fecha de generación
        $sheet->mergeCells('A2:L2');
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
        $sheet->getStyle('A4:L4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5B9BD5']
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

        // Estilo para los datos
        $sheet->getStyle('A5:L' . ($sheet->getHighestRow()))
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D9D9D9']
                    ]
                ]
            ]);

        // Formato numérico para sueldo
        $sheet->getStyle('I5:I' . ($sheet->getHighestRow()))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Congelar paneles (encabezados visibles al desplazar)
        $sheet->freezePane('A5');

        // Autoajustar con espacio adicional
        foreach(range('A','L') as $column) {
            $sheet->getColumnDimension($column)
                ->setAutoSize(true)
                ->setWidth($sheet->getColumnDimension($column)->getWidth() + 2);
        }

        return [];
    }
}
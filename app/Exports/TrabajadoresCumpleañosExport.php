<?php

namespace App\Exports;

use App\Models\Trabajador;
use Carbon\Carbon;
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

class TrabajadoresCumpleañosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $mes;
    protected $mesNombre;

    public function __construct($mes)
    {
        $this->mes = $mes;
        $this->mesNombre = $this->getMonthName($mes);
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $months[$monthNumber] ?? 'Desconocido';
    }

    public function title(): string
    {
        return 'Cumpleaños ' . $this->mesNombre;
    }

    public function collection()
    {
        return Trabajador::whereMonth('fecha_nacimiento', $this->mes)
            ->with(['fichaTecnica.categoria.area'])
            ->orderByRaw('DAY(fecha_nacimiento)')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE CUMPLEAÑOS - ' . strtoupper($this->mesNombre)],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'Día',
                'Nombre Completo',
                'Edad',
                'Área',
                'Categoría',
                'Teléfono',
                'Correo',
                'Estado',
                'Antigüedad',
                'Fecha de Ingreso'
            ]
        ];
    }

    public function map($trabajador): array
    {
        $fechaNacimiento = $trabajador->fecha_nacimiento;
        $edad = $fechaNacimiento->age;
        $proximaEdad = $fechaNacimiento->diffInYears(now()->addYear());
        
        return [
            $fechaNacimiento->format('d'),
            $trabajador->nombre_completo,
            "$edad años (cumple $proximaEdad)",
            $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'N/A',
            $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'N/A',
            $trabajador->telefono,
            $trabajador->correo,
            $trabajador->estatus_texto,
            $trabajador->antiguedad_texto,
            $trabajador->fecha_ingreso->format('d/m/Y')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF3399']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Estilo para la fecha de generación
        $sheet->mergeCells('A2:J2');
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
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF66CC']
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

        // Resaltar cumpleaños del día actual
        $lastRow = $sheet->getHighestRow();
        $currentDay = now()->day;
        
        for ($row = 5; $row <= $lastRow; $row++) {
            $dayCell = 'A' . $row;
            $dayValue = $sheet->getCell($dayCell)->getValue();
            
            if ($dayValue == $currentDay) {
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFCC']
                    ],
                    'font' => [
                        'bold' => true
                    ]
                ]);
            }
        }

        // Congelar paneles
        $sheet->freezePane('A5');

        return [];
    }
}
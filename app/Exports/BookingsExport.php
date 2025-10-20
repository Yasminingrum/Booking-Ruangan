<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dateFrom;
    protected $dateTo;
    protected $roomStats;

    public function __construct($dateFrom, $dateTo, $roomStats)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->roomStats = $roomStats;
    }

    /**
     * Return collection of data
     */
    public function collection()
    {
        return $this->roomStats;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Ruangan',
            'Kategori',
            'Jumlah Booking',
            'Total Jam Pemakaian',
            'Jam Tersedia',
            'Utilisasi (%)',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($stat): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $stat['room']->name,
            \App\Http\Controllers\ReportController::ROOM_TYPE_LABELS[$stat['room']->type] ?? 'Lainnya',
            $stat['total_bookings'],
            $stat['total_hours'] . ' jam',
            $stat['available_hours'] . ' jam',
            $stat['utilization'] . '%',
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style header row (bold + background)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A5568']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],

            // Auto-size columns
            'A' => ['width' => 5],
            'B' => ['width' => 25],
            'C' => ['width' => 20],
            'D' => ['width' => 15],
            'E' => ['width' => 20],
            'F' => ['width' => 15],
            'G' => ['width' => 15],
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Laporan Utilisasi';
    }
}

<?php

namespace App\Exports;

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Resources\ReportResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketTimer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceExport implements
    FromQuery,
    ShouldAutoSize,
    // WithColumnFormatting,
    // WithColumnWidths,
    // WithCustomQuerySize,
    WithHeadings,
    WithMapping,
    // WithProperties
    WithStyles
{
    use RemembersRowNumber;

    protected $input = [];
    protected $ext = 'xlsx';
    protected $request;
    protected $totalRow = null;
    protected $currentRow = 0;

    public function __construct($input, $ext = 'xlsx')
    {
        $this->input = $input;

        $this->ext = $ext;
    }

    public function query()
    {
        return app(InvoiceController::class)->getQuery($this->getRequest());
    }

    public function collection()
    {
        return app(InvoiceController::class)->getAllData($this->getRequest());
    }

    public function querySize(): int
    {
        if ($this->totalRow !== null) {
            return $this->totalRow;
        }

        return ($this->totalRow = $this->query()->count());
    }

    public function getRequest()
    {
        if (! isset($this->request)) {
            $this->request = new Request($this->input);

            $this->request->merge([
                'limit' => 999999999,
                'includes' => 'invoiceServices|invoicePaymentProofs|distributionCenter|franchise',
            ]);
        }

        return $this->request;
    }

    public function headings(): array
    {
        return [
            $this->rowHeadings(),
        ];
    }

    public function rowHeadings()
    {

        return [
            __('No'),
            __('NO INVOICE'),
            __('NO KWITANSI'),
            __('KODE CUSTOMER'),
            __('NAMA CUSTOMER'),
            __('TIPE CUSTOMER'),
            __('JUMLAH PEMBAYARAN'),
            __('TANGGAL PUBLISH'),
            __('TANGGAL PEMBAYARAN'),
            __('STATUS'),
        ];
    }

    public function map($row): array
    {
        $this->currentRow++;

        $customer = null;
        $customerType = null;

        if ($row->distribution_center_id) {
            $customer = $row->distributionCenter;
            $customerType = 'DC';
        } else if ($row->franchise_id) {
            $customer = $row->franchise;
            $customerType = 'FRANCHISE';
        }

        return [
            $this->getRowNumber() ?: $this->currentRow,
            $row->invoice_no ?? null,
            $row->receipt_no ?? null,
            $customer?->code ?? null,
            $customer?->name ?? null,
            $customerType ?? null,
            $row->total ?? null,
            $row->published_at ? $row->published_at->format('d-m-Y') : null,
            $row->actual_payment_date ? $row->actual_payment_date->format('d-m-Y') : null,
            $row->status_description,
        ];
    }


    public function styles(Worksheet $sheet)
    {
        $styles = [
            'A1' => [
                'font' => [
                    'bold' => true
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]
            ],
        ];
        // $sheet->getStyle('A1')->getFont()->setBold(true);
        // $sheet->getStyle('A5:' . $lastColumnKey . '5')->getFont()->setBold(true);
        // $sheet->getStyle('A' . $this->totalRow . ':' . $lastColumnKey . $this->totalRow)->getFont()->setBold(true);

        // $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        // $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // $debitKey = num2alpha(count($headings) - 3);
        // $sheet->getStyle($debitKey . '6:' . $lastColumnKey . $this->totalRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $totalData = $this->querySize();
        $totalHeadings = count($this->rowHeadings()) - 1;
        $startRow = 1;

        $key = num2alpha($totalHeadings);
        $key = 'A' . $startRow . ':' . $key . $startRow;

        $styles[$key] = [
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    // 'rgb' => 'add8e6',
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ];

        if ($this->ext === 'pdf') {
            $sheet->setShowGridLines(false);

            foreach (range(0, $totalHeadings) as $num) {
                $key = num2alpha($num);
                $key = $key . $startRow . ':' . $key . ($totalData + $startRow);

                $styles[$key] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => 'FF000000'
                            ]
                        ]
                    ],
                    'font' => [
                        'size' => 7
                    ]
                ];
            }
        } else {
            foreach (range(0, $totalHeadings) as $num) {
                $key = num2alpha($num);
                $key = $key . $startRow . ':' . $key . ($totalData + $startRow);

                $styles[$key] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => 'FF000000'
                            ]
                        ]
                    ],
                ];
            }
        }

        // $key = num2alpha($totalHeadings);
        // $sheet->mergeCells('A1:' . $key . '1');

        return $styles;
    }
}

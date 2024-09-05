<?php

namespace App\Exports;

use App\Enums\InvoiceStatus;
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
            [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                // null,

                __('Deskripsi 1'),
                __('Qty 1'),
                __('Unit Price 1'),
                __('Deskripsi 2'),
                __('Qty 2'),
                __('Unit Price 2'),
                __('Deskripsi 3'),
                __('Qty 3'),
                __('Unit Price 3'),
                __('Deskripsi 4'),
                __('Qty 4'),
                __('Unit Price 4'),
                __('Deskripsi 5'),
                __('Qty 5'),
                __('Unit Price 5'),

                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ]
        ];
    }

    public function rowHeadings()
    {

        return [
            __('No'),
            __('No Invoice'),
            __('No Kwitansi'),
            __('Tipe Customer'),
            __('Kode Customer'),
            __('Nama Customer'),
            __('Tanggal'),
            __('Surat Penawaran/Our Ref.'),
            __('Surat Penawaran FO/Our Ref.'),
            __('Tanggal Persetujuan SAT-HO'),
            __('Tanggal Persetujuan SAT-HO untuk FO'),
            __('Dasar untuk menerbitkan Invoice Tagihan'),
            __('Dasar untuk menerbitkan Invoice Tagihan FO'),
            __('Rincian'),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            __('Bea Meterai'),
            __('Due Date'),
            __('Catatan'),
            __('Pekerjaan'),
            __('Status'),
            __('Tanggal Pembayaran'),
            __('VAT'),
            __('Sub Total'),
            __('Total'),
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
            $customerType = 'Franchise';
        }

        $result = [
            // $this->getRowNumber() ?: $this->currentRow,
            $row->no ?? null,
            $row->invoice_no ?? null,
            $row->receipt_no ?? null,
            $customerType ?? null,
            $customer?->code ?? null,
            $customer?->name ?? null,
            $row->published_at ? $row->published_at->format('d/m/Y') : null,
            $row->offering_letter_reference_number ?? null,
            $row->fo_offering_letter_reference_number ?? null,
            $row->approval_date ? $row->approval_date->format('d/m/Y') : null,
            $row->fo_approval_date ? $row->fo_approval_date->format('d/m/Y') : null,
            $row->issuance_number ?? null,
            $row->fo_issuance_number ?? null,
        ];

        $invoiceServices = $row->invoiceServices;
        $invoiceServicesCount = count($invoiceServices);

        foreach ($invoiceServices as $service) {
            $result[] = $service->description;
            $result[] = $service->qty;
            $result[] = $service->unit_price;
        }

        if ($invoiceServicesCount < 5) {
            for ($i = 0; $i < (5 - $invoiceServicesCount); $i++) {
                $result[] = null;
                $result[] = null;
                $result[] = null;
            }
        }

        $result = array_merge($result, [
            $row->stamp_duty ?? null,
            $row->due_at ? $row->due_at->format('d/m/Y') : null,
            $row->note ?? null,
            $row->receipt_remark ?? null,
            InvoiceStatus::tryFrom((int) ($row->status ?? 0))?->description() ?: null,
            $row->actual_payment_date ? $row->actual_payment_date->format('d/m/Y') : null,
            $row->ppn_total ?? null,
            $row->sub_total ?? null,
            $row->total ?? null,
        ]);

        return $result;
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
        $startRow = 2;
        $key = num2alpha($totalHeadings);


        $key = 'A1:' . $key . $startRow;

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
                $key = $key . '1:' . $key . ($totalData + $startRow);

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
                $key = $key . '1:' . $key . ($totalData + $startRow);

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
        // $sheet->mergeCells('A1:' . $key . '1');

        for ($j = 0; $j < 13; $j++) {
            $mergingCell = num2alpha($j);
            $sheet->mergeCells($mergingCell . '1:' . $mergingCell . '2');
        }
        for ($j = 28; $j < 37; $j++) {
            $mergingCell = num2alpha($j);
            $sheet->mergeCells($mergingCell . '1:' . $mergingCell . '2');
        }

        $aCell = num2alpha(13);
        $bCell = num2alpha(26);
        $sheet->mergeCells($aCell . '1:' . $bCell . '1');

        return $styles;
    }
}

<?php

namespace App\Imports;

use App\Enums\ImportType;
use App\Enums\InvoiceStatus;
use App\Enums\SettingKey;
use App\Enums\TransferToType;
use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\ImportCache;
use App\Models\Invoice;
use App\Models\Settings;
use App\Services\FileUpload\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;
use Exception;

class InvoiceImport extends ImportCacheImport
{
    public function __construct(string $importPath)
    {
        parent::__construct(ImportType::Invoice, $importPath);
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $items = [];
        $now = Carbon::now();
        $endOfMonth = $now->copy()->endOfMonth();

        $currentNo = null;

        foreach ($collection as $i => $item) {
            if ($i === 0 || $i === 1) {
                continue;
            }

            $no = $item[0] ?? null;
            $customerType = strtolower((string) $item[1]);
            $customerCode = $item[2] ?? null;
            $customerName = $item[3] ?? null;
            $date = $item[4] ?? null;
            $offeringLetterReferenceNumber = $item[5] ?? null;
            $foOfferingLetterReferenceNumber = $item[6] ?? null;
            $approvalDate = $item[7] ?? null;
            $foApprovalDate = $item[8] ?? null;
            $issuanceNumber = $item[9] ?? null;
            $foIssuanceNumber = $item[10] ?? null;
            $dataServices = [
                [
                    'description' => $item[11] ?? null,
                    'qty' => $item[12] ?? null,
                    'unit_price' => $item[13] ?? null,
                ],
                [
                    'description' => $item[14] ?? null,
                    'qty' => $item[15] ?? null,
                    'unit_price' => $item[16] ?? null,
                ],
                [
                    'description' => $item[17] ?? null,
                    'qty' => $item[18] ?? null,
                    'unit_price' => $item[19] ?? null,
                ],
                [
                    'description' => $item[20] ?? null,
                    'qty' => $item[21] ?? null,
                    'unit_price' => $item[22] ?? null,
                ],
                [
                    'description' => $item[23] ?? null,
                    'qty' => $item[24] ?? null,
                    'unit_price' => $item[25] ?? null,
                ],
            ];
            $stampDuty = $item[26] ?? null;
            $dueDate = $item[27] ?? null;
            $note = $item[28] ?? null;
            $receiptRemark = $item[29] ?? null;
            $status = $item[30] ?? null;

            if (! $customerType && ! $customerCode && ! $customerName) {
                continue;
            }

            if ($no) {
                $currentNo = $no;
            } else if ($currentNo) {
                $currentNo++;
            }

            if ($date) {
                $date = Carbon::createFromFormat('d/m/Y', $date);
            } else {
                $date = $now;
            }

            if ($currentNo) {
                [$invoiceNo, $receiptNo, $generatedNo] = Invoice::generateInvoiceReceiptFormat($currentNo, $date);
            } else {
                [$invoiceNo, $receiptNo, $generatedNo] = Invoice::generateInvoiceReceiptNo($date);
            }

            $customer = null;
            $distributionCenter = null;
            $franchise = null;

            if ($customerType === 'dc') {
                if ($customerCode) {
                    $distributionCenter = DistributionCenter::where(DB::raw('lower(code)'), strtolower($customerCode))->first();
                    $customer = $distributionCenter;
                } else if ($customerName) {
                    $distributionCenter = DistributionCenter::where(DB::raw('lower(name)'), strtolower($customerName))->first();
                    $customer = $distributionCenter;
                }
            } else if ($customerType === 'franchise') {
                if ($customerCode) {
                    $franchise = Franchise::where(DB::raw('lower(code)'), strtolower($customerCode))->first();
                    $customer = $franchise;
                } else if ($customerName) {
                    $franchise = Franchise::where(DB::raw('lower(name)'), strtolower($customerName))->first();
                    $customer = $franchise;
                }
            }

            if (! $customer) {
                throw new Exception('Customer on row ' . ($i + 1) . ' could not be found');
            }

            if (! $offeringLetterReferenceNumber) {
                $offeringLetterReferenceNumber = $customer->offering_letter_reference_number;
            }

            if (! $foOfferingLetterReferenceNumber) {
                $foOfferingLetterReferenceNumber = $customer->fo_offering_letter_reference_number;
            }

            if ($approvalDate) {
                $approvalDate = Carbon::createFromFormat('d/m/Y', $approvalDate);
            } else {
                $approvalDate = $customer->approval_date;
            }

            if ($foApprovalDate) {
                $foApprovalDate = Carbon::createFromFormat('d/m/Y', $foApprovalDate);
            } else {
                $foApprovalDate = $customer->fo_approval_date;
            }

            if (! $issuanceNumber) {
                $issuanceNumber = $customer->issuance_number;
            }

            if (! $foIssuanceNumber) {
                $foIssuanceNumber = $customer->fo_issuance_number;
            }

            if (! $stampDuty) {
                $stampDuty = Settings::getValue(SettingKey::StampDuty);
            }

            if ($dueDate) {
                $dueDate = Carbon::createFromFormat('d/m/Y', $dueDate);
            } else {
                $dueDate = $endOfMonth;
            }

            if (! $note) {
                $note = Settings::getValue(SettingKey::InvoiceNote);
            }

            if (! $status || trim(strtolower($status)) === 'draft') {
                $status = InvoiceStatus::Draft->value;
            } else if (trim(strtolower($status)) === 'unpaid') {
                $status = InvoiceStatus::Unpaid->value;
            } else {
                $status = InvoiceStatus::Draft->value;
            }


            $transferToType = TransferToType::BankTransfer->value;

            if (
                $customer->transfer_to_virtual_account_bank_name &&
                $customer->transfer_to_virtual_account_number &&
                trim((string) $customer->transfer_to_virtual_account_bank_name) !== '-' &&
                trim((string) $customer->transfer_to_virtual_account_number) !== '-'
            ) {
                $transferToType = TransferToType::VirtualAccount->value;
            }

            $printStore = Invoice::where('distribution_center_id', $distributionCenter?->id)
                ->orWhere('franchise_id', $franchise?->id)
                ->latest()
                ->first(['print_store']);

            if ($printStore) {
                $printStore = $printStore->print_store;
            } else {
                $printStore = 1;
            }

            $data = [
                'distribution_center_id' => $distributionCenter?->id,
                'franchise_id' => $franchise?->id,
                'no' => $generatedNo,
                'invoice_no' => $invoiceNo,
                'receipt_no' => $receiptNo,
                'published_at' => $date,
                'offering_letter_reference_number' => $offeringLetterReferenceNumber,
                'fo_offering_letter_reference_number' => $foOfferingLetterReferenceNumber,
                'approval_date' => $approvalDate,
                'fo_approval_date' => $foApprovalDate,
                'issuance_number' => $issuanceNumber,
                'fo_issuance_number' => $foIssuanceNumber,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'customer_npwp' => $customer->npwp,

                'ppn_percentage' => Settings::getValue(SettingKey::PpnPercentage),
                'stamp_duty' => $stampDuty,

                'transfer_to_type' => $transferToType,

                'due_at' => $dueDate,
                'note' => $note,
                'signatory_name' => Settings::getValue(SettingKey::SignatoryName),
                'signatory_position' => Settings::getValue(SettingKey::SignatoryPosition),
                'receipt_remark' => $receiptRemark,
                'status' => $status,
                'print_store' => $printStore,
            ];


            $subTotal = 0;

            foreach ($dataServices as $service) {
                if ($service['description'] && $service['qty'] && $service['unit_price']) {
                    $serviceSubTotal = (int) $service['qty'] * (float) $service['unit_price'];

                    $data['services'][] = [
                        'description' => $service['description'],
                        'qty' => $service['qty'],
                        'unit_price' => $service['unit_price'],
                        'sub_total' => $serviceSubTotal,
                    ];
                    $subTotal += $serviceSubTotal;
                }
            }


            $ppnPercentage = (int) Settings::getValue(SettingKey::PpnPercentage);
            $ppnTotal = $subTotal * ($ppnPercentage / 100);
            $total = $subTotal + $ppnTotal + (((int) $stampDuty) ?: 0);

            $data = array_merge($data, [
                'sub_total' => $subTotal,
                'ppn_percentage' => $ppnPercentage,
                'ppn_total' => $ppnTotal,
                'stamp_duty' => $stampDuty === null ? null : (int) $stampDuty,
                'total' => $total,
            ]);

            $items[] = $data;
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $services = $item['services'];
                unset($item['services']);
                $invoice = Invoice::create($item);

                foreach ($services as $service) {
                    $invoice->invoiceServices()->create($service);
                }
            }
        });
    }
}

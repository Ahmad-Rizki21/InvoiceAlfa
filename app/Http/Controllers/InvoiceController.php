<?php

namespace App\Http\Controllers;

use App\Enums\ImportType;
use App\Enums\InvoiceStatus;
use App\Enums\SettingKey;
use App\Enums\TransferToType;
use App\Exports\InvoiceExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoicePaymentProofResource;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use App\Imports\ImportCacheImport;
use App\Imports\InvoiceImport;
use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\ImportCache;
use App\Models\InvoicePaymentProof;
use App\Models\InvoiceService;
use App\Models\Settings;
use App\Models\Store;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use App\Services\Encrypter\Hashids;
use App\Services\FileUpload\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Http;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();

        if ($request->me === 'y') {
            $user = $request->user();

            if ($user instanceof DistributionCenter) {
                $request->merge([
                    'distribution_center_id' => $user->id,
                ]);
            } else if ($user instanceof Franchise) {
                $request->merge([
                    'franchise_id' => (string) $user->id,
                ]);
            } else {
                $request->merge([
                    'distribution_center_id' => 'non',
                ]);
            }
        }

        $entries = $this->getData($request);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'invoices' => InvoiceResource::collection($entries->items()),
            ],
            'meta' => [
                'searches' => $searches->original(),
                'total' => $entries->total(),
                'sorts' => $sorts->original(),
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();

        $user = $request->user();

        if ($user instanceof DistributionCenter) {
            if (! $request->show_franchise) {
                $request->merge([
                    'distribution_center_id' => (string) $user->id,
                ]);
            }
        } else if ($user instanceof Franchise) {
            $request->merge([
                'franchise_id' => 'in:' . Franchise::where('distribution_center_id', $user->distribution_center_id)->pluck('id')->implode('|'),
            ]);
        } else {
            $request->merge([
                'distribution_center_id' => 'non',
            ]);
        }

        $request->merge([
            'status' => 'in:' . implode('|', [
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::PendingReview->value,
                InvoiceStatus::Rejected->value,
            ]),
        ]);

        $entries = $this->getData($request);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'invoices' => InvoiceResource::collection($entries->items()),
            ],
            'meta' => [
                'searches' => $searches->original(),
                'total' => $entries->total(),
                'sorts' => $sorts->original(),
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        return $this->getQuery($request)->paginate($limit ?: 99999999999999, ['*'], 'page', $page);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuery(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();

        $query = Invoice::query();
        $model = $query->getModel();

        foreach ($searches->values() as $search) {
            $operator = $search->operator();

            if ($search->hasRelation()) {
                $query->whereHas($search->relation(), function ($query) use ($search, $operator) {
                    $value = $search->value();
                    $model = $query->getModel();
                    $column = $search->column();

                    if ($operator->isLike()) {
                        if ($model->getKeyName() !== $search->column()) {
                            $value = '%' . $value . '%';
                        }
                    }

                    $column = $model->qualifyColumn($column);

                    if ($operator->isIn() || $operator->isNotIn()) {
                        $query->whereIn($column, $value, 'and', $operator->isNotIn());
                    } else if ($operator->isNull() || $operator->isNotNull()) {
                        $query->whereNull($column, 'and', $operator->isNotNull());
                    } else {
                        $query->where($column, $operator->value(), $value);
                    }
                });
            } else if ($search->column() === 'fuzzy') {
                $query->where(function ($q) use ($search) {
                    $value = $search->value();

                    if ($value) {
                        $value = '%' . $value . '%';

                        $q->where('invoice_no', 'like', $value)
                            ->orWhere('receipt_no', 'like', $value);
                    }
                });
            } else if ($search->column() === 'applicable_month') {
                $query->where(function ($q) use ($search) {
                    try {
                        $value = Carbon::parse($search->value());
                    } catch (\Throwable $e) {
                        try {
                            $value = Carbon::createFromFormat('D M d Y H:i:s e+', $search->value());
                        } catch (\Throwable $e) {
                            $value = Carbon::now();
                        }
                    }

                    $q->where('created_at', '>=', (string) $value->startOfMonth()->startOfDay()->format('Y-m-d H:i:s'))
                        ->where('created_at', '<=', (string) $value->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s'));
                });
            } else if ($model->isColumnExists($search->column())) {
                $value = $search->value();
                $column = $search->column();

                if ($operator->isLike()) {
                    if ($model->getKeyName() === $search->column()) {
                        $value .= '%';
                    } else {
                        $value = '%' . $value . '%';
                    }
                }

                $column = $model->qualifyColumn($column);

                if ($operator->isIn() || $operator->isNotIn()) {
                    $query->whereIn($column, $value, 'and', $operator->isNotIn());
                } else if ($operator->isNull() || $operator->isNotNull()) {
                    $query->whereNull($column, 'and', $operator->isNotNull());
                } else {
                    $query->where($column, $operator->value(), $value);
                }
            }
        }
        $user = $request->user();

        if ($request->show_franchise && $user instanceof DistributionCenter) {
            $query->where(function ($q) use ($user) {
                $q->where('distribution_center_id', $user->id)
                    ->orWhereIn('franchise_id', Franchise::where('distribution_center_id', $user->id)->pluck('id')->toArray());
            });
        }

        foreach ($sorts->values() as $sort) {
            if ($sort->hasRelation()) {
                $query->without($sort->relation())->with([$sort->relation() => function ($query) use ($sort) {
                    $query->orderBy($sort->column(), $sort->direction());
                }]);
            } else if ($model->isColumnExists($sort->column())) {
                $query->orderBy($sort->column(), $sort->direction());
            }
        }

        if ($includes->hasValues()) {
            $query->with($includes->values());
        }

        if ($excludes->hasValues()) {
            $query->without($excludes->values());
        }

        if ($trashed && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if (! is_numeric($id)) {
            $id = Hashids::decodeInvoiceId($id);
        }

        $entry = $this->getQuery($request)->findOrFail($id);

        if ($request->edit) {
            $customer = null;

            if ($entry->distribution_center_id) {
                $customer = DistributionCenter::findOrFail($entry->distribution_center_id);
            } else if ($entry->franchise_id) {
                $customer = Franchise::findOrFail($entry->franchise_id);
            }

            if ($customer) {
                $entry->customer_name = $customer->name;
                $entry->customer_address = $customer->address;
            }
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'invoice' => new InvoiceResource($entry),
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $id)
    {
        $request->merge([
            'includes' => 'invoiceServices'
        ]);

        $entry = $this->getQuery($request)->findOrFail($id);
        $stores = [];

        if ($request->edit) {
            $customer = null;

            if ($entry->distribution_center_id) {
                $customer = DistributionCenter::findOrFail($entry->distribution_center_id);
            } else if ($entry->franchise_id) {
                $customer = Franchise::findOrFail($entry->franchise_id);
            }

            if ($customer) {
                $entry->customer_name = $customer->name;
                $entry->customer_address = $customer->address;
            }
        }

        if ($entry->distribution_center_id && $entry->print_store) {
            $stores = Store::where('distribution_center_id', $entry->distribution_center_id)->get();
        }

        $showSignatures = $request->signs;

        return view('print.invoice', [
            'pageTitle' => 'Invoice',
            'invoice' => $entry,
            'bankTransferName' => Settings::getValue(SettingKey::BankTransferName),
            'bankTransferAccountNumber' => Settings::getValue(SettingKey::BankTransferAccountNumber),
            'bankTransferAccountName' => Settings::getValue(SettingKey::BankTransferAccountName),
            'ppnPercentage' => Settings::getValue(SettingKey::PpnPercentage),
            'signatoryName' => Settings::getValue(SettingKey::SignatoryName),
            'signatoryPosition' => Settings::getValue(SettingKey::SignatoryPosition),
            'signatureImage' => $showSignatures ? Settings::getValue(SettingKey::SignatureImage) : null,
            'stampImage' => $showSignatures ? Settings::getValue(SettingKey::StampImage) : null,
            'showStampDuty' => $showSignatures ? $entry->total >= 5000000 : null,
            'stores' => $stores,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReceipt(Request $request, $id)
    {
        $entry = $this->getQuery($request)->findOrFail($id);

        if ($request->edit) {
            $customer = null;

            if ($entry->distribution_center_id) {
                $customer = DistributionCenter::findOrFail($entry->distribution_center_id);
            } else if ($entry->franchise_id) {
                $customer = Franchise::findOrFail($entry->franchise_id);
            }

            if ($customer) {
                $entry->customer_name = $customer->name;
                $entry->customer_address = $customer->address;
            }
        }

        return view('print.receipt', [
            'pageTitle' => 'Kwitansi',
            'invoice' => $entry,
            'bankTransferName' => Settings::getValue(SettingKey::BankTransferName),
            'bankTransferAccountNumber' => Settings::getValue(SettingKey::BankTransferAccountNumber),
            'bankTransferAccountName' => Settings::getValue(SettingKey::BankTransferAccountName),
            'ppnPercentage' => Settings::getValue(SettingKey::PpnPercentage),
            'signatoryName' => Settings::getValue(SettingKey::SignatoryName),
            'signatoryPosition' => Settings::getValue(SettingKey::SignatoryPosition),
            'signatureImage' => Settings::getValue(SettingKey::SignatureImage),
            'stampImage' => Settings::getValue(SettingKey::StampImage),
            'showStampDuty' => $entry->total >= 5000000,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function template(Request $request)
    {
        $customer = null;

        if ($request->distribution_center_id) {
            $customer = DistributionCenter::findOrFail($request->distribution_center_id);
        } else if ($request->franchise_id) {
            $customer = Franchise::findOrFail($request->franchise_id);
        }

        $now = Carbon::now();
        [$invoiceNo, $receiptNo] = Invoice::generateInvoiceReceiptNo($now);

        $transferToType = TransferToType::BankTransfer->value;

        if (
            $customer?->transfer_to_virtual_account_bank_name &&
            $customer?->transfer_to_virtual_account_number &&
            trim((string) $customer?->transfer_to_virtual_account_bank_name) !== '-' &&
            trim((string) $customer?->transfer_to_virtual_account_number) !== '-'
        ) {
            $transferToType = TransferToType::VirtualAccount->value;
        }

        $entry = new Invoice([
            'distribution_center_id' => $request->distribution_center_id,
            'franchise_id' => $request->franchise_id,
            'invoice_no' => $invoiceNo,
            'receipt_no' => $receiptNo,
            'published_at' => $now,
            'offering_letter_reference_number' => $customer->offering_letter_reference_number,
            'fo_offering_letter_reference_number' => $customer->fo_offering_letter_reference_number,
            'approval_date' => $customer->approval_date,
            'fo_approval_date' => $customer->fo_approval_date,
            'issuance_number' => $customer->issuance_number,
            'fo_issuance_number' => $customer->fo_issuance_number,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'customer_npwp' => $customer->npwp,

            'ppn_percentage' => Settings::getValue(SettingKey::PpnPercentage),
            'stamp_duty' => Settings::getValue(SettingKey::StampDuty),

            'transfer_to_type' => $transferToType,

            'due_at' => $now->copy()->endOfMonth(),
            'note' => Settings::getValue(SettingKey::InvoiceNote),
            'signatory_name' => Settings::getValue(SettingKey::SignatoryName),
            'signatory_position' => Settings::getValue(SettingKey::SignatoryPosition),
            'status' => InvoiceStatus::Draft->value,
            'print_store' => 1,
        ]);

        $service = new InvoiceService();
        $service->id = $service->newUniqueId();
        $entry->setRelation('invoiceServices', collect([$service]));
        $entry->load('distributionCenter', 'franchise');

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'invoice' => new InvoiceResource($entry),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('create.invoice');

        $input = $request->validate([
            'distribution_center_id' => ['bail', Rule::requiredIf(!$request->franchise_id), 'nullable', 'exists:' . DistributionCenter::class . ',id'],
            'franchise_id' => ['bail', Rule::requiredIf(!$request->distribution_center_id), 'nullable', 'exists:' . Franchise::class . ',id'],
            'published_at' => ['required', 'date'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
            'invoice_services' => ['required', 'array'],
            'invoice_services.*.description' => ['required'],
            'invoice_services.*.qty' => ['required'],
            'invoice_services.*.unit_price' => ['required'],
            'stamp_duty' => ['sometimes', 'nullable', 'numeric'],
            'due_at' => ['required', 'date'],
            'note' => ['sometimes', 'nullable'],
            'receipt_remark' => ['sometimes', 'nullable'],
            'transfer_to_type' => ['sometimes', 'nullable', new Enum(TransferToType::class)],
            'print_store' => ['sometimes', 'nullable', 'numeric', 'in:0,1'],
        ]);

        if ($request->distribution_center_id && $request->franchise_id) {
            $request->merge([
                'franchise_id' => null,
            ]);
        }

        $customer = null;

        if ($request->distribution_center_id) {
            $customer = DistributionCenter::findOrFail($request->distribution_center_id);
        } else if ($request->franchise_id) {
            $customer = Franchise::findOrFail($request->franchise_id);
        }


        if (! $request->query('force')) {
            $dueAt = Carbon::parse($request->due_at);

            $exisingInvoiceInSameMonth = Invoice::where('distribution_center_id', $request->distribution_center_id)
                                                ->where('franchise_id', $request->franchise_id)
                                                ->where(function ($q) use ($dueAt) {
                                                    $q->where('due_at', '>=', $dueAt->startOfMonth()->startOfDay())
                                                        ->where('due_at', '<=', $dueAt->copy()->endOfMonth()->endOfDay());
                                                })
                                                ->count(['id']);

            if ($exisingInvoiceInSameMonth) {
                return $this->json([
                    'status' => 'fail',
                    'message' => __('Invoice for :customer already exists for :date', [
                        'customer' => $customer ? $customer->name : 'customer',
                        'date' => $dueAt->format('F Y'),
                    ]),
                    'code' => 4,
                ], 400);
            }
        }


        $publishedAt = Carbon::parse($request->published_at);
        [$invoiceNo, $receiptNo, $currentNo] = Invoice::generateInvoiceReceiptNo($publishedAt);

        $services = [];
        $subTotal = 0;

        foreach ($request->invoice_services as $service) {
            $serviceSubTotal = (int) $service['qty'] * (float) $service['unit_price'];

            $services[] = [
                'description' => $service['description'],
                'qty' => (int) $service['qty'],
                'unit_price' => (float) $service['unit_price'],
                'sub_total' => $serviceSubTotal,
            ];

            $subTotal += $serviceSubTotal;
        }

        $ppnPercentage = (int) Settings::getValue(SettingKey::PpnPercentage);
        $ppnTotal = $subTotal * ($ppnPercentage / 100);
        $total = $subTotal + $ppnTotal + (((int) $request->stamp_duty ) ?: 0);

        $entry = Invoice::create([
            'no' => $currentNo,
            'distribution_center_id' => $request->distribution_center_id,
            'franchise_id' => $request->franchise_id,
            'invoice_no' => $invoiceNo,
            'receipt_no' => $receiptNo,
            'published_at' => $publishedAt,
            'offering_letter_reference_number' => $request->offering_letter_reference_number,
            'fo_offering_letter_reference_number' => $request->fo_offering_letter_reference_number,
            'approval_date' => $request->approval_date,
            'fo_approval_date' => $request->fo_approval_date,
            'issuance_number' => $request->issuance_number,
            'fo_issuance_number' => $request->fo_issuance_number,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'customer_npwp' => $customer->npwp,

            'sub_total' => $subTotal,
            'ppn_percentage' => $ppnPercentage,
            'ppn_total' => $ppnTotal,
            'stamp_duty' => $request->stamp_duty === null ? null : (int) $request->stamp_duty,
            'total' => $total,

            'due_at' => $request->due_at,
            'note' => $request->note,
            'receipt_remark' => $request->receipt_remark,
            'transfer_to_type' => $request->transfer_to_type,

            'signatory_name' => Settings::getValue(SettingKey::SignatoryName),
            'signatory_position' => Settings::getValue(SettingKey::SignatoryPosition),

            'print_store' => (int) $request->print_store,

            'status' => InvoiceStatus::Draft->value,
        ]);

        foreach ($services as $service) {
            $entry->invoiceServices()->create($service);
        }

        $entry->load('invoiceServices');

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully created', ['entity' => __('Invoice')]),
            'data' => [
                'invoice' => new InvoiceResource($entry),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        // $this->authorize('edit.invoice');

        $entry = Invoice::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Invoice')]));
        }

        if ($request->distribution_center_id && $request->franchise_id) {
            $request->merge([
                'franchise_id' => null,
            ]);
        }

        $request->validate([
            'distribution_center_id' => ['sometimes', 'nullable', 'exists:' . DistributionCenter::class . ',id'],
            'franchise_id' => ['sometimes', 'nullable', 'exists:' . Franchise::class . ',id'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
            'invoice_services' => ['sometimes', 'nullable', 'array'],
            'invoice_services.*.description' => [Rule::requiredIf(is_array($request->invoice_services))],
            'invoice_services.*.qty' => [Rule::requiredIf(is_array($request->invoice_services))],
            'invoice_services.*.unit_price' => [Rule::requiredIf(is_array($request->invoice_services))],
            'stamp_duty' => ['sometimes', 'nullable', 'numeric'],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'note' => ['sometimes', 'nullable'],
            'receipt_remark' => ['sometimes', 'nullable'],
            'reject_reason' => ['sometimes', 'nullable'],
            'status' => ['sometimes', 'nullable', new Enum(InvoiceStatus::class)],
            'transfer_to_type' => ['sometimes', 'nullable', new Enum(TransferToType::class)],
            'actual_payment_date' => ['sometimes', 'nullable', 'date'],
            'print_store' => ['sometimes', 'nullable', 'numeric', 'in:0,1'],
            'payment_proof_remark' => ['sometimes', 'nullable'],
        ]);

        $entry->fill([
            'distribution_center_id' => $request->filled('distribution_center_id') ? $request->distribution_center_id : $entry->distribution_center_id,
            'franchise_id' => $request->filled('franchise_id') ? $request->franchise_id : $entry->franchise_id,
            'published_at' => $request->filled('published_at') ? $request->published_at : $entry->published_at,
            'offering_letter_reference_number' => $request->has('offering_letter_reference_number') ? $request->offering_letter_reference_number : $entry->published_at,
            'fo_offering_letter_reference_number' => $request->has('fo_offering_letter_reference_number') ? $request->fo_offering_letter_reference_number : $entry->fo_offering_letter_reference_number,
            'approval_date' => $request->has('approval_date') ? $request->approval_date : $entry->approval_date,
            'fo_approval_date' => $request->has('fo_approval_date') ? $request->fo_approval_date : $entry->fo_approval_date,
            'issuance_number' => $request->has('issuance_number') ? $request->issuance_number : $entry->issuance_number,
            'fo_issuance_number' => $request->has('fo_issuance_number') ? $request->fo_issuance_number : $entry->fo_issuance_number,
            'stamp_duty' => $request->has('stamp_duty') ? $request->stamp_duty : $entry->stamp_duty,
            'due_at' => $request->filled('due_at') ? $request->due_at : $entry->due_at,
            'note' => $request->has('note') ? $request->note : $entry->note,
            'receipt_remark' => $request->filled('receipt_remark') ? $request->receipt_remark : $entry->receipt_remark,
            'ppn_percentage' => $request->filled('ppn_percentage') ? $request->ppn_percentage : $entry->ppn_percentage,
            'reject_reason' => $request->has('reject_reason') ? $request->reject_reason : $entry->reject_reason,
            'transfer_to_type' => $request->filled('transfer_to_type') ? $request->transfer_to_type : $entry->transfer_to_type,
            'actual_payment_date' => $request->has('actual_payment_date') ? $request->actual_payment_date : $entry->actual_payment_date,
            'print_store' => $request->has('print_store') ? $request->print_store : $entry->print_store,
            'payment_proof_remark' => $request->has('payment_proof_remark') ? $request->payment_proof_remark : $entry->payment_proof_remark,
        ]);

        if ($request->status) {
            if ($request->status == InvoiceStatus::Unpaid->value) {
                $entry->unpaid_updated_at = Carbon::now();
            } else if ($request->status == InvoiceStatus::PendingReview->value) {
                if ($entry->status == InvoiceStatus::Paid->value) {
                    $entry->paid_at = null;
                } else if ($entry->status == InvoiceStatus::Rejected->value) {
                    $entry->rejected_at  = null;
                    $entry->reject_reason = null;
                }

                $entry->pending_review_updated_at  = Carbon::now();
            } else if ($request->status == InvoiceStatus::Paid->value) {
                if ($entry->status == InvoiceStatus::Rejected->value) {
                    $entry->rejected_at  = null;
                    $entry->reject_reason = null;
                }

                $entry->paid_at = Carbon::now();
            } else if ($request->status == InvoiceStatus::Rejected->value) {
                if ($entry->status == InvoiceStatus::Paid->value) {
                    $entry->paid_at = null;
                }

                $entry->rejected_at = Carbon::now();
            }

            $entry->status = $request->status;
        }

        $services = [];
        $subTotal = 0;

        if (is_array($request->invoice_services)) {
            foreach ($request->invoice_services as $service) {
                $service['qty'] = (int) $service['qty'];
                $service['unit_price'] = (int) $service['unit_price'];
                $service['sub_total'] = $service['qty'] * $service['unit_price'];
                $subTotal += $service['sub_total'];
                $services[] = $service;

                if (isset($service['created_at'])) {
                    $storedService = $entry->invoiceServices()->where('id', $service['id']);

                    if ($storedService) {
                        $storedService->update($service);
                    } else {
                        $entry->invoiceServices()->create($service);
                    }
                } else {
                    $entry->invoiceServices()->create($service);
                }
            }

            $entry->sub_total = $subTotal;
        }

        if ($entry->isDirty(['sub_total', 'ppn_percentage', 'stamp_duty'])) {
            $entry->ppn_total = $subTotal * ($entry->ppn_percentage / 100);
            $entry->total= $subTotal + $entry->ppn_total + $entry->stamp_duty;
        }

        $entry->save();

        $entry->refresh();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Invoice')]),
            'data' => [
                'invoice' => new InvoiceResource($entry),
            ],
        ]);
    }

    public function uploadPaymentProofs(Request $request)
    {
        $request->validate([
            'payment_proof' => ['bail', 'required', 'file', 'mimes:jpg,jpeg,png,svg,heic', 'max:' . (1024 * 1024 * 10)],
        ]);

        $entry = Invoice::find($request->id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Invoice')]));
        }

        $uploadedFile = UploadedFile::from($request->file('payment_proof'));

        $disk = 'public';
        $basePath = 'payment-proofs/' . $entry->id;

        $paymentProof = InvoicePaymentProof::create([
            'invoice_id' => $entry->id,
            'name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'path' => $uploadedFile->disk('public')->store($basePath),
            'disk' => $disk,
        ]);

        if ($entry->status == InvoiceStatus::Unpaid->value) {
            $entry->status = InvoiceStatus::PendingReview->value;
            $entry->pending_review_updated_at = Carbon::now();
            $entry->save();
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully uploaded', ['entity' => __('Payment proof')]),
            'data' => [
                'invoice_payment_proof' => new InvoicePaymentProofResource($paymentProof),
            ],
        ]);
    }

    public function getPaymentProofs(Request $request)
    {
        $invoiceIds = $request->invoice_id;

        if (! is_array($invoiceIds)) {
            $invoiceIds = [$invoiceIds];
        }

        $entries = Invoice::with('invoicePaymentProofs')->whereIn('id', $invoiceIds)->get();

        $paymentProofs = [];

        foreach ($entries as $entry) {
            foreach ($entry->invoicePaymentProofs as $i => $paymentProof) {
                if ($i < 5) {
                    $paymentProofs[] = $paymentProof;
                }
            }
        }

        $paymentProofs = collect(array_values($paymentProofs));

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully uploaded', ['entity' => __('Payment proof')]),
            'data' => [
                'invoice_payment_proofs' => InvoicePaymentProofResource::collection($paymentProofs),
            ],
        ]);
    }

    public function destroyPaymentProofs(Request $request)
    {
        // $this->authorize('delete.invoice');

        $ids = $request->id;

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        foreach (InvoicePaymentProof::whereIn('id', $ids)->get() as $paymentProof) {
            if (Storage::disk($paymentProof->disk)->exists($paymentProof->path)) {
                Storage::disk($paymentProof->disk)->delete($paymentProof->path);
            }

            $paymentProof->delete();
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Payment proof')]),
            'data' => new \stdClass(),
        ]);
    }


    public function importUpload(Request $request)
    {
        // $this->authorize('manage.distribution_center');

        $request->validate([
            'file' => ['bail', 'required', 'file', 'mimes:xls,xlsx', 'max:' . (1024 * 500)],
        ]);

        $importPath = ImportCacheImport::uploadFile($request->file('file'));

        return $this->json([
            'status' => 'success',
            'data' => [
                'import_path' => $importPath,
            ],
        ]);
    }

    public function importCache(Request $request)
    {
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        Excel::import(new ImportCacheImport(ImportType::DistributionCenter, $importPath), ImportCacheImport::getUploadedFile($importPath));

        return $this->json([
            'status' => 'success',
            'data' => [
                'import_path' => $importPath,
            ],
        ]);
    }

    public function importProcess(Request $request)
    {
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        $limit = 50;
        $page = $request->page ?: 1;
        $entries = ImportCache::where('import_path', $importPath)->paginate($limit, ['*'], 'page', $page);

        $rules = [
            'code' => ['sometimes', 'nullable', 'unique:' . DistributionCenter::class . ',code,NULL,id'],
            'name' => ['required', 'max:191'],
            'email' => ['bail', 'required', 'email', 'max:191', new UniqueEmailRule()],
            'username' => ['bail', 'sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule()],
            'password' => ['sometimes', 'nullable', 'required_without:code'],
            'landline_number' => ['sometimes', 'nullable'],
            'phone_number' => ['sometimes', 'nullable'],
            'location' => ['sometimes', 'nullable'],
            'address' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date_format:d/m/Y'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date_format:d/m/Y'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_bank_name' => ['sometimes', 'nullable'],
            'transfer_to_virtual_account_number' => ['sometimes', 'nullable'],
        ];
        $deletingIds = [];
        $hasError = false;

        foreach ($entries as $entry) {
            $content = $entry->content ?: [];

            $content = [
                'name' => $content[0] ?? null,
                'code' => $content[1] ?? null,
                'npwp' => $content[2] ?? null,
                'location' => $content[3] ?? null,
                'address' => $content[4] ?? null,
                'landline_number' => $content[5] ?? null,
                'phone_number' => $content[6] ?? null,
                'approval_date' => $content[7] ?? null,
                'fo_approval_date' => $content[8] ?? null,
                'offering_letter_reference_number' => $content[9] ?? null,
                'fo_offering_letter_reference_number' => $content[10] ?? null,
                'issuance_number' => $content[11] ?? null,
                'fo_issuance_number' => $content[12] ?? null,
                'transfer_to_virtual_account_bank_name' => $content[13] ?? null,
                'transfer_to_virtual_account_number' => $content[14] ?? null,
                'email' => $content[15] ?? null,
                'username' => $content[16] ?? null,
                'password' => $content[17] ?? $content[1] ?? null,
            ];

            $validator = Validator::make($content, $rules);
            $errors = [];

            if ($validator->fails()) {
                $errors = $validator->errors()->getMessages();
                $entry->content = $content;
                $entry->errors = $errors;
                $entry->save();
                $hasError = true;
            } else {
                $deletingIds[] = $entry->id;

                if (isset($content['approval_date']) && ! empty($content['approval_date'])) {
                    $content['approval_date'] = Carbon::createFromFormat('d/m/Y', $content['approval_date']);
                }
                if (isset($content['approval_date']) && ! empty($content['approval_date'])) {
                    $content['fo_approval_date'] = Carbon::createFromFormat('d/m/Y', $content['fo_approval_date']);
                }
                if (isset($content['password']) && ! empty($content['password'])) {
                    $content['password'] = bcrypt($content['password']);
                }
                Invoice::create($content);
            }
        }

        if (count($deletingIds)) {
            ImportCache::whereIn('id', $deletingIds)->delete();
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'import_path' => $importPath,
                'has_error' => $hasError,
            ],
            'meta' => [
                'searches' =>'',
                'total' => $entries->total(),
                'sorts' => '',
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function importFix(Request $request)
    {
        $importPath = $request->import_path;

        $rules = [
            'code' => ['sometimes', 'nullable', 'unique:' . DistributionCenter::class . ',code,NULL,id'],
            'name' => ['required', 'max:191'],
            'email' => ['bail', 'required', 'email', 'max:191', new UniqueEmailRule()],
            'username' => ['bail', 'sometimes', 'nullable', 'min:3', 'max:30', new ValidUsernameRule(), new UniqueUsernameRule()],
            'password' => ['sometimes', 'nullable', 'required_without:code'],
            'landline_number' => ['sometimes', 'nullable'],
            'phone_number' => ['sometimes', 'nullable'],
            'location' => ['sometimes', 'nullable'],
            'address' => ['sometimes', 'nullable'],
            'approval_date' => ['sometimes', 'nullable', 'date_format:d/m/Y'],
            'fo_approval_date' => ['sometimes', 'nullable', 'date_format:d/m/Y'],
            'offering_letter_reference_number' => ['sometimes', 'nullable'],
            'fo_offering_letter_reference_number' => ['sometimes', 'nullable'],
            'issuance_number' => ['sometimes', 'nullable'],
            'fo_issuance_number' => ['sometimes', 'nullable'],
        ];


        $deletingIds = [];
        $hasError = false;

        foreach ($request->import_cache as $data) {
            $entry = ImportCache::findOrFail($data['id']);

            $validator = Validator::make($data['content'], $rules);
            $errors = [];

            if ($validator->fails()) {
                $errors = $validator->errors()->getMessages();
                $entry->content = $data['content'];
                $entry->errors = $errors;
                $entry->save();
                $hasError = true;
            } else {
                $deletingIds[] = $entry->id;
                $content = $data['content'];

                if (isset($content['approval_date']) && ! empty($content['approval_date'])) {
                    $content['approval_date'] = Carbon::createFromFormat('d/m/Y', $content['approval_date']);
                }
                if (isset($content['fo_approval_date']) && ! empty($content['fo_approval_date'])) {
                    $content['fo_approval_date'] = Carbon::createFromFormat('d/m/Y', $content['fo_approval_date']);
                }
                if (isset($content['password']) && ! empty($content['password'])) {
                    $content['password'] = bcrypt($content['password']);
                }
                Invoice::create($content);
            }
        }

        if (count($deletingIds)) {
            ImportCache::whereIn('id', $deletingIds)->delete();
        }

        return $this->json([
            'status' => $hasError ? 'fail' : 'success',
            'message' => __('Ok'),
        ]);
    }

    public function importErrors(Request $request)
    {
        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $limit = $urlQuery->limit();
        $sorts = $urlQuery->sorts();
        $page = $urlQuery->page();
        $importPath = $request->import_path;
        // $this->authorize('manage.distribution_center');

        $entries = ImportCache::where('import_path', $importPath)->whereNotNull('errors')->paginate($limit, ['*'], 'page', $page);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'import_cache' => $entries->items(),
            ],
            'meta' => [
                'searches' => '',
                'total' => $entries->total(),
                'sorts' => '',
                'limit' => $limit,
                'page' => $page,
                'has_more_page' => $entries->hasMorePages(),
            ],
        ]);
    }

    public function importRowDelete(Request $request, $id)
    {
        ImportCache::findOrFail($id)->delete();

        return $this->json([]);
    }

    public function destroy($id)
    {
        // $this->authorize('delete.invoice');

        $entry = Invoice::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Invoice')]));
        }

        if (! in_array($entry->status, [InvoiceStatus::Draft->value, InvoiceStatus::Unpaid->value])) {
            $entry->delete();
        } else {
            $entry->invoiceServices()->delete();

            foreach ($entry->invoicePaymentProofs()->get() as $paymentProof) {
                if (Storage::disk($paymentProof->disk)->exists($paymentProof->path)) {
                    Storage::disk($paymentProof->disk)->delete($paymentProof->path);
                }

                $paymentProof->delete();
            }

            $entry->forceDelete();
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Invoice')]),
            'data' => new \stdClass(),
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        // $this->authorize('delete.invoice');

        $request->validate([
            'ids' => ['required', 'array'],
        ]);

        foreach ($request->ids as $id) {
            $entry = Invoice::findOrFail($id);

            $entry->invoiceServices()->delete();

            foreach ($entry->invoicePaymentProofs()->get() as $paymentProof) {
                if (Storage::disk($paymentProof->disk)->exists($paymentProof->path)) {
                    Storage::disk($paymentProof->disk)->delete($paymentProof->path);
                }

                $paymentProof->delete();
            }

            $entry->forceDelete();
        }

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Invoice')]),
            'data' => new \stdClass(),
        ]);
    }

    public function requestExport(Request $request)
    {
        if (! in_array($request->ext, ['xlsx', 'pdf'])) {
            return abort(400, __('Please specify extension to export'));
        }

        if (Cache::driver('database')->has(static::class . 'export')) {
            return $this->json([
                'status' => 'fail',
                'data' => [
                    'wait' => true,
                ]
            ], 202);
        }

        Cache::driver('database')->put(static::class . 'export', 1, 1 * 60);

        return $this->json([
            'status' => 'success',
            'message' => __('Please wait while we are generating your export'),
            'data' => [
                'url' => route('invoices.export', array_merge($request->all(), [
                    'api_token' => $request->bearerToken(),
                ])),
            ],
        ]);
    }

    public function export(Request $request)
    {
        // if (!in_array($request->ext, ['xlsx', 'pdf'])) {
        //     return redirect('/invoices');
        // }

        // if (! Cache::driver('database')->has(static::class . 'export')) {
        //     return redirect('/invoices');
        // }

        $ext = $request->ext;

        $date = null;

        if ($request->applicable_month) {
            try {
                $date = Carbon::parse($request->applicable_month)->format('m-Y');
            } catch (\Throwable $e) {
                $date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->applicable_month)->format('m-Y');
            }
        }

        // if ($ext === 'xlsx') {
        //     $ext = ExcelFactory::XLSX;
        // } else if ($ext === 'pdf') {
        //     $ext = ExcelFactory::MPDF;
        // }

        if ($request->ext === 'pdf') {
            $route = url('/invoices/bulk-print?' . http_build_query([
                'ids' => $request->ids,
                'applicable_month' => $request->applicable_month,
                'api_token' => $request->api_token,
                'ext' => $request->ext,
            ]));


            // dd($route);
            // return [
            //     $route,
            // ];

            $output = SnappyPdf::loadFile($route)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-bottom', 0)
                ->setOption('print-media-type', true)
                ->setOption('javascript-delay', 10000)
                ->setOption('enable-javascript', true);

            return $output->inline('pdf.pdf');
        }

        $export = new InvoiceExport($request->except(['ext', 'api_token']), $request->ext);
        $exportFilename = 'invoices-' . $date . '' . (date('U')) . '.' . $request->ext;

        Cache::driver('database')->put(static::class . 'export', 1, 1 * 60);
        try {
            if (intval($request->dl) === 1) {
                return Excel::download($export, $exportFilename);
            }

            if (intval($request->inspect) === 1) {
                return Excel::raw($export, $ext);
            }

            return Excel::download($export, $exportFilename);
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }


    public function simpleImportUpload(Request $request)
    {

        $request->validate([
            'file' => ['bail', 'required', 'file', 'mimes:xls,xlsx', 'max:' . (1024 * 500)],
        ]);

        if (Cache::driver('database')->has(static::class . 'simpleimport')) {
            return $this->json([
                'status' => 'fail',
                'data' => [
                    'wait' => true,
                    'url' => route('api.invoice.simple-import', array_merge(Cache::driver('database')->get(static::class . 'simpleimport'), [
                        'api_token' => $request->bearerToken(),
                    ])),
                ],
            ], 202);
        }

        $uploadedFile = UploadedFile::from($request->file('file'));
        $upload = InvoiceImport::uploadFile($uploadedFile);

        $payload = array_merge($request->except('file'), [
            'import_path' => $upload,
        ]);

        Cache::driver('database')->put(static::class . 'simpleimport', $payload, 3 * 60);

        return $this->json([
            'status' => 'success',
            'message' => __('Please wait while we are generating your import'),
            'data' => [
                'url' => route('api.invoice.simple-import', array_merge($payload, [
                    'api_token' => $request->bearerToken(),
                ])),
            ],
        ]);
    }

    public function simpleImport(Request $request)
    {
        if (! Cache::driver('database')->has(static::class . 'simpleimport')) {
            return redirect('/invoices');
        }


        $date = null;

        if ($request->applicable_month) {
            try {
                $date = Carbon::parse($request->applicable_month)->format('m-Y');
            } catch (\Throwable $e) {
                $date = Carbon::createFromFormat('D M d Y H:i:s e+', $request->applicable_month)->format('m-Y');
            }
        }
        $uploadedFile = InvoiceImport::getUploadedFile($request->import_path);
        try {
            Excel::import(new InvoiceImport($request->import_path), $uploadedFile);
        } catch (Throwable $e) {
            sleep(2);

            InvoiceImport::deleteUploadedFile($request->import_path);
            Cache::driver('database')->delete(static::class . 'simpleimport');

            throw $e;
        }

        sleep(2);
        InvoiceImport::deleteUploadedFile($request->import_path);
        Cache::driver('database')->delete(static::class . 'simpleimport');

        return $this->json([
            'status' => 'success',
            'message' => __('Your file successfully imported'),
        ]);
    }


    public function revisions(Request $request, $id)
    {
        $this->authorize('manage.invoice');

        $urlQuery = $this->urlQuery($request);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();
        $limit = $urlQuery->limit();
        $page = $urlQuery->page();

        $entry = Invoice::findOrFail($id);

        $query = $entry->revisions()->with('invoice');

        if (count($sorts->values())) {
            foreach ($sorts->values() as $sort) {
                if ($query->getModel()->isFillable($sort->column()) || $sort->column() == $entry->getKeyName()) {
                    $query->orderBy($sort->column(), $sort->direction());
                }
            }
        } else {
            $query->latest();
        }

        if ($includes->hasValues()) {
            $query->with($includes->values());
        }

        if ($excludes->hasValues()) {
            $query->without($excludes->values());
        }

        if ($trashed) {
            $query->withTrashed();
        }

        $entries = $query->paginate($limit, ['*'], 'page', $page);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'invoices' => $entries->data(),
            ],
            'meta' => [
                'searches' => $searches->original(),
                'total' => $entries->total(),
                'sorts' => $sorts->original(),
                'limit' => $limit,
                'page' => $page,
            ],
        ]);
    }
}

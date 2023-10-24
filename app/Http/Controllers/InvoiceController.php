<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Models\InvoiceService;
use App\Models\Settings;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            $request->merge([
                'distribution_center_id' => $user->id,
            ]);
        } else if ($user instanceof Franchise) {
            $request->merge([
                'franchise_id' => $user->id,
            ]);
        } else {
            $request->merge([
                'distribution_center_id' => 'non',
            ]);
        }

        $request->merge([
            'status' => 'in:' . implode(',', [
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

        return $this->getQuery($request)->paginate($limit, ['*'], 'page', $page);
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

                        $q->where('name', 'like', $value);
                    }
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

        return view('print.invoice', [
            'pageTitle' => 'Invoice',
            'invoice' => $entry,
            'bankTransferName' => Settings::getValue(SettingKey::BankTransferName),
            'bankTransferAccountNumber' => Settings::getValue(SettingKey::BankTransferAccountNumber),
            'bankTransferAccountName' => Settings::getValue(SettingKey::BankTransferAccountName),
            'ppnPercentage' => Settings::getValue(SettingKey::PpnPercentage),
            'signatoryName' => Settings::getValue(SettingKey::SignatoryName),
            'signatoryPosition' => Settings::getValue(SettingKey::SignatoryPosition),
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

            'ppn_percentage' => Settings::getValue(SettingKey::PpnPercentage),
            'stamp_duty' => Settings::getValue(SettingKey::StampDuty),

            'due_at' => $now->copy()->endOfMonth(),
            'note' => Settings::getValue(SettingKey::InvoiceNote),
            'signatory_name' => Settings::getValue(SettingKey::SignatoryName),
            'signatory_position' => Settings::getValue(SettingKey::SignatoryPosition),
        ]);

        $service = new InvoiceService();
        $service->id = $service->newUniqueId();
        $entry->setRelation('invoiceServices', collect([$service]));

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
        [$invoiceNo, $receiptNo] = Invoice::generateInvoiceReceiptNo($publishedAt);

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

            'sub_total' => $subTotal,
            'ppn_percentage' => $ppnPercentage,
            'ppn_total' => $ppnTotal,
            'stamp_duty' => $request->stamp_duty === null ? null : (int) $request->stamp_duty,
            'total' => $total,

            'due_at' => $request->due_at,
            'note' => $request->note,
            'receipt_remark' => $request->receipt_remark,

            'signatory_name' => Settings::getValue(SettingKey::SignatoryName),
            'signatory_position' => Settings::getValue(SettingKey::SignatoryPosition),
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
        ]);

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

    public function destroy($id)
    {
        // $this->authorize('delete.invoice');

        $entry = Invoice::find($id);

        if (!$entry) {
            return abort(404, __(':entity not found', ['entity' => __('Invoice')]));
        }

        $entry->delete();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully deleted', ['entity' => __('Invoice')]),
            'data' => new \stdClass(),
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

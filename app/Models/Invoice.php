<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Services\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'distribution_center_id', 'franchise_id', 'invoice_no', 'receipt_no',
        'customer_name', 'customer_address',
        'approval_date', 'fo_approval_date',
        'offering_letter_reference_number', 'fo_offering_letter_reference_number',
        'issuance_number', 'fo_issuance_number',
        'sub_total', 'ppn_percentage', 'ppn_total', 'stamp_duty', 'total',
        'due_at', 'published_at', 'note', 'receipt_remark',
        'signatory_name', 'signatory_position',
        'status', 'unpaid_updated_at', 'pending_review_updated_at', 'paid_at',
        'rejected_at', 'reject_reason', 'transfer_to_type', 'customer_npwp',
        'actual_payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'actual_payment_date' => 'date',
        'due_at' => 'date',
        'password' => 'hashed',
        'approval_date' => 'date',
        'fo_approval_date' => 'date',
        'published_at' => 'date',
        'unpaid_updated_at' => 'datetime',
        'pending_review_updated_at' => 'datetime',
        'paid_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function distributionCenter()
    {
        return $this->belongsTo(DistributionCenter::class, 'distribution_center_id', 'id', 'distributionCenter');
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class, 'franchise_id', 'id', 'franchise');
    }

    public function invoiceServices()
    {
        return $this->hasMany(InvoiceService::class, 'invoice_id', 'id');
    }

    public function invoicePaymentProofs()
    {
        return $this->hasMany(InvoicePaymentProof::class, 'invoice_id', 'id');
    }

    public function statusDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => InvoiceStatus::tryFrom((int) $this->status)->description()
        );

    }

    public static function generateInvoiceReceiptNo(DateTimeInterface $date)
    {
        $invoiceId = Invoice::latest('id')->first(['id']);

        if ($invoiceId) {
            $invoiceId = $invoiceId->id + 1;
        } else {
            $invoiceId = 1;
        }

        $idStart = 1743;
        $invoiceId += $idStart;

        $invoiceId = str_pad((string) $invoiceId, 5, '0', STR_PAD_LEFT);

        return [
            $invoiceId . '/INV-AJNusa/' . $date->format('m/Y'),
            $invoiceId . '/KWT-AJNusa/' . $date->format('m/Y'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class InvoicePaymentProof extends Model
{
    use HasUlids;

    protected $table = 'invoice_payment_proofs';

    protected $fillable = [
        'id', 'invoice_id', 'path', 'mime_type',
        'size', 'name', 'disk',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id', 'invoice');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class InvoiceService extends Model
{
    use HasUlids;

    protected $table = 'invoice_services';

    protected $fillable = [
        'invoice_id', 'description', 'qty', 'unit_price',
        'sub_total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id', 'invoice');
    }
}

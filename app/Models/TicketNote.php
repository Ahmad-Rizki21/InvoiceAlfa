<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;

class TicketNote extends Model
{
    use Concerns\HasCreatedBy;

    protected $table = 'ticket_notes';

    protected $fillable = [
        'message', 'ticket_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id', 'ticket');
    }
}

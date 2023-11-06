<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

class InvoicePaymentProofResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'invoice_id' => $this->nullableInt($this->invoice_id),
            'path' => $this->nullableString($this->path),
            'mime_type' => $this->nullableString($this->mime_type),
            'size' => $this->nullableInt($this->size),
            'name' => $this->nullableString($this->name),
            'url' => $this->nullableString(Storage::disk($this->disk)->url($this->path)),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

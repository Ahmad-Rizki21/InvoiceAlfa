<?php

namespace App\Http\Resources;

class InvoiceServiceResource extends JsonResource
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
            'description' => $this->nullableString($this->description),
            'qty' => $this->nullableInt($this->qty),
            'unit_price' => $this->nullableFloat($this->unit_price),
            'sub_total' => $this->nullableFloat($this->sub_total),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

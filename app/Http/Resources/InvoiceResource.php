<?php

namespace App\Http\Resources;

use App\Enums\InvoiceStatus;
use App\Services\Encrypter\Hashids;

class InvoiceResource extends JsonResource
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
            'id' => (int) $this->id,
            'uid' => Hashids::encodeInvoiceId($this->id),
            'invoice_no' => (string) $this->invoice_no,
            'receipt_no' => (string) $this->receipt_no,
            'distribution_center_id' => $this->nullableInt($this->distribution_center_id),
            'distribution_center' => $this->whenLoaded('distributionCenter', function () {
                return new DistributionCenterResource($this->distributionCenter);
            }),
            'franchise_id' => $this->nullableInt($this->franchise_id),
            'franchise' => $this->whenLoaded('franchise', function () {
                return new FranchiseResource($this->franchise);
            }),
            'published_at' => $this->formatDate($this->published_at),
            'offering_letter_reference_number' => $this->nullableString($this->offering_letter_reference_number),
            'fo_offering_letter_reference_number' => $this->nullableString($this->fo_offering_letter_reference_number),
            'approval_date' => $this->formatDateTime($this->approval_date),
            'fo_approval_date' => $this->formatDateTime($this->fo_approval_date),
            'issuance_number' => $this->nullableString($this->issuance_number),
            'fo_issuance_number' => $this->nullableString($this->fo_issuance_number),

            'invoice_services' => $this->whenLoaded('invoiceServices', function () {
                return InvoiceServiceResource::collection($this->invoiceServices);
            }),

            'invoice_payment_proofs' => $this->whenLoaded('invoicePaymentProofs', function () {
                return InvoicePaymentProofResource::collection($this->invoicePaymentProofs);
            }),

            'customer_name' => $this->nullableString($this->customer_name),
            'customer_address' => $this->nullableString($this->customer_address),
            'customer_npwp' => $this->nullableString($this->customer_npwp),

            'transfer_to_type' => (int) $this->transfer_to_type,

            'sub_total' => $this->nullableFloat($this->sub_total),
            'ppn_percentage' => (int) $this->ppn_percentage,
            'ppn_total' => $this->nullableFloat($this->ppn_total),
            'stamp_duty' => $this->nullableFloat($this->stamp_duty),
            'total' => $this->nullableFloat($this->total),

            'due_at' => $this->formatDate($this->due_at),
            'note' => (string) $this->note,
            'receipt_remark' => (string) $this->receipt_remark,
            'status' => (int) $this->status,
            'status_description' => $this->status_description,

            'signatory_name' => (string) $this->signatory_name,
            'signatory_position' => (string) $this->signatory_position,

            'print_store' => (int) $this->print_store,

            'unpaid_updated_at' => $this->formatDateTime($this->unpaid_updated_at),
            'pending_review_updated_at' => $this->formatDateTime($this->pending_review_updated_at),
            'paid_at' => $this->formatDateTime($this->paid_at),
            'actual_payment_date' => $this->formatDate($this->actual_payment_date),
            'payment_proof_remark' => $this->nullableString($this->payment_proof_remark),
            'rejected_at' => $this->formatDateTime($this->rejected_at),
            'reject_reason' => $this->nullableString($this->reject_reason),

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

<?php

namespace App\Http\Resources;

class RemoteLocationResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            'code' => $this->code,
            'name' => $this->name,
            'terminal_name' => $this->terminal_name,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'online_at' => (string) $this->online_at,
            'pic_remote_name' => $this->pic_remote_name,
            'pic_remote_phone_number' => $this->pic_remote_phone_number,
            'pic_it_sat_name' => $this->pic_it_sat_name,
            'pic_it_sat_phone_number' => $this->pic_it_sat_phone_number,
            'infrastructure_type' => $this->infrastructure_type,
            'distribution_center' => $this->distribution_center,
            'gsm_no' => $this->gsm_no,
            'gsm_provider' => $this->gsm_provider,
            'gsm_no2' => $this->gsm_no2,
            'gsm_provider2' => $this->gsm_provider2,
            'cid_provider' => $this->cid_provider,
            'fo_provider' => $this->fo_provider,
            // 'sla' => $this->sla,
            'pic_fo_provider_name' => $this->pic_fo_provider_name,
            'pic_fo_provider_phone_number' => $this->pic_fo_provider_phone_number,
            'pic_service_point_name' => $this->pic_service_point_name,
            'pic_service_point_phone_number' => $this->pic_service_point_phone_number,
            'fo_contract_by_name' => $this->fo_contract_by_name,
            'cid_no' => $this->cid_no,
            'remark' => $this->remark,
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

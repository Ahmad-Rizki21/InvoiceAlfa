<?php

namespace App\Http\Resources;

use Carbon\Carbon;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sla = (float) $this->customer_sla;
        $currentTotalDays = $this->current_total_days;
        $currentTotalHours = $currentTotalDays * 24;
        $mttrHour = 0;
        $openClock = (int) $this->open_clock;
        $pendingClock = (int) $this->pending_clock;
        $startClock = (int) $this->start_clock;
        $totalClock = $openClock + $pendingClock + $startClock;
        $availability = 0;
        $totalTime = 0;
        $downTimeCustomer = (int) $this->down_time_customer;
        $downTimeProvider = (int) $this->down_time_provider;
        $mttr = (int) $this->mttr;

        if ($sla) {
            $totalTime = ($currentTotalHours * ($sla / 100)) * 3600;
            $mttrHour = ($currentTotalHours * ((100 - $sla) / 100)) * 3600;
            $totalClock = $this->open_clock + $this->pending_clock + $this->start_clock;

            $availability = 100 - ($downTimeProvider / ($currentTotalHours * 3600) * 100);
        }

        return [
            'id' => (int) $this->id,
            'customer_id' => $this->customer_id,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->customer_name,
                'sla' => $this->customer_sla,
            ],
            'terminal_name' => $this->terminal_name,
            'location' => $this->location,
            'address' => $this->address,
            'latitude' => $this->latitude ? (string) $this->latitude : null,
            'longitude' => $this->longitude ? (string) $this->longitude : null,
            'pic_name' => $this->pic_name,
            'pic_phone_number' => $this->pic_phone_number,
            'pic_phone_number2' => $this->pic_phone_number2,
            'online_at' => (string) $this->online_at,
            'mttr_hour' => (int) $mttrHour,
            'total_time' => (int) $totalTime,
            'current_total_days' => (int) $this->current_total_days,
            'sla' => $this->sla ? (int) $this->customer_sla : null,
            'total_tickets' => (int) $this->total_tickets,
            'open_clock' => (int) $openClock,
            'pending_clock' => (int) $pendingClock,
            'start_clock' => (int) $startClock,
            'total_clock' => (int) $totalClock,
            'begin_clock' =>(int) $startClock + $openClock,
            'down_time_customer' => (int) $this->down_time_customer,
            'down_time_provider' => (int) $this->down_time_provider,
            'mttr' => (int) $mttr,
            'availability' => (float) $availability ?: 100,
            'status' => (int) $this->status,
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

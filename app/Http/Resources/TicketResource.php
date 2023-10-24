<?php

namespace App\Http\Resources;

use App\Models\Ticket;

class TicketResource extends JsonResource
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
            'no' => (string) $this->no,
            'title' => (string) $this->title,
            'content' => (string) $this->content,
            'customer_id' => (int) $this->customer_id,
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            'remote_location_id' => (int) $this->remote_location_id,
            'remote_location' => $this->whenLoaded('remoteLocation', function () {
                return new RemoteLocationResource($this->remoteLocation);
            }),
            'ticket_notes' => $this->whenLoaded('ticketNotes', function () {
                return TicketNoteResource::collection($this->ticketNotes);
            }),
            'latest_ticket_timer' => $this->whenLoaded('latestTicketTimer', function () {
               return new TicketTimerResource($this->latestTicketTimer);
            }),
            'status' => (int) $this->status,
            'closed_at' => (string) $this->closed_at,
            'created_by' => $this->whenLoaded('createdBy', function () {
                return new UserResource($this->createdBy);
            }),
            'closed_by_id' => $this->closed_by_id,
            'closed_by' => $this->whenLoaded('closedBy', function () {
                return new UserResource($this->closedBy);
            }),
            'closed_message' => $this->closed_message,
            'down_time_customer' => (float) ($this->down_time_caused_by == Ticket::DOWN_TIME_CUSTOMER ? $this->open_clock + $this->start_clock : 0),
            'down_time_provider' => (float) ($this->down_time_caused_by == Ticket::DOWN_TIME_PROVIDER ? $this->open_clock + $this->start_clock : 0),
            'down_time_caused_by' => (int) $this->down_time_caused_by,
            'open_clock' => (float) $this->open_clock,
            'pending_clock' => (float) $this->pending_clock,
            'start_clock' => (float) $this->start_clock,
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

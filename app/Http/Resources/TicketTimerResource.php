<?php

namespace App\Http\Resources;


class TicketTimerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $diff = 0;

        if ($this->started_at && $this->ended_at) {
            $diff = $this->ended_at->diffInSeconds($this->started_at);
        } else if ($this->started_at && ! $this->ended_at) {
            $diff = time() - $this->started_at->getTimestamp();
        }

        return [
            'id' => (int) $this->id,
            'started_at' => (string) $this->started_at,
            'ended_at' => (string) $this->ended_at,
            'diff' => (int) $diff,
            'progress_message' => (string) $this->progress_message,
            'ticket_id' => (int) $this->ticket_id,
            'ticket' => $this->whenLoaded('ticket', function () {
                return new TicketResource($this->ticket);
            }),
            'status' => (int) $this->status,
            'created_by' => $this->whenLoaded('createdBy', function () {
                return new UserResource($this->createdBy);
            }),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}

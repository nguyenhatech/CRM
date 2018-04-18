<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Tickets\Ticket;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class TicketTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'users',
        'customers',
        'leads',
        'user'
    ];

    public function transform(Ticket $ticket = null)
    {
        if (is_null($ticket)) {
            return [];
        }

        $data = [
            'id'            => $ticket->uuid,
            'type'          => $ticket->type,
            'name'          => $ticket->name,
            'prioty'        => $ticket->prioty,
            'status'        => $ticket->status,
            'prioty_text'   => Ticket::PRIOTY_LIST[$ticket->prioty],
            'status_text'   => Ticket::STATUS_LIST[$ticket->status],
            'deadline'      => $ticket->deadline,
            'start_time'    => $ticket->start_time,
            'end_time'      => $ticket->end_time,
            'email_alert'   => $ticket->email_alert,
            'notify_alert'  => $ticket->notify_alert,
            'description'   => $ticket->description,
            'created_by'    => $ticket->created_by,
            'created_at'    => $ticket->created_at ? $ticket->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'    => $ticket->updated_at ? $ticket->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeUser(Ticket $ticket = null)
    {
        if (is_null($ticket)) {
            return $this->null();
        }

        return $this->item($ticket->user, new UserTransformer());
    }

    public function includeUsers(Ticket $ticket = null)
    {
        if (is_null($ticket)) {
            return $this->null();
        }

        return $this->collection($ticket->users, new UserTransformer());
    }

    public function includeCustomers(Ticket $ticket = null)
    {
        if (is_null($ticket)) {
            return $this->null();
        }

        return $this->collection($ticket->customers, new CustomerTransformer());
    }

    public function includeLeads(Ticket $ticket = null)
    {
        if (is_null($ticket)) {
            return $this->null();
        }

        return $this->collection($ticket->leads, new LeadTransformer());
    }
}

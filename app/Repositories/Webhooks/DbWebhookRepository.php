<?php

namespace Nh\Repositories\Webhooks;
use Nh\Repositories\BaseRepository;

class DbWebhookRepository extends BaseRepository implements WebhookRepository
{
    public function __construct(Webhook $webhook)
    {
        $this->model = $webhook;
    }

    /**
     * Get event list
     * @return [type] [description]
     */
    public function getEvents()
    {
        return $this->model->event_list;
    }

    /**
     * Get webhook by client
     * @return [type] [description]
     */
    public function getByClient($id = null)
    {
        $cid = $id ? $id : getCurrentUser()->id;
        return $this->model->where('client_id', $cid)
                            ->orderBy('created_at', 'DESC')
                            ->get();
    }

    /**
     * Lấy list webhook theo event, ưu tiên event ALL
     * @param  [type] $event     [description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getAllByEvent($event, $client_id = null)
    {
        $cid = $client_id ? $client_id : getCurrentUser()->id;
        return $this->model->where('client_id', $cid)
                        ->where(function($q) use ($event) {
                            $q->where('event_id', $event)
                              ->orWhere('event_id', Webhook::WH_ALL);
                        })
                        ->orderBy('event_id', 'ASC')
                        ->get();
    }

    /**
     * Lấy list webhook theo event, ưu tiên event ALL
     * @param  [type] $event     [description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getByEvent($event, $client_id = null)
    {
        return $this->getAllByEvent($event, $client_id)->first();
    }

    /**
     * Xóa subscription
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id)
    {
        $webhook = $this->model->where('client_id', getCurrentUser()->id)
                            ->where('id', $id)
                            ->first();
        if ($webhook)
        {
            return $webhook->delete();
        }
        return false;
    }

}

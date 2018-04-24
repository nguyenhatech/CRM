<?php

namespace Nh\Repositories\Tickets;
use Nh\Repositories\BaseRepository;

class DbTicketRepository extends BaseRepository implements TicketRepository
{
    public function __construct(Ticket $ticket)
    {
        $this->model = $ticket;
    }

    public function getById($id)
    {
        if (!is_numeric($id)) {
            $id = strtolower($id);
            $id = convert_uuid2id($id);
        }
        return $this->model->find($id);
    }

    /**
     * Lấy tất cả bản ghi có phân trang
     *
     * @param  integer $size Số bản ghi mặc định 25
     * @param  array $sorting Sắp xếp
     * @return Illuminate\Pagination\Paginator
     */
    public function getByQuery($params, $size = 25, $sorting = [])
    {
        $query    = array_get($params, 'q', '');
        $type     = array_get($params, 'type', '');
        $status   = array_get($params, 'status', '');
        $prioty   = array_get($params, 'prioty', '');
        $model    = $this->model;

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($type != '') {
            $model->where('type', $type);
        }
        if ($status != '') {
            $model->where('status', $status);
        }
        if ($prioty != '') {
            $model->where('prioty', $prioty);
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name	', 'like', "%{$query}%")
                    ->orWhere('description	', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     *
     * @param  array $data
     * @return Eloquent
     */
    public function store($data)
    {
        $model = $this->model->create($data);
        $this->syncUsers($model, $data['users']);
        $this->syncLeads($model, $data['leads']);
        $this->syncCustomers($model, $data['customers']);

        return $this->getById($model->id);
    }

    /**
     * Cập nhật thông tin 1 bản ghi theo ID
     *
     * @param  integer $id ID bản ghi
     * @return bool
     */
    public function update($id, $data)
    {
        $record = $this->getById($id);
        $record->fill($data)->save();
        if (array_key_exists('users', $data)) {
            $this->syncUsers($record, $data['users']);
        }
        if (array_key_exists('leads', $data)) {
            $this->syncLeads($record, $data['leads']);
        }
        if (array_key_exists('customers', $data)) {
            $this->syncCustomers($record, $data['customers']);
        }
        
        return $this->getById($id);
    }

    /**
     * Đồng bộ User Relation
     * @param  [type] $model     [description]
     * @param  [type] $customers [description]
     * @return [type]            [description]
     */
    public function syncUsers($model, $users)
    {
        return $model->users()->sync($users);
    }

    /**
     * Đồng bộ Customer Relation
     * @param  [type] $model     [description]
     * @param  [type] $customers [description]
     * @return [type]            [description]
     */
    public function syncCustomers($model, $customers)
    {
        return $model->customers()->sync($customers);
    }

    /**
     * Đồng bộ Lead Relation
     * @param  [type] $model     [description]
     * @param  [type] $leads [description]
     * @return [type]            [description]
     */
    public function syncLeads($model, $leads)
    {
        return $model->leads()->sync($leads);
    }

}

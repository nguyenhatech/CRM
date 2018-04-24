<?php

namespace Nh\Repositories\PhoneCallHistories;
use Nh\Repositories\BaseRepository;

class DbPhoneCallHistoryRepository extends BaseRepository implements PhoneCallHistoryRepository
{
    public function __construct(PhoneCallHistory $phoneCallHistory)
    {
        $this->model = $phoneCallHistory;
    }

    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */

    public function getById($id)
    {
        if (!is_numeric($id)) {
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
        $query      = array_get($params, 'q', '');
        $userId     = array_get($params, 'user_id', '');
        $type       = array_get($params, 'type', '');
        $callType   = array_get($params, 'call_type', '');
        $status     = array_get($params, 'status', '');
        $phone      = array_get($params, 'phone', '');
        $model      = $this->model;

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($userId != '') {
            $model = $model->whereHas('user', function ($model) use ($userId) {
                $model->where('id', convert_uuid2id($userId));
            });
        }
        if ($type != '') {
            $model = $model->where('type', $type);
        }
        if ($callType != '') {
            $model = $model->where('call_type', $callType);
        }
        if ($status != '') {
            $model = $model->where('status', $status);
        }
        if ($phone != '') {
            $model = $model->where(function($q) use ($phone) {
                return $q->where('from', $phone)
                    ->orWhere('to', $phone);
            });
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('from', 'like', "%{$query}%")
                    ->orWhere('to', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Tạo lịch sử cuộc gọi đến từ khách hàng
     * @param  array $data Dữ liệu cuộc gọi
     * @return $model
     */
    public function makeByCallInWebHook($data)
    {
        \Log::info(['Dữ liệu cuộc gọi đến', $data]);
        $params = array_only($data, ['tenant_id', 'agent_id', 'to', 'hotline']);
        $params['from'] = array_get($data, 'from_phone', '');
        $params['start_time'] = $data['time']['$numberLong'];
        $params['call_type'] = PhoneCallHistory::CALL_IN;

        $call = $this->model->create($params);
        event(new \Nh\Events\InfoCallIn($call));

        return $call;
    }

    /**
     * Cập nhập trạng thái cho cuộc gọi khi có sự kiện từ webhook
     * @param  array $data Dữ liệu cuộc gọi
     * @return $model
     */
    public function updateStatusByWebhook($data)
    {
    	switch ($data['event']) {
    		case 'ringing':
                // Đang gọi
    			$call = $this->storeOrUpdate($data);
    			break;
    		case 'connected':
                // Đã kết nối
                if (is_array($data['time'])) {
                    $data['start_time'] = $data['time']['$numberLong'];
                } else {
                    $data['start_time'] = $data['time'];
                }
    			$call = $this->storeOrUpdate($data, 1);
    			break;
    		case 'bye':
    			$data['end_time'] = $data['time']['$numberLong'];
                $data['stop_by'] = PhoneCallHistory::STOP_BY_CUSTOMER;
                $call = $this->storeOrUpdate($data, 1);
    			break;
    		case 'busy_reject':
                // Khách không nghe hoặc bấm bận
    			$call = $this->storeOrUpdate($data, 3);
    			break;
    		case 'cancel':
                // Nhân viên tắt cuộc gọi
                if (is_array($data['time'])) {
                    $data['end_time'] = $data['time']['$numberLong'];
                } else {
                    $data['end_time'] = $data['time']['$numberLong'];
                }
                $data['stop_by'] = PhoneCallHistory::STOP_BY_STAFF;
    			$call = $this->storeOrUpdate($data, 3);
    			break;
    		
    		default:
    			# code...
    			break;
    	}
    }

    /**
     * Tìm cuộc gọi theo transaction_id (mã của phía 123CS)
     * @param  string $tranId
     * @return $model
     */
    public function findByTransactionId($tranId)
    {
        $model = $this->model->where('transaction_id', $tranId);
    	return $model->get()->first();
    }

    /**
     * Tạo mới hoặc cập nhật lịch sử cuộc gọi
     * @param  array   $data   Dữ liệu cuộc gọi
     * @param  integer $status Trạng thái cuộc gọi
     * @return $model
     */
    public function storeOrUpdate($data, $status = 0) {
        $data = array_only($data, ['user_id', 'agent_id', 'transaction_id', 'from', 'to', 'hotline', 'type', 'status', 'start_time', 'end_time', 'stop_by']);
        $transactionId = array_get($data, 'transaction_id', null);

        if ($model = $this->findByTransactionId($data['transaction_id'])) {
            $start_time = array_get($data, 'start_time', null);
            $end_time   = array_get($data, 'end_time', null);
            $stop_by    = array_get($data, 'stop_by', 0);

            $model->hotline     = $data['hotline'];
            $model->start_time  = $start_time;
            $model->end_time    = $end_time;
            $model->stop_by     = $stop_by;
            if ($status) {
                $model->status = $status;
            }
            $model->save();
        } else {
            $data['status'] = $status;
            $model = $this->model->create($data);
        }

        return $this->getById($model->id);
    }

}

<?php

namespace Nh\Repositories\PaymentHistories;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\Customers\CustomerRepository;
use Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode;

class DbPaymentHistoryRepository extends BaseRepository implements PaymentHistoryRepository
{
    public function __construct(PaymentHistory $paymentHistory, CustomerRepository $customer, PaymentHistoryCode $paymentHistoryCode)
    {
        $this->model                = $paymentHistory;
        $this->customer             = $customer;
        $this->paymentHistoryCode   = $paymentHistoryCode;
    }

    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */

    public function getById($id)
    {
        $id = convert_uuid2id($id);
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
        $customerId = array_get($params, 'customer_id', null);
        $startDate  = array_get($params, 'start_date', null);
        $endDate    = array_get($params, 'end_date', null);

        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('description', 'like', "%{$query}%")
                            ->orWhere('uuid', 'like', "%{$query}%");
            });
        }

        if ($startDate) {
            $model = $model->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $model = $model->where('created_at', '<=', $endDate);
        }

        if ($customerId) {
            $model = $model->where('customer_id', convert_uuid2id($customerId));
        }

        if (!getCurrentUser()->isAdmin()) {
            $model = $model->where('client_id', getCurrentUser()->id);
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
        \Log::info('Log create payment ', $data);

        $dataCustomer           = array_only($data, ['name', 'email', 'phone']);
        $customer               = $this->customer->storeOrUpdate($dataCustomer);
        $data['customer_id']    = $customer->id;
        $data['client_id']      = getCurrentUser()->id;

        $model = $this->model->where('booking_id', $data['booking_id'])->first();

        // Xly booking tồn tại hay chưa
        if(is_null($model)) {
            $model = $this->model->create($data);
        } else {
            $model->fill($data)->save();
        }

        if(isset($data['flag']) && $data['flag']) {
            //Lưu mảng mã khuyến mãi ứng với lịch sử giao dịch trên
            if(isset($data['details'])) {
                $arr_promotion_codes = $data['details'];

                foreach ($arr_promotion_codes as $key => $item) {
                    if (! empty($item['promotion_code'])) {
                        $paymentHistoryCode = $this->paymentHistoryCode->where('seat_id', $item['seat_id'])
                                                            ->where('payment_history_id', $model->id)
                                                            ->withTrashed()
                                                            ->first();
                        if (!$paymentHistoryCode) {
                            $result = $this->paymentHistoryCode->create([
                                'payment_history_id' => $model->id,
                                'seat_id'            => $item['seat_id'],
                                'promotion_code'     => $item['promotion_code'],
                                'type_check'         => isset($item['type_check']) ? $item['type_check'] : 0,
                                'status'             => isset($item['status']) ? $item['status'] : 0,
                            ]);
                        }
                    }
                }
            }
        } else {
            // Cập nhật mảng mã khuyến mãi ứng với lịch sử giao dịch trên
            if(isset($data['details'])) {
                $arr_promotion_codes = $data['details'];
                foreach ($arr_promotion_codes as $key => $item) {
                    $paymentHistoryCode = $this->paymentHistoryCode->where('seat_id', $item['seat_id'])
                                                            ->where('payment_history_id', $model->id)
                                                            ->first();

                    if (! is_null($paymentHistoryCode)) {
                        $data_update = [
                            'promotion_code'    => $item['promotion_code'],
                            'type_check'        => isset($item['type_check']) ? $item['type_check'] : 0,
                            'status'            => isset($item['status']) ? $item['status'] : 0
                        ];
                        $paymentHistoryCode->fill($data_update)->save();
                    }
                }
            }

            /**
             * update level customer
             * @var [type]
             */
            if ($model->status == PaymentHistory::PAY_SUCCESS) {
                event(new \Nh\Events\UpdateLevelCustomer($model->customer));
                event(new \Nh\Events\PaymentSuccess($model));
            }
        }

        return $this->getById($model->id);
    }

    /**
     * Cập nhật thông tin 1 bản ghi theo ID
     *
     * @param  integer $id ID bản ghi
     * @return bool
     */
    public function updatePaymentHistory($data)
    {
        \Log::info('Log update payment : ', $data);
        $result      = new \stdClass();
        // Tìm bản ghi trong DB
        $record = $this->model->where('booking_id', $data['booking_id'])->first();

        if (is_null($record)) {
            $result->error   = true;
            $result->message = 'Không tìm thấy lịch sử giao dịch nào ứng với booking_id: ' . $data['booking_id'];
            return $result;
        }

        //Lưu mảng mã khuyến mãi ứng với lịch sử giao dịch trên
        if(isset($data['details'])) {
            foreach ($data['details'] as $key => $value) {
                if (isset($value['delete_at'])) {
                    $paymentHistoryCode = $this->paymentHistoryCode->where('seat_id', $value['seat_id'])
                                                            ->where('payment_history_id', $record->id)
                                                            ->first();
                    if ($paymentHistoryCode) {
                        $paymentHistoryCode->delete();
                    }
                }
            }
        }

        if (isset($data['status'])) {
            if($data['status'] == PaymentHistory::PAY_SUCCESS) {
                $data['payment_at']  = \Carbon\Carbon                   ::now()->format('Y-m-d');
                $setting             = \Nh\Repositories\Settings\Setting::find(1);
                $data['total_point'] = floor(abs($data['total_amount']) / $setting->amount_per_score);
            }
            if($data['status'] == PaymentHistory::PAY_CANCEL) {
                $record->payment_history_codes()->delete();
            }
        }

        $record->fill($data)->save();

        /**
         * update level customer
         * @var [type]
         */
        if ($record->status == PaymentHistory::PAY_SUCCESS) {
            event(new \Nh\Events\UpdateLevelCustomer($record->customer));
            event(new \Nh\Events\PaymentSuccess($record));
        }

        return $this->getById($record->id);
    }

    /**
     * Xóa mềm lịch sử giao dịch
     * @param  [type] $booking_id [description]
     * @return [type]             [description]
     */
    public function softDelete($booking_id)
    {
        $result          = new \stdClass();
        // Tìm bản ghi lịch sử thanh toán có mã truyền vào
        $paymentHistory = $this->model->where('description', 'like', "%{$booking_id}%")->first();

        // Nếu ko có thì báo lỗi
        if (is_null($paymentHistory)) {
            $result->error   = true;
            $result->message = 'Không tồn tại lịch sử giao dịch nào ứng với mã vừa nhập';
            return $result;
        }

        // Nếu có thì xóa mềm nó đi
        $paymentHistory->payment_history_codes()->delete();
        $paymentHistory->delete();

        $result->error   = false;
        $result->message = 'Xóa lịch sử giao dịch của booking mã: ' . $booking_id . ' thành công.';
        return $result;
    }
}

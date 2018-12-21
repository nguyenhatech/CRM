<?php

namespace Nh\Repositories\Promotions;

use Nh\Repositories\BaseRepository;
use Nh\Repositories\PaymentHistories\PaymentHistory;
use Nh\Repositories\Customers\Customer;
use Nh\Repositories\UploadTrait;
use Carbon\Carbon;

class DbPromotionRepository extends BaseRepository implements PromotionRepository
{
    use UploadTrait;

    public function __construct(Promotion $promotion, Customer $customer)
    {
        $this->model = $promotion;
        $this->customer = $customer;
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
     * Lấy tất cả bản ghi có phân trang.
     *
     * @param int   $size    Số bản ghi mặc định 25
     * @param array $sorting Sắp xếp
     *
     * @return Illuminate\Pagination\Paginator
     */
    public function getByQuery($params, $size = 25, $sorting = [])
    {
        $client_id = array_get($params, 'client_id', null);
        $code = array_get($params, 'code', null);
        $query = array_get($params, 'q', '');
        $status = array_get($params, 'status', null);
        $expired_status = array_get($params, 'expired_status', null);
        $now = Carbon::now();
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if (!is_null($status)) {
            $status = (int) $status;
            $model = $model->where('status', $status);
        }

        if (!is_null($code)) {
            $model = $model->where('code', $code);
        }

        if (!is_null($client_id)) {
            $model = $model->where('client_id', $client_id);
        }

        if (!getCurrentUser()->isAdmin()) {
            $model = $model->where('client_id', getCurrentUser()->id);
        }

        if (!is_null($expired_status)) {
            if ($expired_status) {
                $model = $model->where('date_end', '<', $now);
            } else {
                $model = $model->where('date_end', '>', $now);
            }
        }

        if (!empty($query)) {
            $query = '%'.$query.'%';
            $model = $model->where(function ($q) use ($query) {
                $q->where('code', 'LIKE', $query);
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Check mã Code hợp lệ.
     *
     * @param [type] $params [description]
     *
     * @return [type] [description]
     */
    public function check($params)
    {
        \Log::info('Request from havaz to crm ', $params);

        $timeNow = strtotime(Carbon::now()->format('Y-m-d H:i'));
        $code = array_get($params, 'code', '');
        $total_money = (int) array_get($params, 'ticket_money', 0);
        $type = (int) array_get($params, 'type', 1); // 1 là theo tuyến, 2 là theo chặng
        $target_type = (int) array_get($params, 'target_type', 1); // 1 là thường, 2 vip , 3 - siêu vip, 4 - Car Rental
        $timeGoing = array_get($params, 'time_going', $timeNow);
        $type_check = array_get($params, 'type_check', 0);
        $used_status = array_get($params, 'used_status', 0);
        $booking_id = array_get($params, 'booking_id', '');
        $merchant = array_get($params, 'merchant', null);
        $seatId = array_get($params, 'seat_id', null);

        // Nếu có nhóm khách hàng thì check xem user có nằm trong nhóm đó không?
        $email = array_get($params, 'email', null);
        $phone = array_get($params, 'phone', null);
        $customer = null;
        $result = new \stdClass();
        $flagTime = false;

        // Check có code đó tồn tại không ?
        $promotion = $this->model->where('status', Promotion::ENABLE)
                                ->where('code', strtoupper($code))->first();

        if (!is_null($promotion)) {
            $dateStart = strtotime($promotion->date_start);
            $dateEnd = strtotime($promotion->date_end);

            if ($dateStart <= $timeNow && $dateEnd >= $timeNow) {
                $flagTime = true;
            }

            if ($flagTime && $dateStart <= $timeGoing && $dateEnd <= $timeGoing && $promotion->limit_time_type == Promotion::TIME_GOING) {
                $flagTime = true;
            }

            if ($flagTime && $dateStart <= $timeGoing && $dateEnd <= $timeGoing && $promotion->limit_time_type == Promotion::TIME_BOOKING) {
                $flagTime = false;
            }

            if (!$flagTime) {
                $result->error = true;
                $result->message = 'Xin lỗi mã khuyến mại đã hết hạn sử dụng';

                return $result;
            }
        } else {
            $result->error = true;
            $result->message = 'Xin lỗi mã khuyến mại không hợp lệ';

            return $result;
        }

        // Check xem ngày sử dụng KM có nằm trong những ngày không được phép KM trong bảng Setting hay không ?
        $settingRepo = \App::make('Nh\Repositories\Settings\Setting');
        $setting = $settingRepo->find(1);

        if (!is_null($setting) && $setting->special_day && $setting->disable_promotion_special_day) {
            $special_day = json_decode($setting->special_day, true);

            $dateCurrent = date('d-m', strtotime(now()));
            $in_array = in_array($dateCurrent, $special_day);

            if ($in_array) {
                $result->error = true;
                $result->message = 'Xin lỗi mã khuyến mại không áp dụng trong ngày lễ - tết';

                return $result;
            }
        }

        // Check hạng xe hợp lệ thì cho qua?
        // $target_valid = false;
        // $promotions_target = !is_null($promotion->target_type) ? explode(',', $promotion->target_type) : [];

        // if (in_array($target_type, $promotions_target) || $target_type == 0 || $promotion->target_type == 0) {
        //     $target_valid = true;
        // }

        // if (!$target_valid) {
        //     $result->error = true;
        //     $result->message = 'Mã khuyến mại không áp dụng hạng xe '.Promotion::LIST_TARGET_TYPE[$target_type];

        //     return $result;
        // }

        // Check mã khuyến mại áp dụng cho từng nhà xe
        // $merchant_valid = false;

        // $promotion_merchant = !is_null($promotion->merchants) ? explode(',', $promotion->merchants) : [];

        // if (in_array($merchant, $promotion_merchant) || is_null($merchant) || is_null($promotion->merchants)) {
        //     $merchant_valid = true;
        // }

        // if (!$merchant_valid) {
        //     $result->error = true;
        //     $result->message = 'Mã khuyến mại không được áp dụng với nhà xe ';
        //     $result->merchant = $merchant;

        //     return $result;
        // }

        if (!is_null($promotion)) {
            if (!is_null($email) || !is_null($phone)) {
                $customerRepo = \App::make('Nh\Repositories\Customers\CustomerRepository');
                $email = null;
                $customer = $customerRepo->checkExist($email, $phone);
            }

            if ($promotion->cgroup_id) {
                if (is_null($customer)) {
                    $result->error = true;
                    $result->message = 'Xin lỗi quý khách không được áp dụng mã khuyến mại';

                    return $result;
                }
                $customers = $promotion->cgroup ? $promotion->cgroup->customers : [];
                $customers = array_pluck($customers, 'id');
                $customer_in_array = in_array($customer->id, $customers);

                if (!$customer_in_array) {
                    $result->error = true;
                    $result->message = 'Xin lỗi quý khách không được áp dụng mã khuyến mại';

                    return $result;
                }
            }

            // Nếu quantity = 0 thì sử dụng không giới hạn
            // Nếu quantity != 0 thì cần check số lượng hợp lệ hay không ?
            $totalQuantityUsed = 0;
            if ($promotion->quantity) {
                $paymentHistoryCodeRepo = \App::make('Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode');

                $totalPromotionUsed = $paymentHistoryCodeRepo->where('promotion_code', strtoupper($code))
                                                ->whereHas('payment_history', function ($q) use ($promotion) {
                                                    $q->where('status', '<>', 2);
                                                })
                                                ->where('type_check', 1)
                                                ->get();
                $totalQuantityUsed = $totalPromotionUsed->count();

                if ($totalQuantityUsed >= $promotion->quantity) {
                    $result->error = true;
                    $result->message = 'Xin lỗi mã giảm giá đã quá lượt sử dụng';

                    return $result;
                }
            }

            // Nếu mã tồn tại theo số lượt của User thì kiểm tra
            $totalQuantityPerUserUsed = 0;
            if ($promotion->quantity_per_user) {
                if (!is_null($customer)) {
                    $paymentHistoryCodeRepo = \App::make('Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode');

                    $countUsed = $paymentHistoryCodeRepo->where('promotion_code', strtoupper($code))
                                                    ->whereHas('payment_history', function ($q) use ($promotion, $customer) {
                                                        $q->where('status', '<>', 2)
                                                        ->where('customer_id', $customer->id);
                                                    })
                                                    ->where('type_check', 1)
                                                    ->get();
                    $totalQuantityPerUserUsed = $countUsed->count();
                    if ($totalQuantityPerUserUsed >= $promotion->quantity_per_user) {
                        $result->error = true;
                        $result->message = "Bạn chỉ được sử dụng tối đa {$promotion->quantity_per_user} mã khuyến mại";

                        return $result;
                    }
                }
            }

            // Kiểm tra nếu giảm theo % thì tính số tiền dựa theo booking
            // Nếu trường amount_max = 0 thì lấy luôn số tiền vừa tính được
            // Nếu không thì lấy theo trường amount_max
            // Nếu amount_segment = 0 và type là theo chặng thị thông báo không áp dụng cho chặng
            // Nếu khách chạy thoe tuyến thì lấy trường amount , theo chặng thì amount_segment
            $ratio = $type === Promotion::ROUTE ? $promotion->amount : $promotion->amount_segment;

            if ($promotion->amount_segment == 0 && $type == Promotion::SEGMENT) {
                $result->error = true;
                $result->message = 'Mã giảm giá chỉ áp dụng cho toàn tuyến.';

                return $result;
            }

            // Nếu là giảm theo %
            if ($promotion->type == Promotion::PERCENT) {
                // Nếu số tiền tối đa = 0 thì lấy theo tỉ lê %
                if (!$promotion->amount_max) {
                    $amount = (int) $ratio * $total_money * 0.01;
                } else {
                    // Nếu không lấy theo số tiền max nếu như tỉ lệ % nhân ra lớn hơn
                    $amountCaculator = (int) $ratio * $total_money * 0.01;
                    $amountInDB = (int) $promotion->amount_max;
                    $amount = $amountCaculator > $amountInDB ? $amountInDB : $amountCaculator;
                }
            } else {
                // Nếu là giảm theo số tiền
                $amount = $ratio;
            }

            // Trả về thông tin nếu hợp lệ
            $result->error = false;
            $result->message = 'Mã khuyến mại hợp lệ';
            $result->quantity_per_user = $promotion->quantity_per_user - $totalQuantityPerUserUsed;
            $result->quantity = $promotion->quantity - $totalQuantityUsed;
            $result->type = $promotion->getFormMovesText($type);
            $result->target_type = $promotion->getListTargetTypeText($promotion->target_type);
            $result->amount = $amount;
        }

        try {
            $paymentHistoryRepo = \App::make('Nh\Repositories\PaymentHistories\PaymentHistoryRepository');

            $data_payment_history = [
                'phone' => $phone,
                'booking_id' => $booking_id,
                'type' => PaymentHistory::TYPE_CONFIRM,
                'status' => PaymentHistory::PAY_PENDDING,
                'total_amount' => $total_money,
                'description' => "Kiểm tra mã vé cho booking - {$booking_id}",
                'flag' => true,
                'details' => [
                    [
                        'seat_id' => $seatId,
                        'promotion_code' => $code,
                        'type_check' => $type_check,
                        'status' => $used_status,
                    ],
                ],
            ];
            // Nếu là 1 thì insert
            if ($type_check == 1) {
                $paymentHistoryRepo->store($data_payment_history);
            }
        } catch (Exception $e) {
            $result->error = true;
            $result->message = 'Có lỗi xảy ra. Vui lòng liên hệ admin';

            return $result;
        }

        return $result;
    }

    public function usedStatistic($id)
    {
        $promotion = $this->getById($id);
        $model = $this->model->leftJoin('payment_history_codes', 'code', '=', 'promotion_code')
                            ->select(\DB::raw('code, count(promotion_code) as total_used'))
                            ->where('payment_history_codes.type_check', 1)
                            ->whereNull('payment_history_codes.deleted_at')
                            ->where('code', $promotion->code)
                            ->groupBy('code')
                            ->get();

        return $model;
    }

    /**
     * Danh sách khách hàng đã sử dụng mã.
     *
     * @param int $id
     *
     * @return [type]
     */
    public function usedCustomers($id, $params = [])
    {
        $startDate = array_get($params, 'start_date', null);
        $endDate = array_get($params, 'end_date', null);

        $promotion = $this->getById($id);
        if ($promotion) {
            $model = $this->customer->leftJoin('payment_histories', 'customers.id', '=', 'payment_histories.customer_id')
                                ->leftJoin('payment_history_codes', 'payment_histories.id', '=', 'payment_history_codes.payment_history_id')
                                ->leftJoin('promotions', 'payment_history_codes.promotion_code', '=', 'promotions.code');
            $select = 'customers.id, customers.name, customers.phone, customers.email,
                    (SUM(CASE WHEN type_check=1 THEN 1 ELSE 0 END) - SUM(!ISNULL(payment_history_codes.deleted_at))) AS total_used,
                    SUM(!ISNULL(payment_history_codes.deleted_at)) as total_cancel';

            $model = $model->selectRaw($select)->where('promotions.code', $promotion->code);
            // Theo thời gian
            if ($startDate) {
                $startDate = $startDate.' 00:00:00';
                $model = $model->where('payment_histories.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $endDate = $endDate.' 23:59:59';
                $model = $model->where('payment_histories.created_at', '<=', $endDate);
            }

            return $model->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')->get();
        }

        return null;
    }

    /**
     * Khách hàng chưa sử dụng mã.
     *
     * @param [type] $id UUID mã giảm giá
     *
     * @return [type] [description]
     */
    public function notUsedCustomers($id)
    {
        $promotion = $this->getById($id);
        if (isset($promotion->cgroup)) {
            $customers = $this->usedCustomers($id);
            $groupCustomers = $promotion->cgroup->customers;

            return $groupCustomers->diff($customers);
        }

        return null;
    }

    /**
     * Thống kê số lượt dùng theo thời gian.
     *
     * @param int $id
     *
     * @return [type] [description]
     */
    public function statisticByTime($id)
    {
        $promotion = $this->getById($id);

        return $this->model->leftJoin('payment_history_codes', 'promotions.code', '=', 'payment_history_codes.promotion_code')
                            ->leftJoin('payment_histories', 'payment_history_codes.payment_history_id', '=', 'payment_histories.id')
                            ->select(\DB::raw('DATE(payment_histories.created_at) as date, COUNT(payment_history_codes.id) as total'))
                            ->where('payment_history_codes.deleted_at', '=', null)
                            ->where('payment_history_codes.type_check', 1)
                            ->where('promotions.code', $promotion->code)
                            ->groupBy(\DB::raw('DATE(payment_histories.created_at)'))
                            ->get();
    }

    public function getPromotionFree()
    {
        $promotions = $this->model->where('target_type', 0)
                            ->where('date_start', '<=', Carbon::now())
                            ->where('date_end', '>=', Carbon::now())
                            ->where('status', Promotion::ENABLE)
                            ->where('quantity', 0)
                            ->get();

        return $promotions;
    }

    public function getAllPromotions($params, $size = 25, $sorting = [])
    {
        $current = array_get($params, 'current', false);
        $code = array_get($params, 'id', '');
        $now = Carbon::now();
        $model = $this->model->where('status', Promotion::ENABLE);

        $model = $model->where('cgroup_id', 0);

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($current) {
            $model->where('date_start', '<=', Carbon::now())
                ->where('date_end', '>=', Carbon::now());
        }

        if ($code) {
            $model->where('uuid', '=', $code);
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function getPromotionByAccountNew()
    {
        $timeNow = strtotime(Carbon::now()->format('Y-m-d H:i'));

        return $this->model->orderBy('created_at', 'DESC')
                            ->where('is_account_new', Promotion::IS_ACCOUNT_NEW)
                            ->where('status', Promotion::ENABLE)
                            ->where('date_end', '>=', $timeNow)
                            ->first();
    }
}

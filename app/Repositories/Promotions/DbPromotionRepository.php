<?php

namespace Nh\Repositories\Promotions;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\Promotions\Promotion;
use Nh\Repositories\UploadTrait;
use Carbon\Carbon;

class DbPromotionRepository extends BaseRepository implements PromotionRepository
{
    use UploadTrait;

    public function __construct(Promotion $promotion)
    {
        $this->model = $promotion;
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
        $client_id      = array_get($params, 'client_id', null);
        $code           = array_get($params, 'code', null);
        $query          = array_get($params, 'q', '');
        $status         = array_get($params, 'status', null);
        $expired_status = array_get($params, 'expired_status', null);
        $now            = Carbon::now();
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if (!is_null($status)) {
            $status = (int) $status;
            $model  = $model->where('status', $status);
        }

        if (!is_null($code)) {
            $model  = $model->where('code', $code);
        }

        if (!is_null($client_id)) {
            $model = $model->where('client_id', $client_id);
        }

        if (!getCurrentUser()->isAdmin()) {
            $model = $model->where('client_id', getCurrentUser()->id);
        }

        if (! is_null($expired_status)) {
            if ($expired_status) {
                $model = $model->where('date_end', '<', $now);
            }else {
                $model = $model->where('date_end', '>', $now);
            }
        }

        if (!empty($query)) {
            $query = '%' . $query . '%';
            $model = $model->where(function ($q) use ($query) {
                $q->where('code','LIKE', $query);
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Check mã Code hợp lệ
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function check($params)
    {
        $code          = array_get($params, 'code', '');
        $total_money   = (int) array_get($params, 'ticket_money', 0);
        $type          = array_get($params, 'type', 1); // 1 là theo tuyến, 2 là theo chặng
        $result        = new \stdClass();

        $promotion = $this->model->where('status', Promotion::ENABLE)
                                ->where('date_start', '<=',  Carbon::now())
                                ->where('date_end', '>=',  Carbon::now())
                                ->where('code', strtoupper($code))->first();

        if (! is_null($promotion)) {
            // Nếu quantity = 0 thì sử dụng không giới hạn
            // Nếu quantity != 0 thì cần check số lượng hợp lệ hay không ?
            if ($promotion->quantity) {
                $paymentHistoryCodeRepo = \App::make('Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode');

                $countUsed = $paymentHistoryCodeRepo->where('promotion_code', strtoupper($code))
                                                ->whereHas('payment_history', function($q) use ($promotion) {
                                                    $q->where('client_id', $promotion->client_id);
                                                })->get()->count();

                if ($countUsed >= $promotion->quantity) {
                    $result->error = true;
                    $result->message = 'Mã khuyến mãi đã quá số lượt sử dụng';
                    return $result;
                }
            }

            // Nếu mã tồn tại theo số lượt của User thì kiểm tra
            if ($promotion->quantity_per_user) {

                $email = array_get($params, 'email', null);
                $phone = array_get($params, 'phone', null);

                if (!is_null($email) || !is_null($phone)) {

                    $customerRepo = \App::make('Nh\Repositories\Customers\CustomerRepository');
                    $customer = $customerRepo->checkExist($email, $phone);

                    if (! is_null($customer)) {
                        $paymentHistoryCodeRepo = \App::make('Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode');

                        $countUsed = $paymentHistoryCodeRepo->where('promotion_code', strtoupper($code))
                                                        ->whereHas('payment_history', function($q) use ($promotion, $customer) {
                                                            $q->where('client_id', $promotion->client_id)
                                                            ->where('customer_id', $customer->id);
                                                        })
                                                        ->get()->count();

                        if ($countUsed >= $promotion->quantity_per_user) {
                            $result->error = true;
                            $result->message = 'Mã khuyến mãi này đã hết số lượt sử dụng';
                            return $result;
                        }
                    }
                }
            }

            // Kiểm tra nếu giảm theo % thì tính số tiền dựa theo booking
            // Nếu trường amount_max = 0 thì lấy luôn số tiền vừa tính được
            // Nếu không thì lấy theo trường amount_max

            // Nếu khách chạy thoe tuyến thì lấy trường amount , theo chặng thì amount_segment
            $ratio = $type === Promotion::ROUTE ? $promotion->amount : $promotion->amount_segment;

            // Nếu là giảm theo %
            if ($promotion->type == Promotion::PERCENT) {
                // Nếu số tiền tối đa = 0 thì lấy theo tỉ lê %
                if (! $promotion->amount_max) {
                    $amount = (int) $ratio  * $total_money * 0.01;
                } else {
                    // Nếu không lấy theo số tiền max nếu như tỉ lệ % nhân ra lớn hơn
                    $amountCaculator = (int) $ratio * $total_money * 0.01;
                    $amountInDB      = (int) $promotion->amount_max;
                    $amount          = $amountCaculator > $amountInDB ? $amountInDB : $amountCaculator;
                }
            } else {
                // Nếu là giảm theo số tiền
                $amount = $ratio;
            }

            // Trả về thông tin nếu hợp lệ
            $result->error             = false;
            $result->message           = 'Mã khuyến mại hợp lệ';
            $result->quantity_per_user = $promotion->quantity_per_user;
            $result->quantity          = $promotion->quantity;
            $result->type              = $promotion->getFormMovesText($type);
            $result->amount            = $amount;

        } else {
            $result->error = true;
            $result->message = 'Mã khuyến mại không hợp lệ';
        }

        return $result;
    }

    public function usedStatistic($id)
    {
        $promotion = $this->getById($id);
        $model = $this->model->leftJoin('payment_histories', 'promotions.code', '=', 'payment_histories.promotion_code')
            ->select(\DB::raw('promotions.id, count(payment_histories.promotion_code) as total_used, count(DISTINCT payment_histories.customer_id) as total_customer'));
        $model = $model->where('promotions.code', $promotion->code)->groupBy('promotions.id');
        return $model->get();
    }

    public function usedCustomers($id)
    {
        $promotion = $this->getById($id);
        $model = $this->model->leftJoin('payment_histories', 'promotions.code', '=', 'payment_histories.promotion_code')
            ->leftJoin('customers', 'payment_histories.customer_id', '=', 'customers.id')
            ->select(\DB::raw('customers.id, customers.name, customers.phone, customers.email, count(customers.id) as total_used'));
        $model = $model->where('promotions.code', $promotion->code)->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email');
        return $model->get();
    }

}

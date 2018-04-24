<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Tickets\TicketRepository;
use Nh\Repositories\Tickets\Ticket;
use Nh\Http\Transformers\TicketTransformer;
use DB;

class TicketController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $ticket;

    protected $validationRules = [
        'name'      => 'required',
        'prioty'    => 'required|numeric',
        'status'    => 'required|numeric',
        'deadline'  => 'required'
    ];

    protected $validationMessages = [
        'name.required'     => 'Chưa nhập tên',
        'prioty.required'   => 'Chưa chọn mức ưu tiên',
        'prioty.numeric'    => 'Mức ưu tiên không xác định',
        'status.required'   => 'Chưa chọn trạng thái',
        'status.numeric'    => 'Trạng thái không xác định',
        'deadline.required' => 'Chưa nhập thời hạn công việc'
    ];

    public function __construct(TicketRepository $ticket, TicketTransformer $transformer)
    {
    	$this->ticket = $ticket;
    	$this->setTransformer($transformer);
    	$this->checkPermission('ticket');
    }

    public function getResource()
    {
        return $this->ticket;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, $sort);
        return $this->successResponse($models);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = $request->all();
            $customers = [];
            $leads = [];
            $users = [];
            if (array_key_exists('users', $params)) {
                foreach ($params['users'] as $key => $user) {
                    array_push($users, convert_uuid2id($user));
                }
            }
            if (array_key_exists('customers', $params)) {
                foreach ($params['customers'] as $key => $customer) {
                    array_push($customers, convert_uuid2id($customer));
                }
            }
            if (array_key_exists('leads', $params)) {
                foreach ($params['leads'] as $key => $lead) {
                    array_push($leads, convert_uuid2id($lead));
                }
            }
            $params['customers']  = $customers;
            $params['leads']      = $leads;
            $params['users']      = $users;
            $params['created_by'] = getCurrentUser()->id;

            $data = $this->getResource()->store($params);

            DB::commit();
            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = $request->all();
            $customers = [];
            $leads     = [];
            $users     = [];
            if (array_key_exists('customers', $params)) {
                foreach ($params['customers'] as $key => $customer) {
                    array_push($customers, convert_uuid2id($customer));
                }
            }
            if (array_key_exists('leads', $params)) {
                foreach ($params['leads'] as $key => $lead) {
                    array_push($leads, convert_uuid2id($lead));
                }
            }
            if (array_key_exists('users', $params)) {
                foreach ($params['users'] as $key => $user) {
                    array_push($users, convert_uuid2id($user));
                }
            }
            $params['customers'] = $customers;
            $params['leads']     = $leads;
            $params['users']     = $users;

            $model = $this->getResource()->update($id, $params);

            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function changePrioty(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->validationRules = ['prioty'    => 'required|numeric'];
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $prioty = array_get($request->all(), 'prioty', null);

            if (is_null($prioty) || !array_key_exists($prioty, Ticket::PRIOTY_LIST)) {
                return $this->errorResponse(['errors' => ['prioty' => ['Giá trị không hợp lệ!']]]);
            }

            $model = $this->getResource()->update($id, ['prioty' => $prioty]);

            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function changeStatus(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->validationRules = ['status'    => 'required|numeric'];
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $status = array_get($request->all(), 'status', null);

            if (is_null($status) || !array_key_exists($status, Ticket::STATUS_LIST)) {
                return $this->errorResponse(['errors' => ['status' => ['Giá trị không hợp lệ!']]]);
            }

            $model = $this->getResource()->update($id, ['status' => $status]);

            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        $data = $this->getResource()->getById($id);
        if (!$data) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $data->customers()->detach();
            $data->leads()->detach();
            $data->users()->detach();
            $this->getResource()->delete($id);

            DB::commit();
            return $this->deleteResponse();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}

<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Customers\CustomerRepository;
use Nh\Repositories\Comments\CommentRepository;
use Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository;
use Nh\Http\Transformers\CustomerTransformer;
use Nh\Repositories\Cgroups\CgroupRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Nh\Jobs\ImportCsvCustomer;
use Excel;

class CustomerController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $customer;
    protected $cgroup;
    protected $comment;
    protected $phoneCall;

    protected $validationRules = [
        'name'            => 'required|min:5|max:255',
        'email'           => 'nullable|required_without_all:phone|email|max:255',
        'phone'           => 'nullable|required_without_all:email|digits_between:8,12',
        'home_phone'      => 'nullable|digits_between:8,12',
        'company_phone'   => 'nullable|digits_between:8,12',
        'website'         => 'nullable|url',
        'dob'             => 'nullable|date_format:Y-m-d',
        'job'             => 'max:255',
        'address'         => 'max:255',
        'company_address' => 'max:255'
    ];

    protected $validationMessages = [
        'name.required'              => 'Tên không được để trống',
        'name.min'                   => 'Tên cần lớn hơn :min kí tự',
        'name.max'                   => 'Tên cần nhỏ hơn :max kí tự',
        'email.required_without_all' => 'Email hoặc số điện thoại không được để trống',
        'email.email'                => 'Email không đúng định dạng',
        'email.max'                  => 'Email cần nhỏ hơn :max kí tự',
        'phone.required_without_all' => 'Số điện thoại hoặc email không được để trống',
        'phone.digits_between'       => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
        'home_phone.digits_between'  => 'Số điện thoại bàn cần nằm trong khoảng :min đến :max số',
        'home_phone.company_phone'   => 'Số điện thoại cơ quan cần nằm trong khoảng :min đến :max số',
        'website.url'                => 'Website không đúng định dạng',
        'dob.date_format'            => 'Ngày sinh không đúng định dạng Y-m-d',
        'job'                        => 'Nghề nghiệp cần nhỏ hơn :max kí tự',
        'address'                    => 'Địa chỉ cần nhỏ hơn :max kí tự',
        'company_address'            => 'Địa chỉ cơ quan cần nhỏ hơn :max kí tự',
    ];

    public function __construct(
        CustomerRepository $customer, 
        CustomerTransformer $transformer, 
        CgroupRepository $cgroup, 
        CommentRepository $comment,
        PhoneCallHistoryRepository $phoneCall)
    {
        $this->customer  = $customer;
        $this->cgroup    = $cgroup;
        $this->comment   = $comment;
        $this->phoneCall = $phoneCall;
        $this->setTransformer($transformer);
        $this->checkPermission('customer');
    }

    public function getResource()
    {
        return $this->customer;
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
        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->getResource()->storeOrUpdate($request->all());

            \DB::commit();
            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data = array_except($data, ['email', 'level', 'last_payment']);
            $model = $this->getResource()->update($id, $data);

            \DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function uploadAvatar (Request $request) {
        try {
            $this->validate($request, [
                'files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120'
            ], [
                'files.*.image'    => 'File upload không đúng định dạng',
                'files.*.mimes'    => 'File upload phải là 1 trong các định dạng: :values',
                'files.*.max'      => 'File upload không thể vượt quá :max KB',
                'file.image'    => 'File upload không đúng định dạng',
                'file.mimes'    => 'File upload phải là 1 trong các định dạng: :values',
                'file.max'      => 'File upload không thể vượt quá :max KB',
            ]);
            if ($request->file('file')) {
                $image = $request->file('file');
            } else {
                $image = $request->file('files')[0];
            }
            return $this->getResource()->upload($image);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
    }

    public function importExcel(Request $request)
    {
        $excelPath = $request->file('file')->store('excel');
        $params = array_except($request->all(), ['file']);

        try {
            $job = new ImportCsvCustomer($excelPath, $params, getCurrentUser()->id);
            dispatch($job)->onQueue(env('APP_NAME'));
        } catch (\Exception $e) {
            throw $e;
        }

        $response = array_merge([
            'code'   => 200,
            'status' => 'success',
        ]);
        return response()->json($response, $response['code']);
    }

    public function exportExcel(Request $request)
    {
        $validationRules = ['fields' => 'required|array|min:1'];
        $validationMessages = [
            'fields.required' => 'Chưa có thông tin dữ liệu cần xuất',
            'fields.array'    => 'Thông tin dữ liệu phải ở dạng mảng',
            'fields.min'      => 'Cần có ít nhất một thông tin để xuất dữ liệu'
        ];
        try {
            $this->validate($request, $validationRules, $validationMessages);
            $params = $request->all();
            $ableFields = ['uuid', 'name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'level'];
            foreach ($params['fields'] as $key => $field) {
                if (!in_array($field, $ableFields)) {
                    $params['fields'] = array_except($params['fields'], [$key]);
                }
            }
            $datas = $this->getResource()->exportExcel($params, -1);
            $rowPointer = 2;

            $pathToFile = Excel::create('Khach_hang_' . time(), function($excel) use ($rowPointer, $datas, $params) {
                // Set the title
                $excel->setTitle('Dữ liệu khách hàng ' . time());
                $excel->setCreator('Havaz')
                      ->setCompany('Havaz.vn');
                $excel->setDescription('Customers by Havaz');

                $excel->sheet('Sheet 1', function($sheet) use ($rowPointer, $datas, $params) {
                    $sheet->freezeFirstRow();
                    $sheet->setFontFamily('Roboto');
                    $sheet->setHeight(1, 25);
                    $sheet->row(1, $params['fields']);
                    foreach ($datas as $key => $customer) {
                        $sheet->row($rowPointer, array_values($customer->toArray()));
                        $rowPointer++;
                    }
                });

            })->store('xlsx', storage_path('/app/public/excels'), true);

            $path = "excels/{$pathToFile['file']}";
            return $this->infoResponse([
                'full' => env('APP_URL') . "/storage/" . $path,
                'path' => env('APP_URL') . "/storage/excels",
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function storeComment($id, Request $request)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        try {
            $this->validate(
                $request,
                ['content'          => 'required'],
                ['content.required' => 'Nội dung không được để trống']
            );
            $params = array_only($request->all(), ['content']);
            $params['commentable_id']   = $data->id;
            $params['commentable_type'] = 'Nh\Repositories\Customers\Customer';
            $model = $this->comment->store($params);

            \DB::commit();
            return $this->infoResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Lấy tất cả lịch sử tương tác với lead
     * @param  String    $id
     * @return Array     $histories
     */
    public function loadHistories($id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }
        // dd($data->tickets);
        $activities = [];
        // Danh sách cuộc gọi
        $phones   = [];
        if (!is_null($data->phone)) {
            $phones = $this->phoneCall->getByQuery(['phone' => $data->phone], -1);
        }

        $comments = $data->comments;
        foreach ($phones as $phone) {
            $type = $phone->call_type == \Nh\Repositories\PhoneCallHistories\PhoneCallHistory::CALL_OUT ? 'Gọi đi' : 'Gọi đến';
            array_push($activities, [
                'type'      => 'phone',
                'time'      => $phone->created_at->format('Y-m-d H:i:s'),
                'content'   => $type
            ]);
        }
        foreach ($comments as $comment) {
            array_push($activities, [
                'type'      => 'comment',
                'time'      => $comment->created_at->format('Y-m-d H:i:s'),
                'content'   => '<p>' . $comment->content . '</p>' 
                            .  '<small>Người thực hiện: ' . $comment->user->name . '</small>'
            ]);
        }
        foreach ($data->tickets as $ticket) {
            array_push($activities, [
                'type'      => 'ticket',
                'time'      => $ticket->created_at->format('Y-m-d H:i:s'),
                'content'   => '<p>' . $ticket->name . '</p>'
                                . '<small>Người thực hiện: ' . implode(", ", array_pluck($ticket->users->toArray(), 'name')) . '</small><br>'
                                . '<small>' . $ticket->description . '</small>'
            ]);
        }
        $activities = array_sort($activities, function ($value) {
            return $value['time'];
        });
        $activities = array_values($activities);
        return $this->infoResponse(array_reverse($activities));
    }

    /**
     * Gửi email cho lead
     * @param  Integer  $id      
     * @param  Request  $request
     * @return Response
     */
    public function sendEmail($id, Request $request)
    {
        if (!$customer = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }
        if (is_null($customer->email)) {
            return $this->errorResponse(['errors' => ['email' => ['Khách hàng này không có email !']]]);
        }

        $content = array_get($request->all(), 'content', null);
        $subject = array_get($request->all(), 'subject', null);
        if (is_null($content)) {
            return $this->errorResponse(['errors' => ['email' => ['Khách hàng này không có email!']]]);
        }
        if (is_null($subject)) {
            return $this->errorResponse(['errors' => ['subject' => ['Chủ đề không được để trống!']]]);
        }

        $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
        $html = str_replace('***name***', $customer->name, $content);
        $response = $mailer->revicer($customer->email)->subject($subject)->content($html)->sent();
        if (!is_null($response) && $response->success()) {
            $params = [
                'content'           => '<p>Gửi email</p><small>' . $html . '</small>',
                'commentable_id'    => $customer->id,
                'commentable_type'  => 'Nh\Repositories\Customers\Customer'
            ];
            $model = $this->comment->store($params);
            $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
            return $this->infoResponse(['id' => $response->getData()['Sent'][0]['MessageID']]);
        }
        return $this->notFoundResponse();
    }

}

<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Leads\LeadRepository;
use Nh\Repositories\Comments\CommentRepository;
use Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository;
use Nh\Http\Transformers\LeadTransformer;

class LeadController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $lead;
    protected $comment;
    protected $phoneCall;

    protected $validationRules = [
        'name'            => 'required|min:5|max:255',
        'email'           => 'nullable|required_without_all:phone|email|max:255',
        'phone'           => 'nullable|required_without_all:email|digits_between:8,12',
        'dob'             => 'nullable|date_format:Y-m-d',
        'gender'          => 'numeric',
        'source'          => 'numeric'
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
        'dob.date_format'            => 'Ngày sinh không đúng định dạng Y-m-d',
        'gender.numeric'             => 'Giới tính chưa đúng định dạng',
        'source.numeric'             => 'Nguồn lead chưa đúng định dạng'
    ];

    public function __construct(
        LeadRepository $lead,
        CommentRepository $comment,
        PhoneCallHistoryRepository $phoneCall,
        LeadTransformer $transformer)
    {
        $this->lead    = $lead;
        $this->comment = $comment;
        $this->phoneCall = $phoneCall;
        $this->setTransformer($transformer);
        $this->checkPermission('lead');
    }

    public function getResource()
    {
        return $this->lead;
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

    public function update($id, Request $request)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = array_only($request->all(), ['name', 'dob', 'gender', 'address', 'city_id', 'ip', 'facebook', 'quality', 'status']);
            $model = $this->getResource()->update($id, $params);

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

    /**
     * Thêm một ghi chú cho lead này
     * @param  String   $id     
     * @param  Request  $request
     * @return Model    $comment
     */
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
            $params['commentable_type'] = 'Nh\Repositories\Leads\Lead';
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
        if (!$lead = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }
        if (is_null($lead->email)) {
            return $this->errorResponse(['errors' => ['email' => ['Liên hệ này không có email !']]]);
        }

        $content = array_get($request->all(), 'content', null);
        $subject = array_get($request->all(), 'subject', null);
        if (is_null($content)) {
            return $this->errorResponse(['errors' => ['email' => ['Liên hệ này không có email!']]]);
        }
        if (is_null($subject)) {
            return $this->errorResponse(['errors' => ['subject' => ['Chủ đề không được để trống!']]]);
        }

        $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
        $html = str_replace('***name***', $lead->name, $content);
        $response = $mailer->revicer($lead->email)->subject($subject)->content($html)->sent();
        if (!is_null($response) && $response->success()) {
            $params = [
                'content'           => '<p>Gửi email</p><small>' . $html . '</small>',
                'commentable_id'    => $lead->id,
                'commentable_type'  => 'Nh\Repositories\Leads\Lead'
            ];
            $model = $this->comment->store($params);
            $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
            return $this->infoResponse(['id' => $response->getData()['Sent'][0]['MessageID']]);
        }
        return $this->notFoundResponse();
    }

}

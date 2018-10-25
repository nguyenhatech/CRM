<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Excel;

class ImportCsvCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $filePath;
    protected $data;
    protected $userId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, $data, $userId)
    {
        $this->filePath   = $path;
        $this->data       = $data;
        $this->userId     = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->data;
        // Tạo nhóm cho tập khách hàng
        $group = false;
        $groupRepo = \App::make('Nh\Repositories\Cgroups\CgroupRepository');
        if (array_key_exists('group_id', $request)) {
            $group = $groupRepo->getById($request['group_id']);
        }
        if (array_key_exists('group_name', $request)) {
            $params = [
                'name'              => $request['group_name'],
                'description'       => $request['group_description'],
                'method_input_type' => 2,
                'customers'         => [],
                'client_id'         => $this->userId
            ];
            $group = $groupRepo->store($params);
        }

        // Import customer
        Excel::selectSheetsByIndex(0)->load('storage/app/' . $this->filePath, function ($reader) use ($request, $group) {
            $results = $reader->get();
            foreach ($results as $key => $row) {
                $params = ['client_id' => $this->userId];
                $params['name']     = array_get($row, formatToTextSimple($request['name']), '');
                $params['phone']    = array_get($row, formatToTextSimple($request['phone']), '');
                $params['address']  = array_get($row, formatToTextSimple($request['address']), '');
                $params['email']    = array_get($row, formatToTextSimple($request['email']), '');
                $params['sex']      = array_get($row, formatToTextSimple($request['sex']), -1);
                $params['identification_number'] = array_get($row, formatToTextSimple($request['identification_number']), null);

                $params['phone'] = '0' . strval(intval($params['phone'])); // Chuẩn hóa phone
                if ($params['name'] && $params['phone'] != '00') {
                    $customerRepo = \App::make('Nh\Repositories\Customers\CustomerRepository');
                    $customer = $customerRepo->storeOrUpdate($params);
                    // sync group
                    if ($group) {
                        $customer->groups()->attach([$group->id]);
                    }
                }
            }
        });

        

        Storage::delete($this->filePath);
    }
}

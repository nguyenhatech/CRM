<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('permissions')->delete();

        $datas = [];

        $moduleNames = [
            'user',
            'campaign',
            'cgroup',
            'customer',
            'email_template',
            'webhook',
            'role',
            'permission',
            'payment_history',
            'promotion',
        ];

        $moduleVnNames = [
            'Tài khoản',
            'Chiến dịch',
            'Nhóm khách hàng',
            'Khách hàng',
            'Mẫu email',
            'Chiến dịch',
            'Nhóm quyền truy cập',
            'Quyền truy cập',
            'Lịch sử  giao dịch',
            'Mã giảm giá',
        ];

        $perNames = ['index', 'show', 'store', 'update', 'destroy'];

        $perVnNames = ['Xem', 'Chi tiết', 'Thêm', 'Sửa', 'Xóa'];

        $id = 1;
        foreach ($moduleNames as $modKey => $moduleName) {
            foreach ($perNames as $perKey => $perName) {
                $name = "{$moduleName}.{$perName}";

                $modVn = array_get($moduleVnNames, $modKey, '');
                $perVn = array_get($perVnNames, $perKey, '');
                $display_name = "{$perVn} {$modVn}";

                $datas[] = [
                    'id'           => $id,
                    'name'         => $name,
                    'display_name' => $display_name,
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ];
                $id++;
            }
        }

        \DB::table('permissions')->insert($datas);
    }
}

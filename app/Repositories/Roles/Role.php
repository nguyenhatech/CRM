<?php

namespace Nh\Repositories\Roles;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['name', 'display_name', 'description', 'type'];

    const TYPE_SYSTEM = 1;// Loại quyền do hệ thống tạo ra user sẽ không thấy được các quyền này
    const TYPE_USER = 0;// Loại quyền do user tạo ra

    public function users()
    {
        return $this->belongsToMany('Nh\User', 'role_user', 'role_id', 'user_id', 'id');
    }
}
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
    public $fillable = ['name', 'display_name', 'description'];

    public function users()
    {
        return $this->belongsToMany('Nh\User', 'role_user', 'role_id', 'user_id', 'id');
    }
}
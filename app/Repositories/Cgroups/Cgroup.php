<?php

namespace Nh\Repositories\Cgroups;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cgroup extends Entity
{
	use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'client_id', 'avatar', 'name', 'description'];

    /**
     * Full path of images.
     */
    public $imgPath = 'storage/images/cgroups';

    /**
     * Physical path of upload folder.
     */
    public $uploadPath = 'app/public/images/cgroups';

    /**
     * Image width.
     */
    public $imgWidth = 200;

    /**
     * Image height.
     */
    public $imgHeight = 200;

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    public function getAvatar()
    {
        return $this->avatar == '' ? get_asset('/assets/avatar_default.png') : get_asset($this->imgPath . '/' . $this->avatar);
    }

    public function customers()
    {
        return $this->belongsToMany('Nh\Repositories\Customers\Customer', 'customer_cgroups', 'cgroup_id', 'customer_id');
    }
}
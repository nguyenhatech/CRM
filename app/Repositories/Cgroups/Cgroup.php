<?php

namespace Nh\Repositories\Cgroups;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cgroup extends Entity
{
	use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'client_id', 'avatar', 'name', 'description', 'filter'];

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

        // Con Hà Sơn Hải Vân là không có theo kiểu nhiều merchant
        // static::addGlobalScope('cgroups', function (Builder $builder) {
        //     if (getCurrentUser() && !getCurrentUser()->isAdmin()) {
        //         $builder->where('client_id', getCurrentUser()->id);
        //     }
        // });

        parent::boot();
    }

    public function getAvatar()
    {
        return $this->avatar == '' ? get_asset('avatar_default.png') : get_asset($this->imgPath . '/' . $this->avatar);
    }

    public function customers()
    {
        return $this->belongsToMany('Nh\Repositories\Customers\Customer', 'customer_cgroups', 'cgroup_id', 'customer_id');
    }

    public function attributes()
    {
        return $this->hasMany('Nh\Repositories\CgroupAttributes\CgroupAttribute', 'cgroup_id', 'id');
    }
}

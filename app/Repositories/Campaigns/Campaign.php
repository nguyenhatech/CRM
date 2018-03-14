<?php

namespace Nh\Repositories\Campaigns;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\Builder;

class Campaign extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id','template_id', 'template','cgroup_id','uuid','name','description', 'status', 'target_type', 'sms_template', 'period', 'runtime', 'email_id', 'sms_id'];

    public $dates = ['start_date', 'end_date'];

    const PEDDING = 0;
    const RUNING  = 1;

    const GROUP_TARGET  = 1;
    const MANUAL_TARGET = 2;
    const FILTER_TARGET = 3;

    const DAY_PERIOD   = 1;
    const WEEK_PERIOD  = 2;
    const MONTH_PERIOD = 3;

    const LIST_STATUS = [
        self::PEDDING => 'Ngừng chạy',
        self::RUNING  => 'Đang chạy'
    ];

    const TARGET_TYPE = [
        self::FILTER_TARGET => 'Bộ lọc',
        self::GROUP_TARGET  => 'Nhóm khách hàng',
        self::MANUAL_TARGET => 'Chọn từ danh sách'
    ];

    const PERIODS = [
        0                   => 'Một lần',
        self::DAY_PERIOD    => 'Mỗi ngày',
        self::WEEK_PERIOD   => 'Mỗi tuần',
        self::MONTH_PERIOD  => 'Mỗi tháng'
    ];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        static::addGlobalScope('campaigns', function (Builder $builder) {
            if (getCurrentUser() && !getCurrentUser()->isAdmin()) {
                $builder->where('client_id', getCurrentUser()->id);
            }
        });

        parent::boot();
    }

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getPeriodText()
    {
        return self::PERIODS[$this->period];
    }

    public static function getListPeriod()
    {
        return self::PERIODS;
    }

    public function client()
    {
        return $this->belongsTo('Nh\User', 'client_id', 'id');
    }

    public function cgroup()
    {
        return $this->belongsTo('Nh\Repositories\Cgroups\Cgroup', 'cgroup_id', 'id');
    }

    public function email_template()
    {
        return $this->belongsTo('Nh\Repositories\EmailTemplates\EmailTemplate', 'template_id', 'id');
    }

    public function customers() {
        return $this->belongsToMany('Nh\Repositories\Customers\Customer', 'customer_campaigns', 'campaign_id', 'customer_id');
    }

}

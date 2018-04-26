<?php

namespace Nh\Repositories\PaymentHistoryCodes;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentHistoryCode extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['payment_history_id', 'promotion_code'];

    public $timestamps = false;

    public $table = 'payment_history_codes';

    public function payment_history()
    {
        return $this->belongsTo('Nh\Repositories\PaymentHistories\PaymentHistory', 'payment_history_id', 'id');
    }
}

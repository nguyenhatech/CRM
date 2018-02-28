<?php

namespace Nh\Repositories\EmailTemplates;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id', 'uuid', 'name', 'template'];

    /**
     * Full path of images.
     */
    public $imgPath = 'storage/images/email-templates';

    /**
     * Physical path of upload folder.
     */
    public $uploadPath = 'app/public/images/email-templates';

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    public function client() {
        return $this->belongsTo('Nh\User', 'client_id');
    }
}

<?php

namespace Nh\Repositories\Comments;

use Nh\Repositories\Entity;

class Comment extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['user_id', 'content', 'commentable_id', 'commentable_type'];

    public function commentable()
    {
    	return $this->morphTo();
    }

    public function user()
    {
        return $this->hasOne('Nh\User', 'id', 'user_id');
    }
}

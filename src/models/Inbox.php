<?php

namespace Art\models;

class Inbox extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'inbox';
  public $timestamps = false;

  public function user(){
    return $this->belongsTo('Art\models\User', 'user_id');
  }

  public function actor(){
    return $this->belongsTo('Art\models\Actor', 'remote_actor_id');
  }
}

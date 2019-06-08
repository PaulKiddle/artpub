<?php

namespace Art\models;

class Subscriber extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'subscriber';
  public $timestamps = false;

  public function actor(){
    return $this->belongsTo('Art\models\Actor', 'remote_actor_id');
  }
}

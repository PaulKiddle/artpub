<?php

namespace Art\models;

class Following extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'following';
  public $timestamps = false;

  public function user(){
    return $this->belongsTo('Art\models\User', 'user_id');
  }
}

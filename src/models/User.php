<?php

namespace Art\models;

class User extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'user';

  public function submissions(){
    return $this->hasMany('Art\models\Submission', 'author_id');
  }
}

<?php

namespace Art\models;

class Submission extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'submission';
  public $timestamps = false;

  public function artist(){
    return $this->belongsTo('Art\models\User', 'author_id');
  }

  public function files(){
    return $this->hasMany('Art\models\File', 'submission_id');
  }

  function getUrl() {
    $httpHost = $_SERVER['HTTP_HOST'];
    return "https://$httpHost/submission/{$this->id}/";
  }

  function getThumb(){
    return $this->files()->first();
  }
}

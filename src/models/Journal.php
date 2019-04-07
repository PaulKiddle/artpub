<?php

namespace Art\models;

class Journal extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'journal';

  public function author(){
    return $this->belongsTo('Art\models\User', 'author_id');
  }

  function getUrl() {
    $httpHost = $_SERVER['HTTP_HOST'];
    return "https://$httpHost/journal/{$this->id}/";
  }
}

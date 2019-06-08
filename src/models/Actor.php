<?php

namespace Art\models;

class Actor extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'remote_actor';
  public $timestamps = false;

  protected $fillable = ['url'];

  public static function fromUrl($url, $props = null) {
    $act = \Art\models\Actor::firstOrNew(['url' => $url]);

    if(!$act->id && !$props) {
      $get_opts = array(
        'http'=>array(
          'method'=>"GET",
          'header'=>"Accept: application/json\r\n"
        )
      );
      $props = json_decode(file_get_contents($key_id, false, stream_context_create($get_opts)), true);
    }

    if($props) {
      error_log(print_r($props), 1);
      $act->inbox = $props['inbox'];
      $act->username = $props['preferredUsername'];
      $act->display_name = $props['name'] ?? $props['preferredUsername'];
      $act->avatar_url = isset($props['icon']['url']) ? $props['icon']['url'] : NULL;
      $act->shared_inbox = isset($props['endpoints']['sharedInbox']) ? $props['endpoints']['sharedInbox'] : null;
      $act->save();
    }

    return $act;
  }
}

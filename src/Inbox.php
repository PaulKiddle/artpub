<?php
namespace Art;

require('components/thumb.php');

function verify_sig($req){
  $signature = $req->getHeaderLine('signature');
  $signature_header = [];

  foreach(explode(',', $signature) as $pair) {
    list($key, $value) = explode('=', $pair);
    $signature_header[trim($key, '"')] = trim($value, '"');
  }

  $key_id = $signature_header['keyId'];
  $headers = $signature_header['headers'];
  $signature = base64_decode($signature_header['signature']);

  $get_opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Accept: application/json\r\n"
    )
  );
  $actor = json_decode(file_get_contents($key_id, false, stream_context_create($get_opts)), true);
  $key = $actor['publicKey']['publicKeyPem'];
  $comparison_string = implode("\n", array_map(
    function($signature_header_name) use($req) {
      if($signature_header_name === '(request-target)') {
        $request_target = $req->getUri()->getPath();
        return "(request-target): post $request_target";
      } else {
        return $signature_header_name . ": " . $req->getHeaderLine($signature_header_name);
      }
    },
    explode(' ', $headers)
  ));
  //error_log(print_r([$comparison_string, $signature, $key], true));

  return openssl_verify($comparison_string, $signature, $key, OPENSSL_ALGO_SHA256) ? $actor : false;
}

class Inbox extends Route {
  function view ($req, $res, $args) {
    $user_id = $args['id'];

    if(!($actor = verify_sig($req))) {
      return $res->withStatus(401);
    }

    $user = \Art\models\User::where('username', $user_id)->first();

    $json = $req->getBody();
    $data = json_decode($json, true);
    $type = strtolower($data['type']);

    $act = \Art\models\Actor::fromUrl($actor['id'], $actor);

    switch($type){
      case 'follow':
        $user->send($user->activity("Accept", $data), $actor['inbox'], $actor['id']);
        $sub = new \Art\models\Subscriber();
        $sub->user_id = $user->id;
        $sub->remote_actor_id = $act->id;
        try {
          $sub->save();
        } catch (\Illuminate\Database\QueryException  $e) {
          // Probably duplicate key where subscription has previously failed
          error_log("Caught $e");
        }
        $user->addNote(
          $actor['preferredUsername'] . ' followed you',
          $actor['url'],
          $act->id
        );
        break;
      case 'accept':
        $follow = $user->following->where('remote_actor_id', $act->id)->first();
        $follow->accepted = 1;
        $follow->save();
        break;
      case 'create':
        $following = $user->following->where('remote_actor_id', $act->id)->first();
        if(isset($data['object']['attachment'][0])) {
          $thumb = $data['object']['attachment'][0];
          $sub = [
            "thumb" => $thumb['url'],
            "url" => $data['object']['url'],
            "title" => '',
            "type" => explode("/", $thumb['mediaType'])[0]
          ];
          $thumb = thumb($thumb);
        }else {
          $thumb = '';
        }
        $user->addNote(
          'New ' . $data['object']['type'] . ' created: ' . $data['object']['content'] . $thumb,
          $data['object']['url'] ?? $data['object']['id'],
          \Art\models\Actor::fromUrl($data['object']['attributedTo'])->id
        );
        break;
    }
    return $res->withStatus(200);
  }
}

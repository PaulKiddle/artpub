<?php
namespace Art;

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

    switch($type){
      case 'follow':
        $user->send($user->activity("Accept", $data), $actor['inbox']);
        $sub = new \Art\models\Subscriber();
        $sub->url = $actor['id'];
        $sub->inbox = $actor['inbox'];
        $sub->user_id = $user->id;
        $sub->save();
      case 'accept':
        $follow = $user->following->where('url', $actor['id'])->first();
        $follow->accepted = 1;
        $follow->save();
      case 'create':
        $following = $user->following->where('url', $actor['id'])->first();
        $create = new \Art\models\Inbox();
        $create->message = 'New ' . $data['object']['type'] . ' created:' . $data['object']['content'];
        $create->url = $data['object']['url'];
        $create->following_id = $following->id;
        $create->user_id = $user->id;
        $create->save();
        error_log(print_r($data, true));
    }
    return $res->withStatus(202);
  }
}

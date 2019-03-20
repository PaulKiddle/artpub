<?php
namespace Art;

class Inbox extends Route {
  function view ($req, $res, $args) {
    $user = \Art\models\User::where('username', $args['id'])->first();
    $json = $req->getBody();
    $data = json_decode($json, true);

    error_log(print_r($data, true));

    $type = strtolower($data['type']);

    error_log($type);

    return $res->withStatus(501);
  }
}

<?php
namespace Art;

class User extends Route {
  function view ($req, $res, $args) {
    $user = \Art\models\User::where('username', $args['id'])->first();
    $username = $user->username;
    $host = $this->host;
    $user_id = "https://$host/user/$username";

    if(!$user->public_key) {
      $user->generateKeypair()->save();
    }

    $json = array(
      '@context' => [
        "https://www.w3.org/ns/activitystreams",
        "https://w3id.org/security/v1"
      ],
      "id" => $user_id,
      "type" => "Person",
      "preferredUsername" => $username,
      "inbox" => "https://$host/user/$username/inbox",
      "publicKey" => [
        "id" => "$user_id#main-key",
        "owner" => $user_id,
        "publicKeyPem" => $user->public_key
      ]
    );

    return $res->withJson($json);
  }
}

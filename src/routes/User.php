<?php
namespace Art\routes;

class User extends Route {
  function view ($req, $res, $args) {
    $user = \Art\models\User::where('username', $args['id'])->first();
    $username = $user->username;
    $user_id = $user->getUrl();

    $json = array(
      '@context' => [
        "https://www.w3.org/ns/activitystreams",
        "https://w3id.org/security/v1"
      ],
      "id" => $user_id,
      "type" => "Person",
      "preferredUsername" => $username,
      "name" => $user->display_name,
      "summary" => $user->summary,
      "inbox" => "$user_id/inbox",
      "followers" => "$user_id/followers",
      "publicKey" => [
        "id" => "$user_id#main-key",
        "owner" => $user_id,
        "publicKeyPem" => $user->public_key
      ],
      "icon" => [
        "type" => "Image",
        "url" => $user->avatar_url
      ]
    );

    return $res->withJson($json);
  }
}

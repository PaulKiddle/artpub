<?php
namespace Art;

class User extends Route {
  function view ($req, $res, $args) {
    $user = $this->user;
    $host = $this->host;

    $json = array(
      '@context' => [
        "https://www.w3.org/ns/activitystreams",
        "https://w3id.org/security/v1"
      ],
      "id" => "http://$host/user/$user",
      "type" => "Person",
      "preferredUsername" => $user,
      "inbox" => "http://$host/user/$user/inbox"
    );
    return $res->withJson($json);
  }
}

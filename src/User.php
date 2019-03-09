<?php
namespace Art;

class User extends Route {
  function __invoke ($req, $res, $args) {
    $user = $this->container['getUser']($args['id'])['username'];
    $host = $this->container->host;
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

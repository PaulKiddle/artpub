<?php
namespace Art\routes;

class Followers extends Route {
  function view ($req, $res, $args) {
    $user = \Art\models\User::where('username', $args['id'])->first();
    $username = $user->username;
    $user_id = $user->getUrl();

    $json = array(
      '@context' => [
        "https://www.w3.org/ns/activitystreams",
        "https://w3id.org/security/v1"
      ],
      "type" => "OrderedCollection",
      "totalItems" => $user->subscribers()->count(),
      "id" => "$user_id/followers",
      "first" => [
        "type" => "OrderedCollectionPage",
        "totalItems" => $user->subscribers()->count(),
        "partOf" => "$user_id/followers",
        "orderedItems" =>  $user->subscribers()->get()->map(function($subscriber){
          return $subscriber->actor->url;
        }),
        "id" => "$user_id/followers?page=1"
      ]
    );

    return $res->withJson($json);
  }
}

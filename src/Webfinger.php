<?php
namespace Art;

class Webfinger extends Route {
  function __invoke ($request, $response, $args) {
    $resource = $request->getQueryParam('resource');
    list($scheme, $account) = explode(':', $resource);
    list($user, $domain) = explode('@', $account);

    $user = \Art\models\User::where('username', [$user])->first();
    $username = $user['username'];
    $profile = $user->getUrl();
    $json = array(
      'subject' => $resource,
      'aliases' => [$profile],
      'links' => [
        [
          'rel' => "self",
          'type' => 'application/activity+json',
          'href' => $profile
        ]
      ]
    );
    return $response->withJson($json);
  }
}

<?php

namespace Art;

require('components/thumb.php');
require('components/gallery.php');
require('components/page.php');

class Index extends Route {
  function submit ($request, $response) {
    global $config;

    $errors = [];

    $user = \Art\models\User::where('username', $_POST['username'])->first();
    if($user) {
      if($user->checkPassword($_POST['password'])) {
        $this->session['user'] = $user;
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = 'Incorrect username/password';
      }
    } else if(!isset($config['disable_signup'])) {
      $user = new \Art\models\User;
      $user->username = $_POST['username'];
      $user->setPassword($_POST['password']);
      $r = $user->save();
      if($r) {
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = $q->errorInfo();
      }
    } else {
      $errors[] = 'Incorrect username/password';
    }

    $this->errors = $errors;
  }

  function view($request, $response) {
    $router = $this->container->router;

    $output = page($this->user, [
      gallery(
        \Art\models\Submission::all()->map(function($sub) use($router) {
          return thumb($router, $sub);
        })->toArray()
      )
    ], $this->errors);

    return $response->write($output);
  }
}

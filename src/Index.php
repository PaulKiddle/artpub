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

      if (preg_match("/[^A-Za-z0-9]/", $_POST['username'])) {
        $errors[] = 'You may only use letters and numbers for your username';
      } else {
        $user->username = $_POST['username'];
        $user->setPassword($_POST['password']);
        $r = $user->save();
        if($r) {
          return $response->withRedirect($request->getUri()->getPath());
        } else {
          $errors[] = $q->errorInfo();
        }
      }
    } else {
      $errors[] = 'Incorrect username/password';
    }

    $this->errors = $errors;
  }

  function view($request, $response) {
    $router = $this->container->router;

    $output = page($this->user, [
      "<p>This is a new fediverse project currently named 'artpub'. It's a social art hosting and artist development site.",
      "<p>Currently in very early development.",
      "<p>For source code, see <a href='https://github.com/PaulKiddle/artpub'>The GitHub repo</a>",
      "<p>To join as an early test user, message <a href='//kith.kitchen/@paul'>@paul@kith.kitchen</a>.",
      gallery(
        \Art\models\Submission::all()->map(function($submission) use($router) {
          $file = $submission->files()->first();
          $artist = $submission->artist()->first();
          $author = [
            "url" => $router->pathFor('gallery', ['id'=>$artist->id]),
            "name" => $artist->username
          ];
          $sub = [
            "title" => $submission->title,
            "url" => $router->pathFor('submission', ['id'=>$submission->id]),
            "thumb" => "/uploads/$file->file",
            "type" => $submission->type
          ];

          return thumb($sub, $author);
        })->toArray()
      )
    ], $this->errors);

    return $response->write($output);
  }
}

<?php
namespace Art;

require('components/page.php');
require('components/gallery.php');
require('components/thumb.php');

class Gallery extends Route {
  function view ($req, $res, $args) {
    $user = \Art\models\User::where('id', $args['id'])->first();

    $output = [];

    foreach($user->submissions()->get() as $submission) {
      $output[] = thumb($this->container->router, $submission);
    }

    return $res->write(page($user, ["<h1>$user->username's gallery</h1>", gallery($output)]));
  }
}

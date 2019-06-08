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
      $this->container->router;
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

      $output[] = thumb($sub, $author);
    }

    $journals = [];
    foreach($user->journals()->get() as $journal) {
      $journals[] = "<li><a href='{$journal->getUrl()}'>$journal->title</a>";
    }

    return $res->write(page($user,
      [
        "<h1>$user->username's gallery</h1>",
        "<p>Follow with ActivityPub: <i>{$user->getWebfinger()}</i></p>",
        gallery($output),
        "<ul>", implode('', $journals), "</ul>"
      ]));
  }
}

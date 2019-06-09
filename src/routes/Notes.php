<?php

namespace Art\routes;

require(__DIR__.'/../components/page.php');
require(__DIR__.'/../components/note.php');

class Notes extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function view($request, $response, $args){
    $note = $request->getParam('delete');

    if($note){
      $note = \Art\models\Inbox::where('id', $note)->first();
      if($note && $note->user_id === $this->user->id) {
        $note->delete();
      }
    }
    $box = '';

    foreach($this->user->inbox()->get() as $note) {
      $box .= note($note);
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $box
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

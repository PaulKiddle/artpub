<?php

namespace Art;

require('components/page.php');

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
      if($note->user_id === $this->user->id) {
        $note->delete();
      }
    }
    $box = '';

    foreach($this->user->inbox()->get() as $note) {
      $box .= '<li><a href="' . $note->url . '">' . $note->message . '</a> by '. $note->actor_url . "<a href='?delete={$note->id}'>x</a>";
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $box
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

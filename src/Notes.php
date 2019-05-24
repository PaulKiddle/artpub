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
      if($note && $note->user_id === $this->user->id) {
        $note->delete();
      }
    }
    $box = '';

    foreach($this->user->inbox()->get() as $note) {
      $box .= "<li style='display:flex;border:1px solid black;width:50%;margin:auto;'><div>"
	. $note->actor_url .
        "</div><div style='flex-grow:1'>" .$note->message."</div><div>"

        ."<a href='"
        . $note->url . "'>Link</a><br>"
        . "<a href='?delete={$note->id}'>x</a></div></li>";
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $box
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

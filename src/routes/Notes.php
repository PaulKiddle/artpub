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

  function submit($request, $response) {
    $note = $request->getParam('delete');

    if($note == 'all') {
      \Art\models\Inbox::where('user_id', $this->user->id)->delete();
    } else if ($note) {
      \Art\models\Inbox::where([
        'id' => $note,
        'user_id' => $this->user->id
      ])->delete();
    }
  }

  function view($request, $response, $args){
    $box = '';

    foreach($this->user->inbox()->get() as $note) {
      $box .= note($note);
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $box
      <form method="post">
        <input type="hidden" name="submit" value="ok">
        <button name="delete" value="all">Clear all</button>
      </form>
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

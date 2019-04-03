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

  function view($request, $response){
    $box = '';

    foreach($this->user->inbox()->get() as $note) {
      $box .= '<li><a href="' . $note->url . '">' . $note->message . '</a> by '. $note->author()->first()->username;
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $box
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

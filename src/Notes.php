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
    $output = '';

    foreach($this->user->inbox as $note) {
      $output .= print_r($note, true);
    }

    $output = <<<HTML
      <h1>Inbox</h1>
      $output
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

<?php
namespace Art;

class Gallery extends Route {
  function view ($req, $res, $args) {
    $user = $this->user;

    $output = '';

    foreach($user->submissions()->get() as $submission) {
      $output .= <<<HTML
        <div>
          <h2>{$submission->title}</h2>
          <img src="/uploads/{$submission->file}">
        </div>
HTML;
    }

    return $res->write($output);
  }
}

<?php
namespace Art;

class Gallery extends Route {
  function renderImage($submission){
    return "<img src=\"/uploads/{$submission->getThumb()->file}\">";
  }

  function renderAudio($submission){
    return "<audio controls src=\"/uploads/{$submission->getThumb()->file}\">";
  }

  function renderSub($submission){
    switch($submission->type) {
      case 'image':
        return $this->renderImage($submission);
      case 'audio':
        return $this->renderAudio($submission);
      default:
        return "Can't render $type files";
    }
  }

  function view ($req, $res, $args) {
    $user = $this->user;

    $output = '';

    foreach($user->submissions()->get() as $submission) {
      $thumb = $this->renderSub($submission);
      $desc = htmlentities($submission->description);
      $output .= <<<HTML
        <div>
          <h2><a href="{$submission->getUrl()}">{$submission->title}</a></h2>
          $thumb
          <p>$desc</p>
        </div>
HTML;
    }

    return $res->write($output);
  }
}

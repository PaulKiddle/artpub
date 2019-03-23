<?php
namespace Art;

class Submission extends Route {
  function renderImage($submission){
    return "<img src=\"/uploads/{$submission->file}\">";
  }

  function renderAudio($submission){
    return "<audio controls src=\"/uploads/{$submission->file}\"></audio>";
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
    $submission = \Art\models\Submission::where('id', $args['id'])->first();

    $thumb = $this->renderSub($submission);
    $desc = htmlentities($submission->description);
    $output = <<<HTML
      <div>
        <h2><a href="{$submission->getUrl()}">{$submission->title}</a></h2>
        $thumb
        <p>$desc</p>
      </div>
HTML;

    return $res->write($output);
  }
}

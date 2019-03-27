<?php
namespace Art;

class Submission extends Route {
  function renderImage($file){
    return "<img src=\"/uploads/{$file->file}\">";
  }

  function renderAudio($file){
    return "<audio controls src=\"/uploads/{$file->file}\"></audio>";
  }

  function renderText($file){
    return "<iframe sandbox src=\"/uploads/{$file->file}\"></iframe>";
  }

  function renderSub($file){
    $type = explode("/", $file->media_type)[0];
    switch($type) {
      case 'image':
        return $this->renderImage($file);
      case 'audio':
        return $this->renderAudio($file);
      case 'text':
        return $this->renderText($file);
      default:
        return "Can't render $type files";
    }
  }

  function view ($req, $res, $args) {
    $submission = \Art\models\Submission::where('id', $args['id'])->first();

    $rendered = [];
    foreach($submission->files()->get() as $file){
      $rendered[] = $this->renderSub($file);
    }
    $content = implode("\n", $rendered);
    $desc = htmlentities($submission->description);
    $output = <<<HTML
      <div>
        <h2><a href="{$submission->getUrl()}">{$submission->title}</a></h2>
        $content
        <p>$desc</p>
      </div>
HTML;

    return $res->write($output);
  }
}

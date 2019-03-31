<?php
namespace Art;

require('components/page.php');

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
    $author = $submission->artist()->first()->username;

    $rendered = [];
    foreach($submission->files()->get() as $file){
      $rendered[] = $this->renderSub($file);
    }
    $content = implode("\n", $rendered);
    $desc = htmlentities($submission->description);
    $output = <<<HTML
      <style>
      .Submission {
        display: flex;
        flex-direction: column;
      }

      .Submission__content {
        background: #333;
        min-height: 250px;
        display: grid;
        align-items: center;
        padding-bottom: 20px;
      }

      .Submission__content > * {
        max-width: 100%;
      }

      .Submission audio {
        height: 40px;
      }

      .Submission iframe {
        background: white;
        justify-self: center;
      }

      .Submission__desc {
        min-height: 250px;
        width: 80%;
        margin: auto;
      }
      </style>
      <div class="Submission">
        <div class="Submission__content">$content</div>
        <div class="Submission__desc">
          <h1>{$submission->title}</h1>
          <h2>{$author}</h2>
          <p>$desc</p>
        </div>
      </div>
HTML;

    return $res->write(page($this->user, [$output]));
  }
}

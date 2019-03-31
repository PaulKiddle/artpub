<?php

namespace Art;

require('components/page.php');

class Upload extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function submit($request, $response) {
    $files = $request->getUploadedFiles()['file'];

    $mimeType0 = mime_content_type($_FILES['file']['tmp_name'][0]);
    $type0 = explode('/', $mimeType0)[0];

    $sub = new \Art\models\Submission();
    $sub->author_id = $this->user['id'];
    $sub->title = $_POST['title'];
    $sub->type = $type0;
    $sub->description = $_POST['description'];
    $saved = $sub->save();

    foreach($files as $ix => $file) {
      $mimeType = mime_content_type($_FILES['file']['tmp_name'][$ix]);
      $type = explode('/', $mimeType)[0];

      if(!in_array($type, ['image', 'audio', 'text'])) {
        $this->errors[] = "The uploaded file is $mimeType ($type), which is not allowed.";
        return;
      }

      $uploaddir = 'uploads/';
      $uploadfile =  time() . '-' . basename($file->getClientFilename());
      $file->moveTo($uploaddir . $uploadfile);

      $file = new \Art\models\File();
      $file->media_type = $mimeType;
      $file->file = $uploadfile;
      $file->submission_id = $sub->id;
      $file->save();
    }

    if ($saved) {
      $domain = $_SERVER['HTTP_HOST'];

      $attachment = [];

      foreach($sub->files()->get() as $file) {
        $attachment[] = [
          'type' => 'Document', //ucfirst($type),
          'url' => "https://$domain/$uploaddir".$file->file,
          'mediaType' => $file->media_type,
          'name' => $sub->title
        ];
      }

     $url = $sub->getUrl();

      $object = [
        'id' => $sub->getUrl(),
        'type'=> 'Note',
        'published'=> date('c'),
        'attributedTo' => $this->user->getUrl(),
        'content'=> "<a href='$url'>$sub->title</a><p>$sub->description</p>",
        'to'=>'https://www.w3.org/ns/activitystreams#Public',
        'name' => $sub->title,
        'attachment' => $attachment,
        'url' => $url
      ];

      $name = $this->user['username'];
      $this->user->broadcast($this->user->activity('Create', $object));
      return $response->withRedirect('/');
    }

    $this->errors[] = $q->errorInfo()[2];
  }

  function view($request, $response){
    $output = '';

    foreach($this->errors as $err) {
      $output .= "<li>$err</li>";
    }

    if($output){
      $output = "<ul>$output</ul>";
    }

    $output .= <<<HTML
      <style>
      .Upload {
        padding-top: 50px;
        display: flex;
        flex-direction: column;
        width: 50%;
        margin: auto;
      }

      .Upload__field {
        display: block;
        width: 100%;
        padding: 10px;
      }

      .Upload__files {
        box-sizing: content-box;
        background: #CCC;
        height: 100px;
        padding: 10px 0;
        text-align: center;
      }

      .Upload__desc {
        height: 100px;
      }

      .Upload__fieldset {
        margin: 10px;
      }
      </style>
      <form class="Upload" enctype="multipart/form-data" method="POST">
        <h1>Upload</h1>
        <label class="Upload__fieldset">
          Files to upload
          <input type="file" name="file[]" multiple class="Upload__field Upload__files">
        </label>
        <label class="Upload__fieldset">
          Title
          <input name="title" class="Upload__field">
        </label>
        <label class="Upload__fieldset">
          Description
          <textarea name="description" class="Upload__field Upload__desc"></textarea>
        </label>
        <button name="submit" class="Upload__fieldset">Upload</button>
      </form>
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

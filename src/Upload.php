<?php

namespace Art;

class Upload extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function submit($request, $response) {
    $files = $request->getUploadedFiles()['file'];

    $mimeType0 = mime_content_type($files[0]->getClientFilename());
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

      $object = [
        'id' => $sub->getUrl(),
        'type'=> 'Note',
        'published'=> date('c'),
        'attributedTo' => $this->user->getUrl(),
        'content'=> $sub->description,
        'to'=>'https://www.w3.org/ns/activitystreams#Public',
        'name' => $sub->title,
        'attachment' => $attachment,
        'url' => $sub->getUrl()
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
      <form enctype="multipart/form-data" method="POST">
        <input type="file" name="file[]" multiple><br>
        <input name="title"><br>
        <textarea name="description"></textarea>
        <button name="submit">Upload</button>
      </form>
HTML;
    return $response->write($output);
  }
}

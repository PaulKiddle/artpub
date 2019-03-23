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
    if(exif_imagetype($_FILES['file']['tmp_name']) === false) {
      $this->errors[] = 'The uploaded file is not a valid image file';
      return;
    }

    $uploaddir = 'uploads/';
    $uploadfile =  time() . '-' . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir . $uploadfile);

    $sub = new \Art\models\Submission();
    $sub->author_id = $this->user['id'];
    $sub->title = $_POST['title'];
    $sub->file = $uploadfile;

    if ($sub->save()) {
      $guid = time();
      $domain = $_SERVER['HTTP_HOST'];

      $name = $this->user['username'];
      $this->user->broadcast($this->user->activity('Create',
        [
          'id'=>"https://$domain/$guid",
          'type'=>'Note',
          'published'=> date('c'),
          'attributedTo' => $this->user->getUrl(),
          'content'=> $_POST['title'],
          'cc'=>'https://www.w3.org/ns/activitystreams#Public'
        ]
      ));
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
        <input type="file" name="file"><br>
        <input name="title"><br>
        <button name="submit">Upload</button>
      </form>
HTML;
    return $response->write($output);
  }
}

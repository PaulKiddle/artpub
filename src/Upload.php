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

    $q = $this->db->prepare("INSERT INTO submission (author_id, title, file) VALUES(?, ?, ?)");

    if ($q->execute([
      $this->user['id'],
      $_POST['title'],
      $uploadfile
    ])) {
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
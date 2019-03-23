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
    $mimeType = mime_content_type($_FILES['file']['tmp_name']);
    $type = explode('/', $mimeType)[0];

    if(!in_array($type, ['image', 'audio'])) {
      $this->errors[] = "The uploaded file is $mimeType ($type), which is not allowed.";
      return;
    }

    $uploaddir = 'uploads/';
    $uploadfile =  time() . '-' . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir . $uploadfile);

    $sub = new \Art\models\Submission();
    $sub->author_id = $this->user['id'];
    $sub->title = $_POST['title'];
    $sub->file = $uploadfile;
    $sub->type = $type;
    $sub->description = $_POST['description'];

    if ($sub->save()) {
      $domain = $_SERVER['HTTP_HOST'];

      $object = [
        'id' => $sub->getUrl(),
        'type'=> ucfirst($type),
        'published'=> date('c'),
        'attributedTo' => $this->user->getUrl(),
        'content'=> $_POST['title'],
        'cc'=>'https://www.w3.org/ns/activitystreams#Public',
        'name' => $sub->title,
        'url' => [
          [
            'type' => 'Link',
            'href' => "https://$domain/$uploaddir".$uploadfile,
            'mediaType' => $mimeType,
            'rel' => 'self'
          ],
          [
            'type' => 'Link',
            'href' => $sub->getUrl(),
            'mediaType' => 'text/html',
            'rel' => 'describedby'
          ]
        ]
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
        <input type="file" name="file"><br>
        <input name="title"><br>
        <textarea name="description"></textarea>
        <button name="submit">Upload</button>
      </form>
HTML;
    return $response->write($output);
  }
}

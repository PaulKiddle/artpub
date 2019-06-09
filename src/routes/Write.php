<?php

namespace Art\routes;

require(__DIR__.'/../components/page.php');

class Write extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function submit($request, $response) {
    $journal = new \Art\models\Journal();
    $journal->author_id = $this->user['id'];
    $journal->title = $_POST['title'];
    $journal->content = $_POST['content'];
    $saved = $journal->save();

    if ($saved) {
      $domain = $_SERVER['HTTP_HOST'];

      $url = $journal->getUrl();

      $object = [
        'id' => $journal->getUrl(),
        'type'=> 'Note',
        'published'=> date('c'),
        'attributedTo' => $this->user->getUrl(),
        'content'=> $journal->content,
        'to'=>'https://www.w3.org/ns/activitystreams#Public',
        'name' => $journal->title,
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
      .Write {
        padding-top: 50px;
        display: flex;
        flex-direction: column;
        width: 50%;
        margin: auto;
      }

      .Write__field {
        display: block;
        width: 100%;
        padding: 10px;
      }

      .Write__desc {
        height: 200px;
      }

      .Write__fieldset {
        margin: 10px;
      }
      </style>
      <form class="Write" method="POST">
        <h1>Write</h1>
        <label class="Write__fieldset">
          Title
          <input name="title" class="Write__field">
        </label>
        <label class="Write__fieldset">
          Content
          <textarea name="content" class="Write__field Write__desc"></textarea>
        </label>
        <button name="submit" class="Write__fieldset">Write</button>
      </form>
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

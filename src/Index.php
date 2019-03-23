<?php

namespace Art;

class Index extends Route {
  function submit ($request, $response) {
    global $config;

    $errors = [];

    $user = \Art\models\User::where('username', $_POST['username'])->first();
    if($user) {
      if($user->checkPassword($_POST['password'])) {
        $this->session['user'] = $user;
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = 'Incorrect username/password';
      }
    } else if(!isset($config['disable_signup'])) {
      $user = new \Art\models\User;
      $user->username = $_POST['username'];
      $user->setPassword($_POST['password']);
      $r = $user->save();
      if($r) {
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = $q->errorInfo();
      }
    } else {
      $errors[] = 'Incorrect username/password';
    }

    $this->errors = $errors;
  }

  function view($request, $response) {
    $errors = $this->errors;
    $user = $this->user['username'];

    $w = $user ? "Welcome, $user!" : '';

    $output = '';

    foreach($errors as $err) {
      $output .= "<li>$err</li>";
    }

    if($output){
      $output = "<ul>$output</ul>";
    }


    $r = '';

    foreach(\Art\models\Submission::all() as $row) {
      $file = $row['file'];
      $title = $row['title'];
      switch($row['type']){
        case 'image':
          $thumb = "<img src=\"/uploads/$file\">";
          break;
        case 'audio':
          $thumb = "<img alt=\"Audio file\">";
          break;
        default:
          $thumb = "<img alt=\"???\">";
          break;
      }

      $r .= <<<HTML
        <div>
          <h2><a href="{$row->getUrl()}">$title</a></h2>
          <p>{$row->artist()->first()->username}</p>
          $thumb
        </div>
HTML;

    }

    $output .= <<<HTML
  <form method="POST">
    <label>Username <input name="username"></label><br>
    <label>Password <input type="password" name="password"></label>
    <button name="submit">Sign up</button>
  </form>

  $w

  $r

  <ol>
    <li>Allow url slugs
    <li>Improve submission page html
    <li>Broadcast creates as correct object type
    <li>Templates: sanitize, tidy, use form helper
    <li>Follow remote
    <ul>
      <li>Create subscribees table
      <li>Resolve webfinger
      <li>Send follow activity
      <li>Insert into table as pending
      <li>Process accept activity
      <li>Update pending to following
      <li>Create inbox table
      <li>Process create activities
      <li>Insert into inbox table
      <li>Create inbox view
    </ul>
    <li>User roles
    <li>Moderation
    <li>Local follow
    <li>Username rules
    <li>Move getUrl functions to router
  </ol>
HTML;
    return $response->write($output);
  }
}

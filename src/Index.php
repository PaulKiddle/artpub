<?php

namespace Art;

class Index extends Route {
  function submit ($request, $response) {
    $db = $this->container['db'];
    $errors = [];

    $user = \Art\models\User::where('username', $_POST['username'])->first();
    if($user) {
      if(password_verify($_POST['password'], $user['password'])) {
        $this->session['user'] = $user;
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = 'Incorrect username/password';
      }
    } else {
      $q = $db->prepare("INSERT INTO user (username, password) VALUES(?, ?)");
      $r = $q->execute(array(
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT)
      ));
      if($r) {
        return $response->withRedirect($request->getUri()->getPath());
      } else {
        $errors[] = $q->errorInfo();
      }
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

    global $db;
    $q = $db->prepare("SELECT * FROM submission");
    $q->execute(array($user));
    $r = '';

    foreach($q->fetchAll() as $row) {
      $file = $row['file'];
      $title = $row['title'];

      $r .= <<<HTML
        <div>
          <h2>$title</h2>
          <img src="/uploads/$file">
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
    <li>Follow from remote
    <ul>
      <li>Create inbox route
      <li>Create subscribers table
      <li>Process follow activity, insert into subscriptions
      <li>Send accept activity
      <li>Send create activity on upload
    </ul>
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
    <li>Remove PDO db
    <li>Templates
    <li>User roles
    <li>Moderation
    <li>Local follow
  </ol>
HTML;
    return $response->write($output);
  }
}

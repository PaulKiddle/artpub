<?php

function menu($user) {
  $items = "";
  $username = $user->username ? "Welcome, $user->username!" : '';
  $numNotes = $user->inbox()->count();
  if ($numNotes > 0) {
    $numNotes = " ($numNotes)";
  }

  $items .= $username ?
    "$username
    <a class=Menu__upload href=/upload>Upload</a>
    <a class=Menu__link href=/write>Write</a> |
    <a class=Menu__link href=/notes>Inbox</a>$numNotes |
    <a class=Menu__link href=/follow>Follow</a>"
  : "<details>
    <summary>Log in</summary>
    <form method=\"POST\">
      <label>Username <input name=\"username\"></label><br>
      <label>Password <input type=\"password\" name=\"password\"></label><br>
      <button name=\"submit\">Sign up</button>
    </form>
  </details>";

  return <<<END
    <style>
    .Menu {
      display: flex;
      background: #333;
      color: #CCC;
      align-items: center;
      justify-content: flex-end;
    }

    .Menu > * {
      margin: 10px;
    }

    .Menu__link {
      color: #ccc;
    }

    .Menu__upload {
      display: inline-block;
      background: #00CC00;
      color: black;
      padding: 10px;
      border-radius: 5px;
      text-decoration: none;
    }

    .Menu__upload:hover {
      text-decoration: underline;
    }

    .Menu__title {
      margin-right: auto;
      font-weight: bold;
      color: inherit;
    }
    </style>
    <div class="Menu">
      <a href="/" class="Menu__title">ArtPub</a>
      $items
    </div>
  END;
}

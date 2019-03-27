<?php

function menu($user) {
  $username = $user->username ? "Welcome, $user->username!" : '';
  $add = $username ? "<a class=Menu__upload href=/upload>Upload</a>" : '';

  return <<<END
    <style>
    .Menu {
      display: flex;
      background: #333;
      color: #CCC;
      align-items: center;
      justify-content: space-between;
    }

    .Menu__upload {
      display: inline-block;
      background: #99cc00;
      color: black;
      padding: 10px;
      border-radius: 5px;
      text-decoration: none;
    }

    .Menu__upload:hover{
      text-decoration: underline;
    }
    </style>
    <div class="Menu">
        <form method="POST">
        <label>Username <input name="username"></label><br>
        <label>Password <input type="password" name="password"></label>
        <button name="submit">Sign up</button>
      </form>
      {$username}
      $add
    </div>
  END;
}

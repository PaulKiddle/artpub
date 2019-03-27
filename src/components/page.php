<?php

include('menu.php');

function page($user, $content, $errors=[]) {
  $m = menu($user);
  $content = implode("\n", $content);
  $e = '';

  foreach($errors as $err) {
    $e .= "<li>$err</li>";
  }

  if($e){
    $e .= "<ul>$e</ul>";
  }

  return <<<END
    <style>
    body {
      margin: 0;
      padding: 0;
      font-family: sans-serif;
    }
    </style>
    <body>
    $m
    $e
    $content
    </body>
  END;
}

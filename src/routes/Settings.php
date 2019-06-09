<?php

namespace Art\routes;

require(__DIR__.'/../components/page.php');

class Settings extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function submit($request, $response) {
    $this->user['avatar_url'] = $_POST['avatar_url'];
    $this->user['summary'] = $_POST['summary'];
    $this->user['display_name'] = $_POST['display_name'];
    $this->user->save();
    return $response->withRedirect('/settings');
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
      .Settings {
        padding-top: 50px;
        display: flex;
        flex-direction: column;
        width: 50%;
        margin: auto;
      }

      .Settings__field {
        display: block;
        width: 100%;
        padding: 10px;
      }

      .Settings__desc {
        height: 200px;
      }

      .Settings__fieldset {
        margin: 10px;
      }
      </style>
      <form class="Settings" method="POST">
        <h1>Settings</h1>
        <label class="Settings__fieldset">
          Display name
          <input name="display_name" class="Settings__field" value="{$this->user['display_name']}">
        </label>
        <label class="Settings__fieldset">
          Avatar url
          <input name="avatar_url" class="Settings__field" value="{$this->user['avatar_url']}">
        </label>
        <label class="Settings__fieldset">
          About you
          <textarea name="summary" class="Settings__desc">{$this->user['summary']}</textarea>
        </label>
        <button name="submit" class="Settings__fieldset">Save</button>
      </form>
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

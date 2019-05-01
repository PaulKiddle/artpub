<?php

namespace Art;

require('components/page.php');

class Follow extends Route {
  function __invoke ($request, $response, $args) {
    if(!$this->user) {
      return $response->withRedirect('/');
    }

    return parent::__invoke($request, $response, $args);
  }

  function submit($request, $response) {
    $webfinger = trim($_POST['follow'], "@ \t\n\r\0\x0B");

    if(preg_match('/^https?:\/\//', $webfinger)) {
      $actor_url = $webfinger;
      $webfinger = null;
    } else {
      list($user, $domain) = explode('@', $webfinger);
      $url = 'https://' . $domain . '/.well-known/webfinger?resource=acct:' . $webfinger;
      $obj = json_decode(file_get_contents($url));
      foreach($obj->links as $link) {
        if($link->rel == 'self') {
          $actor_url = $link->href;
          break;
        }
      }
    }

    if(!isset($actor_url)){
      return "Couldn't find an actor with that URL/webfinger";
    }

    $get_opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Accept: application/json\r\n"
      )
    );
    $actor = json_decode(file_get_contents($actor_url, false, stream_context_create($get_opts)), true);

    if(!isset($webfinger)) {
      $webfinger = $actor['preferredUsername'] . '@' . parse_url($actor_url, PHP_URL_HOST);
    }

    $inbox = $actor['inbox'];

    if(!isset($inbox)) {
      return;
    }

    $follow = new \Art\models\Following();
    $follow->url = $actor['id'];
    $follow->inbox = $inbox;
    $follow->user_id = $this->user->id;
    $follow->username = $webfinger;
    $follow->accepted = 0;
    $follow->save();

    try {
      $this->user->send($this->user->activity("Follow", $actor_url), $inbox, $actor_url);
    } catch(Exception $e) {
      return "An error occurred trying to follow $webfinger; ". $e->getMessage();
    }

    return "A follow request has been sent to $webfinger";
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
      .Follow {
        padding-top: 50px;
        display: flex;
        flex-direction: column;
        width: 50%;
        margin: auto;
      }

      .Follow__field {
        display: block;
        width: 100%;
        padding: 10px;
      }

      .Follow__fieldset {
        margin: 10px;
      }
      </style>
      <form class="Follow" method="POST">
        <h1>Follow</h1>
        <label class="Follow__fieldset">
          Follow webfinger:
          <input name="follow" multiple class="Follow__field">
        </label>
        <button name="submit" class="Follow__fieldset">Follow</button>
      </form>
HTML;
    return $response->write(page($this->user, [$output]));
  }
}

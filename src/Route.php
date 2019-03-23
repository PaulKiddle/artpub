<?php
namespace Art;

class Input {

}

class Route {
  protected $container;
  protected $session;
  protected $user;
  protected $errors = [];

  function __construct($container){
    $this->container = $container;
    $this->session = $container['session'];
    $this->user = $this->session->exists('user') ? $this->session['user'] : null;
  }

  function __invoke($request, $response, $args){
    if(isset($_POST['submit'])){
      $r = $this->submit($request, $response, $args);
      if($r) {
        return $r;
      }
    }

    return $this->view($request, $response, $args);
  }
}

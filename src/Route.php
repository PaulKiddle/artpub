<?php
namespace Art;

class Input {

}

class Route {
  protected $container;
  protected $session;
  protected $user;
  protected $db;
  protected $errors = [];

  function __construct($container){
    $this->container = $container;
    $this->session = $container['session'];
    $this->user = $this->session->exists('user') ? $this->session['user'] : null;
    $this->db = $container['db'];
  }

  function __invoke($request, $response, $args){
    if(isset($_POST['submit'])){
      $r = $this->submit($request, $response);
      if($r) {
        return $r;
      }
    }

    $this->view($request, $response);
  }
}

<?php
namespace Art\routes;

require(__DIR__.'/../components/page.php');

class Journal extends Route {
  function view ($req, $res, $args) {
    $journal = \Art\models\Journal::where('id', $args['id'])->first();
    $author = $journal->author()->first();

    $desc = htmlentities($journal->content);
    $output = <<<HTML
      <style>
      .Journal {
        display: flex;
        flex-direction: column;
      }

      .Journal__content {
        min-height: 250px;
        width: 80%;
        margin: auto;
      }
      </style>
      <div class="Journal">
        <div class="Journal__content">
          <h1>{$journal->title}</h1>
          <h3>by <a href="{$this->container->router->pathFor('gallery', ['id'=>$author->id]) }">{$author->username}</a></h3>
          <p>$journal->content</p>
        </div>
      </div>
HTML;

    return $res->write(page($this->user, [$output]));
  }
}

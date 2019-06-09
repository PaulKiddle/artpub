<?php

function note($note) {
  return <<<HTML
    <style>
      .Note {
        display:flex;
        border:1px solid black;
        width:50%;
        margin:auto;
      }

      .Note__message {
        flex-grow: 1;
      }

      .Note__avatar {
        max-width: 100px;
      }
    </style>
    <li class="Note">
      <div>
        {$note->actor->display_name}<br>
        <img class="Note__avatar" src="{$note->actor->avatar_url}" alt="">
      </div>
      <div class="Note__message">
        $note->message
      </div>
      <div>
        <a href='$note->url'>Link</a><br>
        <form method="post">
          <input type="hidden" name="submit" value="ok">
          <button name="delete" value="{$note->id}">x</button>
        </form>
      </div>
    </li>
    HTML;
/*
HTML;
//*/
}

<?php

function note($note) {
  return <<<END
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
        <a href='?delete={$note->id}'>x</a>
      </div>
    </li>
    END;
/*
END;
//*/
}

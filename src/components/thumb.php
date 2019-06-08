<?php

function thumb($sub, $author = null) {
  switch($sub['type']){
    case 'image':
      $thumb = "<img src=\"{$sub['thumb']}\" alt=''>";
      break;
    case 'audio':
      $thumb = "Audio file";
      break;
    default:
      $thumb = "???";
      break;
  }

  if($author) {
    $auth = "<a class=\"Thumb__author\" href={$author['url']}>{$author['name']}</a>";
  }

  return <<<END
    <style>
      .Thumb {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 10px;
        text-align: center;
      }

      .Thumb__image {
        width: 300px;
        height: 300px;
        overflow: hidden;
        display: grid;
        align-items: center;
        justify-content: center;
        background: #ccc;
      }

      .Thumb__image > * {
        max-width: 100%;
        min-height: 100%;
      }
    </style>
    <div class="Thumb">
      <a class="Thumb__title" href={$sub['url']}>
        <div class="Thumb__image">$thumb</div>
        {$sub['title']}
      </a>
      $auth
    </div>
    END;
/*
END;
//*/
}

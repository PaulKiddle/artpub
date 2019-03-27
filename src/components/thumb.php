<?php

function thumb($router, $submission) {
  $file = $submission->files()->first();
  $artist = $submission->artist()->first();

  switch($submission->type){
    case 'image':
      $thumb = "<img src=\"/uploads/$file->file\">";
      break;
    case 'audio':
      $thumb = "Audio file";
      break;
    default:
      $thumb = "???";
      break;
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
      <a class="Thumb__title" href={$router->pathFor('submission', ['id'=>$submission->id])}>
        <div class="Thumb__image">$thumb</div>
        $submission->title
      </a>
      <a class="Thumb__author" href={$router->pathFor('gallery', ['id'=>$artist->id]) }>{$artist->username}</a>
    </div>
    END;
/*
END;
//*/
}

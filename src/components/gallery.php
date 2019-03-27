<?php

function gallery($items) {
  $content = implode('', $items);

  return <<<END
    <style>
    .Gallery {
      display: flex;
      flex-wrap: wrap;
    }
    </style>
    <div class="Gallery">
      {$content}
    </div>
  END;
}

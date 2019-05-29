# Contributing

I want to make contributing to this project as easy as possible to developers of all levels.
If you're interested in helping, please send me a message and I'll be happy to help you get started. If you feel there's anything missing from the documentation, please file an issue or make a pull request.

## Designs

Ideally designs should be mocked up in [Pencil](https://pencil.evolus.vn/) and added to the source in this repo. There is currently no formal process for reviewing designs but please raise an issue or pull request with an image of any new designs when submitting them (or add to relevant existing issues/PRs).

## Structure/architecture

 - Routing and general site configuration is in `index.php`.
 - Route views are in `src`.
 - Views may use components in `src/components`.
 - Models are in `src/models`.

### Index

#### Activities:
 - Notes/inbox UI: src/Notes.php

#### Components:
 - src/components

#### User:
 - Registration/login: src/Index.php

#### Webfinger:
 - Following/resolving: src/Follow.php

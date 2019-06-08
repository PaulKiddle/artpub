CREATE TABLE IF NOT EXISTS remote_actor (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  inbox varchar(255) NOT NULL,
  shared_inbox varchar(255),
  username varchar(255) NOT NULL,
  display_name varchar(255) NOT NULL,
  summary varchar(255),
  avatar_url varchar(255),
  last_changed timestamp not null default now(),
  PRIMARY KEY (id),
  UNIQUE KEY (url)
);

-- FOLLOWING
INSERT INTO remote_actor (url, inbox, username, display_name)
  SELECT url, inbox, username, username as display_name FROM following
ON DUPLICATE KEY update remote_actor.url=remote_actor.url;

ALTER TABLE following
  ADD remote_actor_id INT;

UPDATE following
  JOIN remote_actor ON (following.url=remote_actor.url)
SET remote_actor_id=remote_actor.id;

ALTER TABLE following DROP index user_id_2;
ALTER TABLE following ADD INDEX (user_id, remote_actor_id);
ALTER TABLE following DROP url, DROP inbox, DROP username;

-- SUBSCRIBER
INSERT INTO remote_actor (url, inbox, username, display_name)
  SELECT url, inbox, url as username, url as display_name FROM subscriber
ON DUPLICATE KEY update remote_actor.url=remote_actor.url;

ALTER TABLE subscriber
  ADD remote_actor_id INT;

UPDATE subscriber
  JOIN remote_actor ON (subscriber.url=remote_actor.url)
SET remote_actor_id=remote_actor.id;

ALTER TABLE subscriber DROP index user_id;
ALTER TABLE subscriber ADD INDEX (user_id, remote_actor_id);
ALTER TABLE subscriber DROP url, DROP inbox;

-- INBOX
INSERT INTO remote_actor (url, inbox, username, display_name)
  SELECT actor_url as url, '' as inbox, actor_url as username, actor_url as display_name FROM inbox
ON DUPLICATE KEY update remote_actor.url=remote_actor.url;

ALTER TABLE inbox
  ADD remote_actor_id INT;

UPDATE inbox
  JOIN remote_actor ON (inbox.actor_url=remote_actor.url)
SET remote_actor_id=remote_actor.id;

ALTER TABLE inbox DROP actor_url;

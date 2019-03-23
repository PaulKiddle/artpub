CREATE TABLE IF NOT EXISTS subscriber (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  inbox varchar(255) NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (id, url)
);

DROP INDEX url ON subscriber;
ALTER TABLE subscriber ADD CONSTRAINT UNIQUE (id, url);

CREATE TABLE IF NOT EXISTS following (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  inbox varchar(255) NOT NULL,
  user_id INT NOT NULL,
  username varchar(255) NOT NULL,
  accepted INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (user_id, url)
);

CREATE TABLE IF NOT EXISTS inbox (
  id INT AUTO_INCREMENT,
  user_id INT NOT NULL,
  following_id INT NOT NULL,
  message varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (user_id, url)
);

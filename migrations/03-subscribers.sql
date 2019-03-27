CREATE TABLE IF NOT EXISTS subscriber (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  inbox varchar(255) NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (user_id, url)
);

CREATE TABLE IF NOT EXISTS subscriber (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL UNIQUE,
  inbox varchar(255) NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id)
);

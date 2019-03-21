CREATE TABLE IF NOT EXISTS subscriber (
  id INT AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  inbox varchar(255) NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id)
);

ALTER TABLE user
  ADD COLUMN private_key TEXT,
  ADD COLUMN public_key TEXT;

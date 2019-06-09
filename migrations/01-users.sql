CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255),
  private_key TEXT,
  public_key TEXT,
  PRIMARY KEY (id)
);

ALTER TABLE user
  ADD display_name varchar(255),
  ADD summary TEXT,
  ADD avatar_url varchar(255);

CREATE TABLE user (
  id INT AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255),
  private_key TEXT,
  public_key TEXT,
  PRIMARY KEY (id)
);

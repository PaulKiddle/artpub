CREATE TABLE IF NOT EXISTS submission (
  id INT AUTO_INCREMENT,
  author_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  type VARCHAR(255) NOT NULL DEFAULT 'image',
  description TEXT,
  PRIMARY KEY (id)
);

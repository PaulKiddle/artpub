CREATE TABLE IF NOT EXISTS journal (
  id INT AUTO_INCREMENT,
  author_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  created_at DATETIME,
  updated_at DATETIME,
  PRIMARY KEY (id)
);

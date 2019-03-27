CREATE TABLE IF NOT EXISTS file (
  id INT AUTO_INCREMENT,
  submission_id INT NOT NULL,
  file VARCHAR(255) NOT NULL,
  media_type VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO file(submission_id, file, media_type) (SELECT id, file, type FROM submission);
ALTER TABLE submission DROP COLUMN file;

CREATE TABLE IF NOT EXISTS users (
  id INT(10) NOT NULL AUTO_INCREMENT,
  username VARCHAR(32) NOT NULL,
  password VARCHAR(32) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  UNIQUE(username),
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS settings (
  id INT(10) NOT NULL AUTO_INCREMENT,
  category VARCHAR(255) DEFAULT 'system',
  `name` VARCHAR(255) NOT NULL,
  `value` TEXT,
  UNIQUE(category, `name`),
  PRIMARY KEY (id)
);

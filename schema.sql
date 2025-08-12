CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(50) NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, -- hashed
  profile_photo VARCHAR(255), -- optional path to profile image
  role ENUM('student', 'admin') NOT NULL DEFAULT 'student', -- user role
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  project_title VARCHAR(255) NOT NULL,
  project_description TEXT NOT NULL,
  project_category ENUM('Games', 'Websites', 'Mobile Apps', 'Console Apps', 'AI/ML', 'Databases') NOT NULL,
  download_link VARCHAR(255), -- optional (.exe)
  live_link VARCHAR(255),     -- optional live site
  github_link VARCHAR(255),   -- optional GitHub link
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE project_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE project_technologies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  technology_name VARCHAR(100) NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE project_team_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  member_name VARCHAR(100) NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE project_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  student_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (project_id, student_id), -- prevent multiple likes from same user
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE project_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  student_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
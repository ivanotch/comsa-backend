INSERT INTO `students` (`id`, `student_number`, `name`, `email`, `password`, `profile_photo`, `role`,`created_at`) VALUES
(1, '234-999923', 'Kileoma', 'untalan.j.bscs22@gmail.com', '123', NULL, 'student', '2023-04-28 16:15:22');








ALTER TABLE students 
ADD COLUMN nickname VARCHAR(50) 
CHARACTER SET utf8mb4 COLLATE utf8mb4_bin 
DEFAULT NULL 
COMMENT 'Letters only, no spaces/numbers/special chars';

ALTER TABLE students 
ADD COLUMN bio TEXT 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci 
DEFAULT NULL 
COMMENT 'Max 100 words, allows special chars/numbers';

---------------------------------------NEW TABLE TO ADD----------------------------------------

CREATE TABLE quick_links (
	id INT AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(100) NOT NULL,
	url VARCHAR(100) NOT NULL,
	category ENUM('academic', 'opportunity', 'support', 'resource') NOT NULL,
	remix_icon VARCHAR(100),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    status ENUM('active', 'upcoming', 'ended', 'draft') NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    event_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    carousel_status BOOLEAN NOT NULL
);

CREATE TABLE admin_post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    title VARCHAR(100) NOT NULL,
    post_image VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    post_status ENUM('published', 'draft', 'archived') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_post_admin
        FOREIGN KEY (admin_id) REFERENCES students(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);


CREATE TABLE admin_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    tag_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES admin_post(id) ON DELETE CASCADE
);

CREATE TABLE post_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  student_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (post_id, student_id), -- prevent multiple likes from same user
  FOREIGN KEY (post_id) REFERENCES admin_post(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE post_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  student_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES admin_post(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

---------------------------------------NEW TABLE TO ADD----------------------------------------
---------------------------------------NEW TABLE TO ALTER--------------------------------------
ALTER TABLE projects
ADD COLUMN visibility ENUM('public', 'hidden') NOT NULL DEFAULT 'public',
ADD COLUMN featured BOOLEAN NOT NULL DEFAULT FALSE;


-- For MySQL 8.0.16+ you can add constraints for the nickname
-- ALTER TABLE students 
-- ADD CONSTRAINT chk_nickname_letters_only 
-- CHECK (nickname REGEXP '^[A-Za-z]+$' OR nickname IS NULL);



---------------------------------------NEW TABLE TO ALTER--------------------------------------

-- Include Mayor, Vice-Mayor, Secretary in roles
ALTER TABLE students 
MODIFY role ENUM('student', 'admin', 'mayor', 'vice-mayor', 'secretary') 
NOT NULL DEFAULT 'student';

-- Include Year Level and Section in students table
ALTER TABLE students
ADD COLUMN year_level VARCHAR(50) NOT NULL,
ADD COLUMN section VARCHAR(50) NOT NULL;

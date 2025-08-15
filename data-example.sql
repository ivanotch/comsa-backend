INSERT INTO `students` (`id`, `student_number`, `name`, `email`, `password`, `profile_photo`, `role`,`created_at`) VALUES
(1, '234-99992', 'Kileoma', 'untalan.j.bscs22@gmail.com', '123', NULL, 'student', '2023-04-28 16:15:22');








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

-- For MySQL 8.0.16+ you can add constraints for the nickname
ALTER TABLE students 
ADD CONSTRAINT chk_nickname_letters_only 
CHECK (nickname REGEXP '^[A-Za-z]+$' OR nickname IS NULL);
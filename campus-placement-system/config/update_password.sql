-- Update developer password to hash for 'Vishal@178'
-- This script should be run after initial database setup

USE Placement_db;

-- Update the developer password with correct hash
-- Password: Vishal@178
UPDATE users 
SET password = '$2y$10$rGZYQOJ8txW4jvHMHrYQYuCd6hBrH0zF8rQrJ5TqXqK9QgJ8VlzZu' 
WHERE email = 'vishalsharma08555252@gmail.com';

-- Verify the update
SELECT user_id, email, full_name, role_id 
FROM users 
WHERE email = 'vishalsharma08555252@gmail.com';
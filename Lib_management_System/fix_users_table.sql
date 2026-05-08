-- Add role column if it doesn't exist
SET @db_name = DATABASE();
SET @table_name = 'users';
SET @column_name = 'role';

SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @db_name 
     AND TABLE_NAME = @table_name 
     AND COLUMN_NAME = @column_name) > 0,
    'SELECT "Column already exists"',
    'ALTER TABLE users ADD COLUMN role ENUM("admin","user") DEFAULT "user" AFTER id'
) INTO @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add created_at column if it doesn't exist
SET @column_name = 'created_at';

SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @db_name 
     AND TABLE_NAME = @table_name 
     AND COLUMN_NAME = @column_name) > 0,
    'SELECT "Column already exists"',
    'ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
) INTO @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

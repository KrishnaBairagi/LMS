-- Create book_requests table for request/approval workflow
CREATE TABLE IF NOT EXISTS book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_date DATETIME NULL,
    approval_notes TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

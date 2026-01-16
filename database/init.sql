-- TalkMetrics Database Schema

CREATE TABLE IF NOT EXISTS calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caller_number VARCHAR(20) NOT NULL,
    destination_number VARCHAR(20) NOT NULL,
    duration INT NOT NULL DEFAULT 0,
    status ENUM('completed', 'failed', 'no_answer', 'busy') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
);

-- Sample data
INSERT INTO calls (caller_number, destination_number, duration, status, created_at) VALUES
('+1-555-0101', '+1-555-0201', 245, 'completed', NOW() - INTERVAL 5 MINUTE),
('+1-555-0102', '+1-555-0202', 0, 'no_answer', NOW() - INTERVAL 12 MINUTE),
('+1-555-0103', '+1-555-0203', 189, 'completed', NOW() - INTERVAL 18 MINUTE),
('+1-555-0104', '+1-555-0204', 0, 'failed', NOW() - INTERVAL 25 MINUTE),
('+1-555-0105', '+1-555-0205', 432, 'completed', NOW() - INTERVAL 32 MINUTE),
('+1-555-0106', '+1-555-0206', 0, 'busy', NOW() - INTERVAL 45 MINUTE),
('+1-555-0107', '+1-555-0207', 67, 'completed', NOW() - INTERVAL 52 MINUTE),
('+1-555-0108', '+1-555-0208', 298, 'completed', NOW() - INTERVAL 1 HOUR),
('+1-555-0109', '+1-555-0209', 0, 'no_answer', NOW() - INTERVAL 75 MINUTE),
('+1-555-0110', '+1-555-0210', 156, 'completed', NOW() - INTERVAL 90 MINUTE),
('+1-555-0111', '+1-555-0211', 0, 'failed', NOW() - INTERVAL 2 HOUR),
('+1-555-0112', '+1-555-0212', 523, 'completed', NOW() - INTERVAL 140 MINUTE),
('+1-555-0113', '+1-555-0213', 0, 'busy', NOW() - INTERVAL 165 MINUTE),
('+1-555-0114', '+1-555-0214', 178, 'completed', NOW() - INTERVAL 3 HOUR),
('+1-555-0115', '+1-555-0215', 392, 'completed', NOW() - INTERVAL 210 MINUTE),
('+1-555-0116', '+1-555-0216', 0, 'no_answer', NOW() - INTERVAL 4 HOUR),
('+1-555-0117', '+1-555-0217', 234, 'completed', NOW() - INTERVAL 255 MINUTE),
('+1-555-0118', '+1-555-0218', 0, 'failed', NOW() - INTERVAL 5 HOUR),
('+1-555-0119', '+1-555-0219', 445, 'completed', NOW() - INTERVAL 330 MINUTE),
('+1-555-0120', '+1-555-0220', 167, 'completed', NOW() - INTERVAL 6 HOUR),
('+1-555-0121', '+1-555-0221', 0, 'busy', NOW() - INTERVAL 405 MINUTE),
('+1-555-0122', '+1-555-0222', 289, 'completed', NOW() - INTERVAL 7 HOUR),
('+1-555-0123', '+1-555-0223', 0, 'no_answer', NOW() - INTERVAL 8 HOUR),
('+1-555-0124', '+1-555-0224', 356, 'completed', NOW() - INTERVAL 510 MINUTE),
('+1-555-0125', '+1-555-0225', 198, 'completed', NOW() - INTERVAL 9 HOUR);
USE isp_billing;

-- Only insert admin user, no other data
INSERT INTO users (name, email, password_hash, role, is_active)
VALUES (
    'Admin User',
    'admin@isp.local',
    '$2y$12$3MTXTXCas/l9yQLeKRGY1.OBwRAIF6CB8cHPmD1c0a/w2RAo0122S',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE email = email;

-- Initialize default cost types
INSERT INTO cost_types (name, description, is_active) VALUES
('ISP Bill Payment', 'Internet Service Provider bill payment', 1),
('Salary', 'Employee salary and wages', 1),
('Electricity Bill', 'Electricity utility bill', 1),
('Other', 'Other miscellaneous costs', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

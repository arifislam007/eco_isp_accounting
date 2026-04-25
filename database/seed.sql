USE isp_billing;

INSERT INTO users (name, email, password_hash, role, is_active)
VALUES (
    'Admin User',
    'admin@isp.local',
    '$2y$12$3MTXTXCas/l9yQLeKRGY1.OBwRAIF6CB8cHPmD1c0a/w2RAo0122S',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO businesses (name) VALUES
('Alpha Net'),
('Beta Link'),
('City Fiber')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO commissions (business_id, percentage) VALUES
(1, 5.00),
(2, 4.00),
(3, 3.50)
ON DUPLICATE KEY UPDATE percentage = VALUES(percentage);

INSERT INTO bonuses (business_id, percentage) VALUES
(1, 2.00),
(2, 1.50),
(3, 1.00)
ON DUPLICATE KEY UPDATE percentage = VALUES(percentage);

INSERT INTO collections (business_id, total_users, total_collection, month) VALUES
(1, 120, 48000.00, '2026-04'),
(2, 85, 32250.00, '2026-04'),
(3, 140, 56000.00, '2026-04'),
(1, 118, 47200.00, '2026-03'),
(2, 82, 31600.00, '2026-03'),
(3, 136, 54400.00, '2026-03')
ON DUPLICATE KEY UPDATE total_users = VALUES(total_users), total_collection = VALUES(total_collection);

INSERT INTO deposits (business_id, amount, date, type, medium, reference) VALUES
(1, 15000.00, '2026-04-10', 'deposit', 'bank', 'BK-4401'),
(1, 12000.00, '2026-04-18', 'deposit', 'bkash', 'BK-4402'),
(2, 10000.00, '2026-04-12', 'deposit', 'cash', 'CS-2201'),
(2, 9000.00, '2026-04-22', 'deposit', 'bank', 'BK-2202'),
(3, 18000.00, '2026-04-14', 'deposit', 'bank', 'BK-3301'),
(3, 16000.00, '2026-04-24', 'deposit', 'cash', 'CS-3302')
ON DUPLICATE KEY UPDATE amount = VALUES(amount);

INSERT INTO discounts (business_id, amount, month, note) VALUES
(1, 500.00, '2026-04', 'Late payment adjustment'),
(2, 300.00, '2026-04', 'Customer retention discount'),
(3, 800.00, '2026-04', 'Special promotion')
ON DUPLICATE KEY UPDATE amount = VALUES(amount);

INSERT INTO costs (type, amount, month) VALUES
('ISP Bill', 42000.00, '2026-04'),
('Software Cost', 6000.00, '2026-04'),
('Others', 2500.00, '2026-04'),
('ISP Bill', 41000.00, '2026-03'),
('Software Cost', 6000.00, '2026-03'),
('Others', 2400.00, '2026-03');

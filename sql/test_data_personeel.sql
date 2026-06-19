-- =============================================
-- TEST DATA: Employees (users) & Work Shifts
-- Current date: June 17, 2026 (Week 25)
-- All employees have password: "password"
-- =============================================

USE supercharged_db;

-- Password hash for "password"
-- Already inserted: admin (beheerder, can't be deleted)

INSERT INTO users (username, password_hash, display_name, email) VALUES
('jan.verhoeven',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jan Verhoeven',  'jan.verhoeven@supercharged.nl'),
('marie.jansen',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie Jansen',   'marie.jansen@supercharged.nl'),
('peter.vdberg',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter van den Berg', 'peter.vdberg@supercharged.nl'),
('anna.dewit',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anna de Wit',    'anna.dewit@supercharged.nl'),
('mark.bakker',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark Bakker',    'mark.bakker@supercharged.nl'),
('lisa.visser',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Visser',    'lisa.visser@supercharged.nl'),
('thomas.hendriks', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thomas Hendriks', 'thomas.hendriks@supercharged.nl'),
('emma.dijkstra',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Dijkstra',  'emma.dijkstra@supercharged.nl'),
('david.meijer',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Meijer',   'david.meijer@supercharged.nl'),
('sophie.timmer',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie Timmer',  'sophie.timmer@supercharged.nl'),
('lars.hermans',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lars Hermans',   'lars.hermans@supercharged.nl'),
('nina.koster',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nina Koster',    'nina.koster@supercharged.nl');

-- ============== WEEK 25 (June 15 - 21, 2026) ==============

-- Monday June 15
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(2,  '2026-06-15', '08:00', '12:00', 'Ochtend magazijn'),
(3,  '2026-06-15', '08:00', '16:00', 'Vloermanagement'),
(5,  '2026-06-15', '12:00', '20:00', 'Kassa'),
(7,  '2026-06-15', '08:00', '14:00', 'Versafdeling'),
(9,  '2026-06-15', '14:00', '20:00', 'Magazijn'),
(11, '2026-06-15', '10:00', '18:00', 'Klantenservice');

-- Tuesday June 16
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(4,  '2026-06-16', '08:00', '16:00', 'Kassa'),
(6,  '2026-06-16', '08:00', '14:00', 'Versafdeling'),
(8,  '2026-06-16', '12:00', '20:00', 'Vulploeg'),
(10, '2026-06-16', '08:00', '12:00', 'Schoonmaak'),
(12, '2026-06-16', '14:00', '20:00', 'Kassa'),
(2,  '2026-06-16', '13:00', '17:00', 'Overleg teamlead');

-- Wednesday June 17 (today)
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(3,  '2026-06-17', '08:00', '16:00', 'Vloermanagement'),
(5,  '2026-06-17', '10:00', '18:00', 'Kassa'),
(7,  '2026-06-17', '08:00', '14:00', 'Versafdeling'),
(9,  '2026-06-17', '14:00', '20:00', 'Magazijn'),
(11, '2026-06-17', '08:00', '12:00', 'Klantenservice'),
(12, '2026-06-17', '12:00', '20:00', 'Vulploeg');

-- Thursday June 18
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(2,  '2026-06-18', '08:00', '14:00', 'Magazijn'),
(4,  '2026-06-18', '12:00', '20:00', 'Kassa'),
(6,  '2026-06-18', '08:00', '16:00', 'Versafdeling'),
(8,  '2026-06-18', '08:00', '12:00', 'Vulploeg'),
(10, '2026-06-18', '14:00', '20:00', 'Kassa'),
(11, '2026-06-18', '10:00', '18:00', 'Klantenservice');

-- Friday June 19
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(3,  '2026-06-19', '12:00', '20:00', 'Vloermanagement'),
(5,  '2026-06-19', '08:00', '14:00', 'Kassa'),
(7,  '2026-06-19', '08:00', '16:00', 'Versafdeling'),
(9,  '2026-06-19', '08:00', '12:00', 'Magazijn'),
(12, '2026-06-19', '14:00', '20:00', 'Vulploeg'),
(2,  '2026-06-19', '10:00', '14:00', 'Inwerken nieuwe medewerker');

-- Saturday June 20
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(4,  '2026-06-20', '10:00', '18:00', 'Kassa'),
(6,  '2026-06-20', '08:00', '14:00', 'Versafdeling'),
(8,  '2026-06-20', '10:00', '18:00', 'Vulploeg'),
(10, '2026-06-20', '08:00', '16:00', 'Kassa'),
(11, '2026-06-20', '12:00', '20:00', 'Klantenservice'),
(3,  '2026-06-20', '08:00', '12:00', 'Inkoop administratie');

-- Sunday June 21
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-21', '10:00', '16:00', 'Kassa (zondag)'),
(7,  '2026-06-21', '10:00', '16:00', 'Versafdeling (zondag)'),
(9,  '2026-06-21', '12:00', '17:00', 'Magazijn (zondag)');

-- ============== WEEK 24 (June 8 - 14, 2026) ==============

-- Monday June 8
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(2,  '2026-06-08', '08:00', '16:00', 'Magazijn'),
(4,  '2026-06-08', '08:00', '14:00', 'Kassa'),
(6,  '2026-06-08', '12:00', '20:00', 'Versafdeling'),
(8,  '2026-06-08', '14:00', '20:00', 'Vulploeg'),
(10, '2026-06-08', '08:00', '12:00', 'Schoonmaak');

-- Tuesday June 9
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(3,  '2026-06-09', '08:00', '16:00', 'Vloermanagement'),
(5,  '2026-06-09', '10:00', '18:00', 'Kassa'),
(7,  '2026-06-09', '08:00', '14:00', 'Versafdeling'),
(9,  '2026-06-09', '14:00', '20:00', 'Magazijn'),
(11, '2026-06-09', '08:00', '16:00', 'Klantenservice');

-- Wednesday June 10
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(2,  '2026-06-10', '12:00', '20:00', 'Magazijn'),
(4,  '2026-06-10', '08:00', '16:00', 'Kassa'),
(6,  '2026-06-10', '08:00', '14:00', 'Versafdeling'),
(8,  '2026-06-10', '08:00', '14:00', 'Vulploeg'),
(12, '2026-06-10', '10:00', '18:00', 'Kassa');

-- Thursday June 11
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(3,  '2026-06-11', '08:00', '14:00', 'Vloermanagement'),
(5,  '2026-06-11', '12:00', '20:00', 'Kassa'),
(7,  '2026-06-11', '08:00', '16:00', 'Versafdeling'),
(9,  '2026-06-11', '08:00', '12:00', 'Magazijn'),
(11, '2026-06-11', '14:00', '20:00', 'Klantenservice');

-- Friday June 12
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(2,  '2026-06-12', '08:00', '14:00', 'Magazijn'),
(4,  '2026-06-12', '14:00', '20:00', 'Kassa'),
(6,  '2026-06-12', '08:00', '16:00', 'Versafdeling'),
(10, '2026-06-12', '08:00', '14:00', 'Kassa'),
(12, '2026-06-12', '10:00', '18:00', 'Vulploeg');

-- Saturday June 13
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(3,  '2026-06-13', '10:00', '18:00', 'Vloermanagement'),
(5,  '2026-06-13', '08:00', '14:00', 'Kassa'),
(8,  '2026-06-13', '10:00', '18:00', 'Vulploeg'),
(9,  '2026-06-13', '08:00', '16:00', 'Magazijn'),
(11, '2026-06-13', '08:00', '16:00', 'Klantenservice');

-- Sunday June 14
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-14', '10:00', '16:00', 'Versafdeling (zondag)'),
(10, '2026-06-14', '10:00', '16:00', 'Kassa (zondag)');

-- Work shifts - correct user IDs
-- Jan=5, Marie=6, Peter=7, Anna=8, Mark=9, Lisa=10
-- Thomas=11, Emma=12, David=13, Sophie=14, Lars=15, Nina=16

USE supercharged_db;

DELETE FROM work_shifts;

-- Week 25 (June 15-21, 2026)
-- Monday June 15
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-15', '08:00', '12:00', 'Ochtend magazijn'),
(7,  '2026-06-15', '08:00', '16:00', 'Vloermanagement'),
(9,  '2026-06-15', '12:00', '20:00', 'Kassa'),
(11, '2026-06-15', '08:00', '14:00', 'Versafdeling'),
(13, '2026-06-15', '14:00', '20:00', 'Magazijn'),
(15, '2026-06-15', '10:00', '18:00', 'Klantenservice');

-- Tuesday June 16
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(8,  '2026-06-16', '08:00', '16:00', 'Kassa'),
(10, '2026-06-16', '08:00', '14:00', 'Versafdeling'),
(12, '2026-06-16', '12:00', '20:00', 'Vulploeg'),
(14, '2026-06-16', '08:00', '12:00', 'Schoonmaak'),
(16, '2026-06-16', '14:00', '20:00', 'Kassa'),
(5,  '2026-06-16', '13:00', '17:00', 'Overleg teamlead');

-- Wednesday June 17 (today)
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-17', '08:00', '16:00', 'Vloermanagement'),
(9,  '2026-06-17', '10:00', '18:00', 'Kassa'),
(11, '2026-06-17', '08:00', '14:00', 'Versafdeling'),
(13, '2026-06-17', '14:00', '20:00', 'Magazijn'),
(15, '2026-06-17', '08:00', '12:00', 'Klantenservice'),
(16, '2026-06-17', '12:00', '20:00', 'Vulploeg');

-- Thursday June 18
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-18', '08:00', '14:00', 'Magazijn'),
(8,  '2026-06-18', '12:00', '20:00', 'Kassa'),
(10, '2026-06-18', '08:00', '16:00', 'Versafdeling'),
(12, '2026-06-18', '08:00', '12:00', 'Vulploeg'),
(14, '2026-06-18', '14:00', '20:00', 'Kassa'),
(15, '2026-06-18', '10:00', '18:00', 'Klantenservice');

-- Friday June 19
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-19', '12:00', '20:00', 'Vloermanagement'),
(9,  '2026-06-19', '08:00', '14:00', 'Kassa'),
(11, '2026-06-19', '08:00', '16:00', 'Versafdeling'),
(13, '2026-06-19', '08:00', '12:00', 'Magazijn'),
(16, '2026-06-19', '14:00', '20:00', 'Vulploeg'),
(5,  '2026-06-19', '10:00', '14:00', 'Inwerken nieuwe medewerker');

-- Saturday June 20
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(8,  '2026-06-20', '10:00', '18:00', 'Kassa'),
(10, '2026-06-20', '08:00', '14:00', 'Versafdeling'),
(12, '2026-06-20', '10:00', '18:00', 'Vulploeg'),
(14, '2026-06-20', '08:00', '16:00', 'Kassa'),
(15, '2026-06-20', '12:00', '20:00', 'Klantenservice'),
(7,  '2026-06-20', '08:00', '12:00', 'Inkoop administratie');

-- Sunday June 21
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(9,  '2026-06-21', '10:00', '16:00', 'Kassa (zondag)'),
(11, '2026-06-21', '10:00', '16:00', 'Versafdeling (zondag)'),
(13, '2026-06-21', '12:00', '17:00', 'Magazijn (zondag)');

-- Week 24 (June 8-14, 2026)
-- Monday June 8
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-08', '08:00', '16:00', 'Magazijn'),
(8,  '2026-06-08', '08:00', '14:00', 'Kassa'),
(10, '2026-06-08', '12:00', '20:00', 'Versafdeling'),
(12, '2026-06-08', '14:00', '20:00', 'Vulploeg'),
(14, '2026-06-08', '08:00', '12:00', 'Schoonmaak');

-- Tuesday June 9
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-09', '08:00', '16:00', 'Vloermanagement'),
(9,  '2026-06-09', '10:00', '18:00', 'Kassa'),
(11, '2026-06-09', '08:00', '14:00', 'Versafdeling'),
(13, '2026-06-09', '14:00', '20:00', 'Magazijn'),
(15, '2026-06-09', '08:00', '16:00', 'Klantenservice');

-- Wednesday June 10
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-10', '12:00', '20:00', 'Magazijn'),
(8,  '2026-06-10', '08:00', '16:00', 'Kassa'),
(10, '2026-06-10', '08:00', '14:00', 'Versafdeling'),
(12, '2026-06-10', '08:00', '14:00', 'Vulploeg'),
(16, '2026-06-10', '10:00', '18:00', 'Kassa');

-- Thursday June 11
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-11', '08:00', '14:00', 'Vloermanagement'),
(9,  '2026-06-11', '12:00', '20:00', 'Kassa'),
(11, '2026-06-11', '08:00', '16:00', 'Versafdeling'),
(13, '2026-06-11', '08:00', '12:00', 'Magazijn'),
(15, '2026-06-11', '14:00', '20:00', 'Klantenservice');

-- Friday June 12
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(5,  '2026-06-12', '08:00', '14:00', 'Magazijn'),
(8,  '2026-06-12', '14:00', '20:00', 'Kassa'),
(10, '2026-06-12', '08:00', '16:00', 'Versafdeling'),
(14, '2026-06-12', '08:00', '14:00', 'Kassa'),
(16, '2026-06-12', '10:00', '18:00', 'Vulploeg');

-- Saturday June 13
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(7,  '2026-06-13', '10:00', '18:00', 'Vloermanagement'),
(9,  '2026-06-13', '08:00', '14:00', 'Kassa'),
(12, '2026-06-13', '10:00', '18:00', 'Vulploeg'),
(13, '2026-06-13', '08:00', '16:00', 'Magazijn'),
(15, '2026-06-13', '08:00', '16:00', 'Klantenservice');

-- Sunday June 14
INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES
(11, '2026-06-14', '10:00', '16:00', 'Versafdeling (zondag)'),
(14, '2026-06-14', '10:00', '16:00', 'Kassa (zondag)');

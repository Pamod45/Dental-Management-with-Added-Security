CREATE USER 'patient_user'@'localhost' IDENTIFIED BY 'dkjk@dfdjfdjfkHDHD56';
-- Allow the user to view, insert, and delete records in the `appointment` table
GRANT SELECT, INSERT, DELETE ON pdms.appointment TO 'patient_user'@'localhost';
-- Allow the user to select (view) records in the `patient` table
GRANT SELECT, UPDATE ON pdms.patient TO 'patient_user'@'localhost';
-- Allow the user to select (view) records in the `doctor` table
GRANT SELECT ON pdms.doctor TO 'patient_user'@'localhost';
-- Allow the user to select (view) records in the `paymentmethod` table
GRANT SELECT ON pdms.paymentmethod TO 'patient_user'@'localhost';
-- Allow the user to select (view) records in the `schedule` table
GRANT SELECT ON pdms.schedule TO 'patient_user'@'localhost';
-- Allow the user to select (view) records in the `medicalrecord` table
GRANT SELECT ON pdms.medicalrecord TO 'patient_user'@'localhost';
-- Allow the user to select (view) and update records in the `user` table for password updates
GRANT SELECT, UPDATE ON pdms.user TO 'patient_user'@'localhost';

CREATE USER 'guest_user'@'localhost' IDENTIFIED BY 'jdkkdjkfj5454SIER,';
-- Grant SELECT permission on user table to check credentials
GRANT SELECT ON pdms.user TO 'guest_user'@'localhost';

-- Grant INSERT permission on patient and user tables for sign-up
GRANT INSERT,SELECT ON pdms.patient TO 'guest_user'@'localhost';
GRANT SELECT ON pdms.doctor TO 'guest_user'@'localhost';
GRANT SELECT ON pdms.employee TO 'guest_user'@'localhost';
GRANT SELECT ON pdms.employee_type TO 'guest_user'@'localhost'; 
GRANT INSERT ON pdms.user TO 'guest_user'@'localhost';
-- Refresh privileges to ensure the changes take effect
FLUSH PRIVILEGES;
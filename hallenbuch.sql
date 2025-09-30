CREATE TABLE hallenbuch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datum DATE NOT NULL,
    von TIME NOT NULL,
    bis TIME NOT NULL,
    gruppe VARCHAR(50) NOT NULL,
    leiter VARCHAR(100) NOT NULL,
    vermerk TEXT
);
ALTER TABLE hb_hallenbuch ADD COLUMN bemerkung TEXT;

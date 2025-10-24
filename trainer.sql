CREATE TABLE hb_trainer (
    trname VARCHAR(100) NOT NULL,
    gruppe_id INT NOT NULL,
    UNIQUE (trname, gruppe_id),
    FOREIGN KEY (gruppe_id) REFERENCES hb_gruppen(id)
);
INSERT INTO hb_trainer (trname, gruppe_id) VALUES ('Selina', 11);


TRUNCATE TABLE tabellenname;


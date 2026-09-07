BEGIN;

CREATE TABLE muutokset (
    koodi VARCHAR(3) PRIMARY KEY,
    maarittely VARCHAR(100) NOT NULL
);

INSERT INTO muutokset (koodi, maarittely) VALUES
    ('11', 'Estetään ajoneuvot. Päästetään jalankulkijat.'),
    ('10', 'Ajoneuvoilla punainen. Portti kiinni. Jalankulkijoilla vihreä.'),
    ('00', 'Ajoneuvoilla vihreä. Portti auki. Jalankulkijoilla punainen.'),
    ('01', 'Päästetään ajoneuvot. Estetään jalankulkijat.');

COMMIT;
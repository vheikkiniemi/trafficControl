BEGIN;

CREATE TABLE infot (
    koodi VARCHAR(3) PRIMARY KEY,
    maarittely VARCHAR(50) NOT NULL
);

INSERT INTO infot (koodi, maarittely) VALUES
    ('61', 'Jalankulkijoiden punainen päälle'),
    ('50', 'Jalankulkijoiden vihreä pois'),
    ('11', 'Portti auki'),
    ('10', 'Portti kiinni'),
    ('31', 'Ajoneuvojen keltainen päälle'),
    ('30', 'Ajoneuvojen keltainen pois'),
    ('40', 'Ajoneuvojen punainen pois'),
    ('41', 'Ajoneuvojen punainen päälle'),
    ('60', 'Jalankulkijoiden punainen pois'),
    ('51', 'Jalankulkijoiden vihreä päälle'),
    ('20', 'Ajoneuvojen vihreä pois'),
    ('21', 'Ajoneuvojen vihreä päälle');

COMMIT;
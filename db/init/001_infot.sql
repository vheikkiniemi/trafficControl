BEGIN;

CREATE TABLE infot (
    koodi VARCHAR(3) PRIMARY KEY,
    maarittely VARCHAR(50) NOT NULL
);

INSERT INTO infot (koodi, maarittely) VALUES
    ('61', 'Jalankulkijoiden punainen p&auml;&auml;lle'),
    ('50', 'Jalankulkijoiden vihre&auml; pois'),
    ('11', 'Portti auki'),
    ('10', 'Portti kiinni'),
    ('31', 'Ajoneuvojen keltainen p&auml;&auml;lle'),
    ('30', 'Ajoneuvojen keltainen pois'),
    ('40', 'Ajoneuvojen punainen pois'),
    ('41', 'Ajoneuvojen punainen p&auml;&auml;lle'),
    ('60', 'Jalankulkijoiden punainen pois'),
    ('51', 'Jalankulkijoiden vihre&auml; p&auml;&auml;lle'),
    ('20', 'Ajoneuvojen vihre&auml; pois'),
    ('21', 'Ajoneuvojen vihre&auml; p&auml;&auml;lle');

COMMIT;
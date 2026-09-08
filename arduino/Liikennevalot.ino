#include <Servo.h>
Servo portti;

// defines pins numbers
const int PainikeIn = 2;
const int UaaniTrig = 3;
const int UaaniEcho = 5;
const int AaniOut = 6;
const int JaPunaisenOut = 7;
const int JaVihreanOut = 8;
const int ServoOut = 9;
const int PunaisenOut = 11;
const int KeltaisenOut = 12;
const int VihreanOut = 13;

// defines variables
long Nykyaika = 0;
long VihreaPaalle = 0;
int Etaisyys = 0;
int Etaisyys1 = 0;
int Etaisyys2 = 0;
bool Kiikku = true;
long Muutos = 0;
int MuutosPyynto = 0;
int PalautusPyynto = 0;
bool Tila = false; // Normaali false, muutos true
bool Prosessi = false; // Prosessi ei käynnisssä false, käynnissä true
long AanenAika = 0;
bool Aani = false;
int Taajuus = 1000;
bool SarjaIn = false;
bool Mute = true;
bool Syote = false;
char Sensor = 'X';

void setup() {
  // put your setup code here, to run once:
  pinMode(PunaisenOut, OUTPUT);
  pinMode(KeltaisenOut, OUTPUT);
  pinMode(VihreanOut, OUTPUT);
  pinMode(JaPunaisenOut, OUTPUT);
  pinMode(JaVihreanOut, OUTPUT);
  pinMode(PainikeIn, INPUT);
  pinMode(UaaniTrig, OUTPUT);
  pinMode(UaaniEcho, INPUT);
  pinMode(AaniOut, OUTPUT);

  // Start > Defaults
  digitalWrite(PunaisenOut, HIGH);
  digitalWrite(JaVihreanOut, HIGH);
  Muutos = millis();
  portti.attach(ServoOut);
  portti.write(0);
  if (!Mute) {
    tone(AaniOut, Taajuus);
  }
  AanenAika = Muutos;
  Serial.begin(9600);
}

void loop() {
  // Alustus
  //*************************************
  // Nollataan sensoritieto
  Sensor = 'X';

  // Luetaan sarjaportti ja arvo
  if (Serial.available()) {
    if (Serial.read()) {
      Sensor = 'S';
    }
    delay(20);
  }

  // Luetaan painikkeen tila
  if (Sensor != 'S' && digitalRead(PainikeIn)) {
    Sensor = 'P';
  }

  // Mitataan etaisyys > Jos sarjaportissa ei arvoa
  if (Sensor != 'S' && Sensor != 'P') {
    Etaisyyden_mittaus();
    if (Etaisyys < 100 && Etaisyys > 5) {
      Sensor = 'U';
    }
  }

  // Odotetaan ja nollataan aika
  delay(200);
  Nykyaika = millis();

  // Tarkistetaan syötteen tila
  if ( Sensor != 'X' ) {
    Syote = true;
  }
  else {
    Syote = false;
  }

  // Käynnistetään Prosessi muutostilaan
  if (Syote && !Tila && !Prosessi) {
    MuutosPyynto = 1;
  }

  // Jos muutos tilaan pitää päästä kesken palautusprosessin
  if (Syote && Prosessi && PalautusPyynto ) {
    MuutosPyynto = PalautusPyynto;
    PalautusPyynto = 0;
    Tila = false;
    Serial.println("C:10");
    delay(100);
  }

  // Liikennevalot punaiselta vihrealle + Jalankulkijat vihrealta punaiselle
  if ((MuutosPyynto == 1) && !Prosessi) {
    // Prosessi käynnissä lippu
    Prosessi = true;
    // Prosessin ensimmäinen vaihe
    if (Muutosprosessi_Vaihe1()) {
      MuutosPyynto = 2;
    }
    // Nollataan aika
    Muutos = millis();
  }
  // Portti ylos sekunti havaitsemisesta
  if (Nykyaika > Muutos + 1000 && (MuutosPyynto == 2) && Prosessi) {
    if ( Muutosprosessi_Vaihe2() ) {
      MuutosPyynto = 3;
    }
  }
  // Keltainen paalle kaksi sekuntia havaitsemisesta
  if (Nykyaika > Muutos + 2000 && (MuutosPyynto == 3) && Prosessi) {
    //if (!digitalRead(VihreanOut) && !digitalRead(KeltaisenOut)) {
    //Muutosprosessi_Vaihe3();
    //}
    if ( Muutosprosessi_Vaihe3() ) {
      MuutosPyynto = 4;
    }
  }
  // Vihrea paalle, muut pois 3,5 sekuntia havaitsemisesta
  if (Nykyaika > Muutos + 3500 && (MuutosPyynto == 4) && Prosessi) {
    Muutosprosessi_Vaihe4();
    MuutosPyynto = 0;
    Tila = true;
    Prosessi = false;
    Muutos = millis();
  }

  // Prosessi normaalitilaan
  // Vihrea paalla 4 sekuntia, jos ei havaita muutosta
  // Jos muutos, nollataan aika
  if ( Tila && Syote) {
    Muutos = millis();
  }

  // Käynnistetäänkö palautusprosessi
  if ( Nykyaika > Muutos + 4000 && Tila && !Syote) {
    PalautusPyynto = 1;
  }

  //Vihrea pois, keltainen paalle
  if ((PalautusPyynto == 1) && !Prosessi) {
    // Prosessi käynnissä lippu
    Prosessi = true;
    // Prosessin ensimmäinen vaihe
    if (Palautusprosessi_Vaihe1()) {
      PalautusPyynto = 2;
    }
    // Nollataan aika
    Muutos = millis();
  }
  // Keltainen paalla 3 sekuntia > Punainen paalle > Portti kiinni > Jalankulkijoiden valot vaihtuu
  if (Nykyaika > Muutos + 3000 && (PalautusPyynto == 2) && Prosessi) {
    Palautusprosessi_Vaihe2();
    PalautusPyynto = 0;
    Tila = false;
    Prosessi = false;
  }
  // Jalankulkijoiden merkkiaani > Valo punainen, hidas > Valo vihrea, nopea
  AanenOhjaus();
}

int Etaisyyden_mittaus() {
  digitalWrite(UaaniTrig, LOW);
  delayMicroseconds(2);
  digitalWrite(UaaniTrig, HIGH);
  delayMicroseconds(10);
  digitalWrite(UaaniTrig, LOW);
  if (Kiikku) {
    Etaisyys = pulseIn(UaaniEcho, HIGH, 7000) * 0.034 / 2;
    Kiikku = false;
  }
  else {
    Etaisyys1 = pulseIn(UaaniEcho, HIGH, 7000) * 0.034 / 2;
    Kiikku = true;
  }
  if ( abs(Etaisyys - Etaisyys1) > 5 ) {
    Etaisyys = 1;
  }
  else {
    Etaisyys = (Etaisyys + Etaisyys1) / 2;
  }
  return Etaisyys;
}

boolean Muutosprosessi_Vaihe1() {
  Serial.print("C:01:");
  Serial.println(Sensor);
  delay(100);
  if (!digitalRead(JaPunaisenOut)) {
    digitalWrite(JaPunaisenOut, HIGH);
    Serial.println("I:61");
    delay(100);
  }
  if (digitalRead(JaVihreanOut)) {
    digitalWrite(JaVihreanOut, LOW);
    Serial.println("I:50");
    delay(100);
  }
  return true;
}

boolean Muutosprosessi_Vaihe2() {
  int aste = portti.read();
  while (portti.read() < 90) {
    portti.write(aste++);
    if (aste == 90) {
      Serial.println("I:11");
    }
    delay(3);
  }
  delay(100);
  return true;
}

boolean Muutosprosessi_Vaihe3() {
  if (!digitalRead(KeltaisenOut)) {
    digitalWrite(KeltaisenOut, HIGH);
    Serial.println("I:31");
    delay(100);
  }
  return true;
}

boolean Muutosprosessi_Vaihe4() {
  if (digitalRead(PunaisenOut)) {
    digitalWrite(PunaisenOut, LOW);
    Serial.println("I:40");
    delay(100);
  }
  if (digitalRead(KeltaisenOut)) {
    digitalWrite(KeltaisenOut, LOW);
    Serial.println("I:30");
    delay(100);
  }
  if (!digitalRead(VihreanOut)) {
    digitalWrite(VihreanOut, HIGH);
    Serial.println("I:21");
    delay(100);
  }
  Serial.println("C:00");
  return true;
}

boolean Palautusprosessi_Vaihe1() {
  Serial.println("C:11");
  delay(100);
  if (digitalRead(VihreanOut)) {
    digitalWrite(VihreanOut, LOW);
    Serial.println("I:20");
    delay(100);
  }
  if (!digitalRead(KeltaisenOut)) {
    digitalWrite(KeltaisenOut, HIGH);
    Serial.println("I:31");
    delay(100);
  }
  return true;
}

boolean Palautusprosessi_Vaihe2() {
  if (digitalRead(KeltaisenOut)) {
    digitalWrite(KeltaisenOut, LOW);
    Serial.println("I:30");
    delay(100);
  }

  if (!digitalRead(PunaisenOut)) {
    digitalWrite(PunaisenOut, HIGH);
    Serial.println("I:41");
    delay(100);
  }
  // Portin sulkeminen
  int aste = portti.read();
  while (portti.read() > 0) {
    portti.write(aste--);
    delay(3);
  }
  Serial.println("I:10");
  delay(200);
  if (digitalRead(JaPunaisenOut)) {
    digitalWrite(JaPunaisenOut, LOW);
    Serial.println("I:60");
    delay(100);
  }

  if (!digitalRead(JaVihreanOut)) {
    digitalWrite(JaVihreanOut, HIGH);
    Serial.println("I:51");
    delay(100);
  }
  Serial.println("C:10");
  return true;
}

boolean AanenOhjaus() {
  if (Aani && !Mute) {
    if (digitalRead(JaPunaisenOut)) {
      if (Nykyaika > AanenAika + 400) {
        noTone(AaniOut);
        Aani = false;
        AanenAika = Nykyaika;
      }
    }
    else {
      if (Nykyaika > AanenAika + 200) {
        noTone(AaniOut);
        Aani = false;
        AanenAika = Nykyaika;
      }
    }
  }
  else if (!Mute) {
    if (digitalRead(JaPunaisenOut)) {
      if (Nykyaika > AanenAika + 1600) {
        tone(AaniOut, Taajuus);
        Aani = true;
        AanenAika = Nykyaika;
      }
    }
    else {
      if (Nykyaika > AanenAika + 200) {
        tone(AaniOut, Taajuus);
        Aani = true;
        AanenAika = Nykyaika;
      }
    }
  }
  return true;
}

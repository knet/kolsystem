CREATE TABLE grupper (
  gruppenavn VARCHAR(64) NOT NULL,
  beskrivelse VARCHAR(256) NULL,
  mail_liste_navn VARCHAR(64) NULL,
  mail_skriverettigheder ENUM('alle', 'alle_medlemmer', 'udvalgte_medlemmer') NOT NULL DEFAULT 'alle',
  mail_obligatorisk BOOL NOT NULL DEFAULT FALSE,
  mail_footer TEXT NULL,
  PRIMARY KEY(gruppenavn),
  UNIQUE INDEX grupper_mailliste_navn(mail_liste_navn)
)
TYPE=InnoDB;

CREATE TABLE switche (
  switch_ip VARCHAR(64) NOT NULL,
  PRIMARY KEY(switch_ip)
)
TYPE=InnoDB;

CREATE TABLE vaerelser (
  vaerelse VARCHAR(64) NOT NULL,
  telefon VARCHAR(16) NULL,
  vaerelse_type ENUM('lejlighed', 'vaerelse', 'koekken', 'andet') NOT NULL DEFAULT 'andet',
  PRIMARY KEY(vaerelse)
)
TYPE=InnoDB;

CREATE TABLE tidligere_beboere (
  idtidligere_beboere INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  navn VARCHAR(128) NOT NULL,
  vaerelse VARCHAR(64) NOT NULL,
  indflytning DATE NULL,
  udflytning DATE NULL,
  brugernavn VARCHAR(64) NULL,
  PRIMARY KEY(idtidligere_beboere),
  FOREIGN KEY(vaerelse)
    REFERENCES vaerelser(vaerelse)
      ON DELETE RESTRICT
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE brugere (
  brugernavn VARCHAR(64) NOT NULL,
  vaerelse VARCHAR(64) NOT NULL,
  navn VARCHAR(128) NOT NULL,
  password_cleartext VARCHAR(64) NULL,
  password_nt VARCHAR(64) NULL,
  password_unix VARCHAR(64) NULL,
  email VARCHAR(128) NULL,
  mobilnummer VARCHAR(64) NULL,
  hjemmeside VARCHAR(512) NULL,
  net_tilmeldt_dato DATE NULL,
  net_tilmeldt_dato_kabstatus DATE NULL,
  spaerret_net_konto BOOL NOT NULL DEFAULT FALSE,
  skjult_navn BOOL NOT NULL DEFAULT FALSE,
  skjult_email BOOL NOT NULL DEFAULT FALSE,
  indflytning DATE NULL,
  udflytning DATE NULL,
  lejer_type ENUM('lejer1', 'lejer2', 'fremlejer') NOT NULL DEFAULT 'lejer1',
  kab_lejemaal_id VARCHAR(64) NULL,
  PRIMARY KEY(brugernavn),
  UNIQUE INDEX brugere_email(email),
  FOREIGN KEY(vaerelse)
    REFERENCES vaerelser(vaerelse)
      ON DELETE RESTRICT
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE gruppemedlemskaber_eksterne (
  email VARCHAR(128) NOT NULL,
  gruppenavn VARCHAR(64) NOT NULL,
  mail_forfatter BOOL NOT NULL DEFAULT 0,
  mail_modtag BOOL NOT NULL DEFAULT TRUE,
  beskrivelse VARCHAR(256) NULL,
  PRIMARY KEY(email, gruppenavn),
  FOREIGN KEY(gruppenavn)
    REFERENCES grupper(gruppenavn)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE gruppemedlemskaber (
  brugernavn VARCHAR(64) NOT NULL,
  gruppenavn VARCHAR(64) NOT NULL,
  oprettet DATE NOT NULL,
  gruppeadmin BOOL NOT NULL DEFAULT FALSE,
  mail_forfatter BOOL NOT NULL DEFAULT FALSE,
  mail_modtag BOOL NOT NULL DEFAULT TRUE,
  PRIMARY KEY(brugernavn, gruppenavn),
  FOREIGN KEY(brugernavn)
    REFERENCES brugere(brugernavn)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(gruppenavn)
    REFERENCES grupper(gruppenavn)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE switch_patching (
  port INTEGER UNSIGNED NOT NULL,
  switch_ip VARCHAR(64) NOT NULL,
  vaerelse VARCHAR(64) NULL,
  note TEXT NULL,
  PRIMARY KEY(port, switch_ip),
  FOREIGN KEY(switch_ip)
    REFERENCES switche(switch_ip)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(vaerelse)
    REFERENCES vaerelser(vaerelse)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;



INSERT INTO grupper SET gruppenavn='nyk_alle_beboere', beskrivelse='Official notices to all residents from the janitor, network group etc. Only selected trusted members can write to this list', mail_liste_navn='alle_beboere', mail_skriverettigheder='udvalgte_medlemmer', mail_obligatorisk=TRUE;
INSERT INTO grupper SET gruppenavn='nyk_meddelelse', beskrivelse='Open mail list to which all residents can write to', mail_liste_navn='meddelelse', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_kc_reklame', beskrivelse='Advertisments from Cafe on events', mail_liste_navn='kc_reklame', mail_skriverettigheder='udvalgte_medlemmer';

-- Kældercaféens lister --
INSERT INTO grupper SET gruppenavn='nyk_cafe', beskrivelse='KælderCaféen', mail_liste_navn='cafe', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_kc_bestyr', beskrivelse='KælderCaféens Board', mail_liste_navn='kc_bestyr', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_kc_ansat', beskrivelse='KælderCaféens employees', mail_liste_navn='kc_ansat', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_kc_bartender', beskrivelse='KælderCaféens bartenders', mail_liste_navn='kc_bartender', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_kc_pr', beskrivelse='KælderCaféens PR', mail_liste_navn='kc_pr', mail_skriverettigheder='alle_medlemmer';

-- Div udvalg med maillister --
INSERT INTO grupper SET gruppenavn='nyk_beboerraadet', beskrivelse='Members of the resident council (beboerrådet)', mail_liste_navn='beboerraadet', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_netdrift', beskrivelse='The network group', mail_liste_navn='netdrift', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_bestyrelsen', beskrivelse='The board (bestyrelsen)', mail_liste_navn='bestyrelsen', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_intern_bestyrelsen', beskrivelse='The board (bestyrelsen) internal', mail_liste_navn='intern-bestyrelsen', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_festival', beskrivelse='Organizers of Nybro Festival', mail_liste_navn='festivalgruppe', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_kasserer', beskrivelse='Treasurer (kasserer) of dormitory association NYK', mail_liste_navn='kasserer', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_klage', beskrivelse='Complaint Committee', mail_liste_navn='klage', mail_skriverettigheder='alle';
INSERT INTO grupper SET gruppenavn='nyk_tidende', beskrivelse='Nybrotidende editor', mail_liste_navn='tidende', mail_skriverettigheder='alle';

-- Gange og lejlighedslister --
INSERT INTO grupper SET gruppenavn='nyk_lejligheder', beskrivelse='Apartments of the domentory', mail_liste_navn='lejligheder', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_ab_lige', beskrivelse='Residents of corridor AB-lige', mail_liste_navn='ab-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_ab_ulige', beskrivelse='Residents of corridor AB-ulige', mail_liste_navn='ab-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_cd_lige', beskrivelse='Residents of corridor CD-lige', mail_liste_navn='cd-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_cd_ulige', beskrivelse='Residents of corridor CD-ulige', mail_liste_navn='cd-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_ef_lige', beskrivelse='Residents of corridor EF-lige', mail_liste_navn='ef-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_ef_ulige', beskrivelse='Residents of corridor EF-ulige', mail_liste_navn='ef-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_gh_lige', beskrivelse='Residents of corridor GH-lige', mail_liste_navn='gh-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_gh_ulige', beskrivelse='Residents of corridor GH-ulige', mail_liste_navn='gh-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_jk_lige', beskrivelse='Residents of corridor JK-lige', mail_liste_navn='jk-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_jk_ulige', beskrivelse='Residents of corridor JK-ulige', mail_liste_navn='jk-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_lm_lige', beskrivelse='Residents of corridor LM-lige', mail_liste_navn='lm-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_lm_ulige', beskrivelse='Residents of corridor LM-ulige', mail_liste_navn='lm-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_no_lige', beskrivelse='Residents of corridor NO-lige', mail_liste_navn='no-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_no_ulige', beskrivelse='Residents of corridor NO-ulige', mail_liste_navn='no-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_st_lige', beskrivelse='Residents of corridor ST-lige', mail_liste_navn='st-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_st_ulige', beskrivelse='Residents of corridor ST-ulige', mail_liste_navn='st-ulige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_pr_lige', beskrivelse='Residents of corridor PR-lige', mail_liste_navn='pr-lige', mail_skriverettigheder='alle_medlemmer';
INSERT INTO grupper SET gruppenavn='nyk_pr_ulige', beskrivelse='Residents of corridor PR-ulige', mail_liste_navn='pr-ulige', mail_skriverettigheder='alle_medlemmer';

-- Div udvalg/klubber uden maillister --
INSERT INTO grupper SET gruppenavn='nyk_cykelvaerksted', beskrivelse='Cykelværkstedet';
INSERT INTO grupper SET gruppenavn='nyk_kanolaug', beskrivelse='Kanolauget';
INSERT INTO grupper SET gruppenavn='nyk_musikrum', beskrivelse='Musikrummet';
INSERT INTO grupper SET gruppenavn='nyk_motionsrum', beskrivelse='Motionsrummets';
INSERT INTO grupper SET gruppenavn='nyk_sekretariatet', beskrivelse='Sekretariatet';

<?php

/*

   minimal german language setup
   -----------------------------

   Dieses Skript verstellt die EWIKI_KonfigurationsKonstanten (in erster
   Linie die WikiSeitenNamen), so daß sie auf de_* abgestimmt werden.
   Außerdem können die englischen Übersetzungen deaktiviert werden (was
   jedoch nicht empfohlen wird).

   Dieses Skript muß unbedingt noch VOR dem Hauptscript geladen werden!
   Also z.B.:
     include("fragments/force_lang_de.php")
     include("ewiki.php")

   Die Verwenung bringt auch viele Nebenwirkungen mit sich, da einige der
   englischen SeitenNamen (z.B. PowerSearch) stellenweise noch fest kodiert
   sind. Es empfiehlt sich daher evtl. der Einsatz von "plugins/alias.php"!

*/


#-- SeitenNamen:
define("EWIKI_PAGE_INDEX", "ErfurtWiki");
define("EWIKI_PAGE_NEWEST", "NeuesteSeiten");
define("EWIKI_PAGE_SEARCH", "SeitenSuche");
define("EWIKI_PAGE_HITS", "MeistBesuchteSeiten");
define("EWIKI_PAGE_VERSIONS", "AmHäufigstenGeänderteSeiten");
define("EWIKI_PAGE_UPDATES", "AktualisierteSeiten");

define("EWIKI_PAGE_CALENDAR", "SeitenKalender");
define("EWIKI_PAGE_YEAR_CALENDAR", "JahresKalender");
define("EWIKI_PAGE_UPLOAD", "DateiUpload");
define("EWIKI_PAGE_DOWNLOAD", "DateiDownload");
define("EWIKI_PAGE_EMAIL", "GeschützteEmail");
define("EWIKI_PAGE_IMAGEGALLERY", "BilderGallerie");
define("EWIKI_PAGE_ORPHANEDPAGES", "VerwaisteSeiten");
define("EWIKI_PAGE_PAGEINDEX", "SeitenIndex");
define("EWIKI_PAGE_POWERSEARCH", "ErweiterteSuche");
define("EWIKI_PAGE_RANDOMPAGE", "ZufälligeSeite");
define("EWIKI_PAGE_WORDINDEX", "WortIndex");


#-- vom Browser/Benutzer gewünschte Sprache mutwillig überschrieben:
$_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de; q=1.0, en; q=0.5, eo; q=0.07";


?>
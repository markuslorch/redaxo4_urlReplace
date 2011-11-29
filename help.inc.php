<?php
/**
 * urlReplace - Help File
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 */
?>
<b>urlReplace - URL-Manipulations-AddOn</b>
<p>Dieses AddOn schreibt die URLs die &uuml;ber get_rexUrl() generiert wurden um, sofern der Zielartikel keinen Inhalt hat. Es wird stadessen die URL des n&auml;chsttieferen mit Inhalt versehenem Artikels zur&uuml;ckgegeben. Zus&auml;tzlich k&ouml;nnen benutzerdefinierte URLs angegeben werden (Intern wie extern). Dieses AddOn sollte NICHT mit realurl verwendet werden (dieses AddOn beinhaltet diese Funktionalit&auml;t bereits), ist ansonsten vom URL-Rewriter unabh&auml;nig.</p>
<p>Seit Version 2 werden alle URL-Manipulationen gecached, sodass keine Datenbankabfragen im Frontend entstehen</p>

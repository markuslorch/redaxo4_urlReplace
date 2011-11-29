<?php
//Datenbank entfernen
$db = new rex_sql();
$db->setQuery('DROP TABLE `rex_746_rules`');

if ($db->getError()) {
  $REX['ADDON']['installmsg']['urlreplace'] = 'Failed to drop the database.<br>Mysql says:'.$db->getError();
}

$REX['ADDON']['install']['urlreplace'] = 0;
?>

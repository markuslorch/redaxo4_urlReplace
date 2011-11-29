<?php
// Create database
$db = new rex_sql();
$db->setQuery('SELECT rid FROM `rex_746_rules` LIMIT 1');

// Wenn nicht vorhanden, neue Tabelle anlegen.  
if ($db->getError())
{
  $db->setQuery('CREATE TABLE `rex_746_rules` (
  `rid` int(10) unsigned NOT NULL auto_increment,
  `aid` int(11) NOT NULL,
  `clang` int(11) default NULL,
  `target_intern` int(11) default NULL,
  `target_extern` varchar(255) NOT NULL,
  `method` int(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL,
  PRIMARY KEY  (`rid`),
  KEY `aid` (`aid`)
  ) ENGINE=MyISAM;');

  if ($db->getError())
  {
    $REX['ADDON']['installmsg']['urlreplace'] = 'Failed to create the database.<br>Mysql says:'.$db->getError();
  }
}

$REX['ADDON']['install']['urlreplace'] = 1;
?>
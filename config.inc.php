<?php
/**
 * urlReplace - Config File
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 * @version v2.0.3
 *
 * Dank an jandeluxe fuer die zahlreichen Tipps
 */

//REDAXO CONFIG
$mypage = "urlreplace";
$REX['ADDON']['page'][$mypage] = $mypage;
$REX['ADDON']['name'][$mypage] = 'urlReplace';
$REX['ADDON']['perm'][$mypage] = 'urlReplace[]';
$REX['ADDON']['version'][$mypage] = '2.1';
$REX['ADDON']['author'][$mypage] = 'Markus Lorch / ITKULT';
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum/addons-f30/urlreplace-url-manipulation-t14712.html';
$REX['ADDON']['path'] = dirname(__FILE__);

$REX['PERM'][] = 'urlReplace[]';

// Replace Klasse
require_once $REX['ADDON']['path'].'/classes/class.urlreplacer.inc.php';

// Replace Klasse definieren  
$replacer = new urlReplacer();

// Extention Points registrieren
if($REX['REDAXO'])
{
  $urlreplaceExtensions = array(
    'URLREPLACE_RULE_UPDATED', 'CAT_DELETED', 'ART_ADDED',
    'ART_DELETED', 'ART_TO_CAT', 'CAT_TO_ART',
    'ART_TO_STARTPAGE', 'CLANG_ADDED', 'CLANG_DELETED',
    'ALL_GENERATED', 'CAT_STATUS', 'ART_STATUS',
    'ART_CONTENT_UPDATED', 'CAT_UPDATED', 'ART_UPDATED');

  foreach($urlreplaceExtensions as $urlreplaceExtension)
  {
    rex_register_extension($urlreplaceExtension, array ($replacer, 'generate'));
  }

  // Abwaertskompatibilitaet mit Rex 4.x herstellen
  if($REX['VERSION'] == 4 && $REX['SUBVERSION'] < 3)
    rex_register_extension('PAGE_CONTENT_HEADER', array ($replacer, 'generate'));
  
  // Links im Backend einfuegen
  rex_register_extension('PAGE_CONTENT_MENU', array($replacer, 'addToPageContentMenu'));
  $REX['ADDON'][$mypage]['SUBPAGES'] = array(array('', '&Uuml;bersicht'));
}

// Urls-Manipulieren  
rex_register_extension('URL_REWRITE', array($replacer, 'replace'));
?>

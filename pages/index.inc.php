<?php
/**
 * urlReplace - Config File
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 * @version v2010-2
 */
 
// Parameter
$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');


// Include Header and Navigation
include $REX['INCLUDE_PATH'].'/layout/top.php';

rex_title('urlReplace AddOn', $REX['ADDON'][$page]['SUBPAGES']);

// Include Current Page
switch($subpage)
{
    case 'documentation':
        require $Basedir .'/documentation.inc.php';
    break;
    case 'rule':
        require $Basedir .'/rule.inc.php';
    break;
    default:
        require $Basedir .'/overview.inc.php';
}

// Include Footer 
include $REX['INCLUDE_PATH'].'/layout/bottom.php';
?>

<?php
/**
 * urlReplace - urlReplacer Class
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 * @version v2.1
 *
 * Mindestens rex4.3 wegen Extensionpoint ART_CONTENT_UPDATE
 */
define('REPLACE_TARGETS', $REX['INCLUDE_PATH'].'/generated/files/urlreplace_targets.php');
 
class urlReplacer
{
  /*
  * Konstruktor
  */
  function urlReplacer()
  {
    // ----
  }

  /*
  * Cache neu erstellen oder Aktuallisieren
  */
  function generate($params)
  {
    global $REX, $REPLACE_TARGET;
    
    if(file_exists(REPLACE_TARGETS))
    {
      // Cachfile fuer modifizierung einbinden
      require_once(REPLACE_TARGETS);
    }
  
    if(!isset($REPLACE_TARGET)) 
      $REPLACE_TARGET = array();
  
    if(!isset($params['extension_point']))
      $params['extension_point'] = '';
    
    $where = '';
    $column = '';
    
    switch($params['extension_point'])
    {
    case 'URLREPLACE_RULE_UPDATED':
      // Cache fuer Artikel neu erstellen
      $column = 'id';
      $where = '(id='. $params['id'] .' AND clang='. $params['clang'] .')';
      break;
    case 'ART_STATUS':
    case 'CAT_STATUS':
    case 'CAT_DELETED':
    case 'ART_DELETED':
    case 'CAT_UPDATED':
    case 'ART_UPDATED':
    case 'ART_TO_CAT':
    case 'CAT_TO_ART':
    case 'ART_TO_STARTPAGE':
    case 'ART_CONTENT_UPDATED':
      //Cache nur fuer uebergeordneten Artikel neu erstellen
      $column = 're_id';
      $where = '(id='. $params['id'] .' AND clang='. $params['clang'] .')';
      break;
    //case 'ART_UPDATED':
      //
    case 'CLANG_ADDED':
    case 'CLANG_UPDATED':
    case 'CLANG_DELETED':
    case 'ALL_GENERATED':
    default:
      //Array leeren, Cache fuer alle neue erstellen.
      $REPLACE_TARGET = array();
      $column = 'id';
      $where = '1=1';
			break;
    }
    
    if($where != '')
    {
      $db = new rex_sql();
      // $db->debugsql=true;
      $db->setQuery('SELECT id,clang,re_id FROM '. $REX['TABLE_PREFIX'] .'article WHERE '.$where.' AND startpage=1 AND revision=0 OR revision IS NULL');

      while($db->hasNext())
      {
        $current_article = $db->getValue($column);
        if($current_article == 0) $current_article = $db->getValue('id'); // Wenn in Root-Ebene eigene URL neu errechnen
        $current_clang = $db->getValue('clang');
        $article_target = '';

        $article_target = $this->getArticleTarget($current_article,$current_clang);

        if($article_target != '')
          // Artikel Weiterleitung in Array schreiben
          $REPLACE_TARGET[$current_article][$current_clang] = $article_target;
        else
          unset($REPLACE_TARGET[$current_article][$current_clang]);
      
        $db->next();    
      }
    }
    // Ganzes Array in Cachefile schreiben  
    rex_put_file_contents(REPLACE_TARGETS, "<?php\n\$REPLACE_TARGET = ". var_export($REPLACE_TARGET, true) .";\n");        
  }
  
  /*
  * Artikelweiterleitung abfragen und zurueckgeben
  */
  function getArticleTarget($article_id, $clang)
  {
    global $REX;
    
    // Benutzerdefiniertes Ziel ermitteln
    $user_target = $this->getUserArticleTarget($article_id, $clang);
        
    if($user_target != '')
    {
      $url = $user_target;
    }
    else
    {
      // Die Logik dieser Funktion wurde im Wesentlichen aus 'realurl' von Nicole Ruediger uebernommen

      $db = new rex_sql();
      //$db->debugsql=true;
      // Alle Slices des Artikels ausgeben    
      $slice = $db->getArray('SELECT COUNT(id) as count FROM `'.$REX['TABLE_PREFIX'].'article_slice` WHERE (article_id='.$article_id .') AND (clang='.$clang.')');

      if($slice[0]['count'] == 0)
      {
        // Naechstmoegliche Unterkategorie herausfinden
        $categories = $db->getArray('SELECT id FROM `'.$REX['TABLE_PREFIX'].'article` WHERE (re_id='.$article_id .') AND (clang='.$clang.') AND (catprior!=0) AND (status=1) ORDER BY catprior LIMIT 0,1');

    	  if (isset($categories[0]))
        {
		  	  $url = $categories[0]['id'];
   			}
        else
        {
          // Kindartikel der aktuellen Kategorie herausfinden
          $articles = $db->getArray('SELECT id FROM `'.$REX['TABLE_PREFIX'].'article` WHERE (re_id='.$article_id.') AND (catprior=0) AND (startpage=0) AND (clang='.$clang.') AND (status=1) ORDER BY prior LIMIT 0,1');
			  
          if(isset($articles[0]))
    		    $url = $articles[0]['id'];	
        }      
      }
    }
    
    if(isset($url))
      return $url;
  }
  
  /*
  * Gibt Benutzerangabe zurueck
  */
  function getUserArticleTarget($article_id, $clang)
  {
    global $REX;
    
    $db = new rex_sql();

    $article = $db->getArray('SELECT * FROM `'.$REX['TABLE_PREFIX'].'746_rules` WHERE (aid='.$article_id.') AND (clang='.$clang.') LIMIT 1');
    
    if(count($article) > 0)
    {
      if($article['0']['ignore'] != true)
      {
        if(isset($article[0]))
        {
          if(!empty($article['0']['target_extern']))
            $url = $article['0']['target_extern'];
          else
            $url = $article['0']['target_intern'];
        }
      }
      else
      {
        // Bei ignore Ziel-ID auf sich selbst setzen.
        $url = $article_id;
      }
    }
    
    if(isset($url))
      return $url;
  }

  /*
  * URL aus Cache auslesen und get_rexUrl() manipulieren
  */
  function replace($params)
  {
    global $REX, $REPLACE_TARGET;
  
    $id = $params['id'];
    $name = $params['name'];
    $clang = $params['clang'];
    $params = $params['params'];
    
    // Cache bei Bedarf neu erstellen
    if(!file_exists(REPLACE_TARGETS))
       $this->generate(array());
    
    // Cachefile einbinden
    require_once(REPLACE_TARGETS);
    
    //Pruefen, ob weiterleitung erforderlich
    if(isset($REPLACE_TARGET[$id][$clang]))
    {
      if($REPLACE_TARGET[$id][$clang] == $id)
        // Nichts tun
        $url = '';
      elseif(is_numeric($REPLACE_TARGET[$id][$clang]))
        // Interne Weiterleitung auf Artikel
        // Erzeug wenn erforderlich rekursiver Aufruf
        $url = rex_getUrl($REPLACE_TARGET[$id][$clang],$clang);
      else
        // Ersetzen mit benutzerdefinierter Url
        $url = $REPLACE_TARGET[$id][$clang];
    }
    
    if(isset($url))
      return $url;
  }
  
  /*
  * Link in Page-Content-Menue einfuegen
  */
  function addToPageContentMenu($params)
  {
    global $REX;
  
    if($REX['USER']->hasPerm('urlReplace[]') || $REX["USER"]->isAdmin())
    {
      $newLink = '<a href="index.php?page=urlreplace&amp;subpage=rule&amp;aid='.$params['article_id'].'&amp;clang='.$params['clang'].'&amp;ref='.$params['mode'].'" '.rex_tabindex().'>URL ersetzen</a>';
      //PAGE_CONTENT_MENU neu sortieren
      //Array verschieben und als vorletztes Element einfuegen.
      array_splice($params['subject'], '-1', '-1', $newLink);
    }
    return $params['subject'];
  }
}
?>
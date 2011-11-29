<?php
/**
 * urlReplace - rule.inc.php
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 * @version v2010-2
 */

//Paramter aus URL einlesen
$aid = rex_get('aid', 'int'); //Artikel ID
$clang = rex_get('clang', 'int'); //Sprache
$ref = rex_get('ref', 'string'); //Momentan ohne Funktion

//SQL KLasse initlialisieren
$sql = new rex_sql();

//Formulardaten aus Datenbank auslesen, sofern vorhanden
$article = $sql->getArray('SELECT * FROM `'.$REX['TABLE_PREFIX'].'746_rules` WHERE (aid='.$aid.') AND (clang='.$clang.') LIMIT 1');

//Updaten der Einstellung
if ($func == 'update')
{
  //Mit Post &uuml;bergebene Daten lesen
  $target_id = rex_post('LINK','array');
  $target_id = $target_id['1'];
  $target_url = rex_post('target_url','string');
  $ignore_target = rex_post('ignore_target','int');
  
  $sql->setTable($REX['TABLE_PREFIX'].'746_rules');
  
  //Pr&uuml;fen ob Regel gel&ouml;scht werden muss.
  //Ansonsten vorhandenen Datensatz aktuallisieren
  if(empty($target_id) && empty($target_url) && $ignore_target == 0)
  {
    $sql->setWhere('aid = '.$aid.'');  
    $sql->delete();
    echo rex_warning("Die Regel wurde erfolgreich gel&ouml;scht!");  
  }
  else
  {
    $sql->setValue('aid', $aid);
    $sql->setValue('clang', $clang);
    $sql->setValue('target_intern', $target_id);
    $sql->setValue('target_extern', $target_url);
    $sql->setValue('ignore', $ignore_target);

    if(isset($article['0']))
    {
      //Datensatz Aktuallisieren.
      $sql->setWhere('aid='.$aid.' AND clang='.$clang.'');
      $sql->update();

      echo rex_info("Die &Auml;nderungen wurden &uuml;bernommen.");
    }
    else
    {
      //Datensatz neu erstellen
      $sql->insert();
      echo rex_info("Es wurde eine neue Regel erstellt.");
    }
  }
  
  // Extention Point registrieren
  echo rex_register_extension_point('URLREPLACE_RULE_UPDATED', '', array('id' => $aid,'clang' => $clang)  );

  //Aktuallisierte Daten auslesen
  $article = $sql->getArray('SELECT * FROM `'.$REX['TABLE_PREFIX'].'746_rules` WHERE (aid='.$aid.') AND (clang='.$clang.') LIMIT 1');
}

//Datenbankdaten in Variablen schreiben
$target_id = $article['0']['target_intern'];
$target_url = $article['0']['target_extern'];
$ignore_target = $article['0']['ignore'];

if ($target_id > 0)
{
  $target_article = OOArticle::getArticleById($target_id, $clang);
  $target_id = $target_article->getId();
  $target_id_name = $target_article->getName();
}

//Informationen &uuml;ber den zu bearbeitenden Artikel
$update_article = OOArticle::getArticleById($aid);	
?>

<div class="rex-addon-output">
<h2 class="rex-hl2"><?php echo $update_article->getName(); ?> - [<a href="<?php echo 'index.php?page=content&category_id='.$update_article->getValue('category_id').'&article_id='.$aid.'';?>">Artikel editieren</a>]</h2>


<div class="rex-form">
<form action="<?php echo 'index.php?page=urlreplace&subpage=rule&func=update&aid='.$aid.'&clang='.$clang.'&ref='.$ref.''; ?>" method="post" enctype="multipart/form-data" id="REX_FORM">
<fieldset class="rex-form-col-1">
<legend><span>URL ersetzen durch:</span></legend>
<div class="rex-form-wrapper">
            
<div class="rex-form-row">
<label for="rex-form-startarticle-id">Internes Ziel</label>
<?php echo rex_var_link::getLinkButton(1, $target_id); ?>
</div>
				      	

<div class="rex-form-row"><p class="rex-form-text">
<label for="target_url">Externes Ziel</label>
<input class="rex-form-text" type="text" name="target_url" value="<?php echo $target_url; ?>" />
</p>
</div>

<div class="rex-form-row"><p class="rex-form-col-a rex-form-checkbox">
<label for="Teaser">Nichts ersetzen</label>
<input class="rex-form-checkbox" type="checkbox" name="ignore_target" value="1" <?php if($ignore_target == 1) echo 'checked="checked"'; ?>/>
</p>
</div>

									<div class="rex-form-row">
										<p class="rex-form-col-a rex-form-submit">

								  		<input class="rex-form-submit" type="submit" value="Einstellung speichern" accesskey="s" title="Einstellung speichern"/>
										</p>
									</div>
									<div class="rex-clearer"></div>


</div>
</fieldset>
</form>
</div>

</div>

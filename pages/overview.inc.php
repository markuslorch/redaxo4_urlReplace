<?php
/**
 * urlReplace - overview.inc.php
 * @author m[dot]lorch[at]it-kult[dot]de Markus Lorch
 * @package redaxo4
 * @version v2010-2
 */
?>

<div id="rex-output">

<table class="rex-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Ziel-URL</th>
        <th>Ignore</th>
        <th>Sprache</th>
        <th>Aktion</th>
      </tr>
    </thead>
<tbody>

<?php
$sql = new sql();
$sql->setQuery('SELECT * FROM `'.$REX['TABLE_PREFIX'].'746_rules` ORDER BY `aid`');
for($i = 0; $i < $sql->getRows(); $i++)
{
  $ignore_target = '';
  $url_target = '';
  
  $article = OOArticle::getArticleById($sql->getValue('aid'), $sql->getValue('clang'));
  $target_article = OOArticle::getArticleById($sql->getValue('target_intern'), $sql->getValue('clang'));
  
  if($sql->getValue('target_intern') > 0)
    $url_target = '<a href="index.php?page=content&category_id='.$target_article->getValue('category_id').'&article_id='.$target_article->getId().'">'.$target_article->getName().'</a>';
  if($sql->getValue('target_extern') != '')
    $url_target = '<a href="'.$sql->getValue('target_extern').'">'.$sql->getValue('target_extern').'</a>';
  if($sql->getValue('ignore') == 1)
    $ignore_target = 'true';
  
  echo '<tr>';
  echo '<td>'. $sql->getValue('aid') .'</td>';
  echo '<td>'. $article->getName() .'</td>';
  echo '<td>'. $url_target .'</td>';
  echo '<td>'. $ignore_target .'</td>';
  echo '<td>'. $sql->getValue('clang') .'</td>';
  echo '<td><a href="index.php?page=urlreplace&subpage=rule&aid='.$sql->getValue('aid').'&clang='.$sql->getValue('clang').'">Bearbeiten</a></td>';
  echo '</tr>';
  $sql->next();
}

if($i < 1)
{
  echo '<tr><td colspan="6">Es wurden keine Regeln angelegt.</td></tr>';
}
?>
</tbody>
</table>

</div>


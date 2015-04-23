<?php

function cml_frontend_hide_translations_for_tags($wp_query) {
  global $wpCeceppaML, $wpdb;

  /*
    Può capitare che l'utente invece di tradurre un tag ne usa uno nuovo per l'articolo. In questo caso
    aggiungendo all'url ?lang=##, viene visualizzato il messaggio 404 se per quella lingua non c'è nessun
    articolo con quel tag, il ché può essere un po' noioso.
    Dagli articoli da escludere rimuovo tutti quelli che non hanno quel tag. Così se assegno 2 tag diversi
    all'articolo e alla sua traduzione non mi ritrovo con un 404.
    Il metodo, a causa dei vari cicli da eseguire, probabilmente porterà dei rallentamenti nel caricamento delle pagine
    dei tag, però è anche l'unico modo di evitare pagine 404 se all'utente piace assegnare tag diversi invece di tradurli...
  */
  $tag_name = $wp_query->query_vars['tag'];
  $i = 0;

  if( ! is_object( $wpCeceppaML ) || ! is_array( $wpCeceppaML->_hide_posts ) ) return;
  foreach( $wpCeceppaML->_hide_posts as $id ) :
    $tags = wp_get_post_tags($id);
    $lang_id = CMLLanguage::get_id_by_post_id($id);

    foreach($tags as $tag) :
      if($tag->name == $tag_name && CMLLanguage::is_current( $lang_id ) ) :

	//Controllo che per questa articolo non esista nessuna traduzione nella lingua corrente con la stessa categoria
	$nid = cml_get_linked_post($lang_id, null, $id, CMLLanguage::get_current_id());
	if(!empty($nid)) :
	  //Verifico le categorie dell'articolo collegato
	  $_tags = wp_get_post_tags($nid);
	  $found = false;
	  foreach($_tags as $_tag) :
	    if($_tag->name == $tag_name) :
	      $found = true;
	      break;
	    endif;
	  endforeach;

	  if(!$found) :
	    unset($wpCeceppaML->_hide_posts[$i]);
	    break;
	  endif;
	endif;
      endif;
    endforeach;

    $i++;
  endforeach;
}
?>

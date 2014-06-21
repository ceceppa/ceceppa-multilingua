<?php
//Compatibility api for CML < 1.4

/**
 * Return object about all languages
 *
 * @param $only_enabled - return only enabled items
 * @param $no_default - exclude default language
 *
 * La struttura dell'array è la seguente:
 *	id - id della lingua
 *	cml_default - 1 o 0 a seconda se la lingua è quella predefinita o meno
 *	cml_flag    - nome della bandiera
 *	cml_notice_post - avviso "articolo"
 *	cml_notice_page - avviso "pagina"
 *	cml_notice_category - avviso "categoria"
 *	cml_category_name - nome della categoria a cui è collegata la lingua
 *	cml_category_id - id della categoria collegata alla lingua
 *	cml_category_slug - abbreviazione della categoria collegata alla lingua
 *	cml_locale - locale wordpress della lingua
 *	cml_page_id - id della pagina padre collegata alla lingua
 *	cml_page_slug - abbreviazione della pagina collegata alla lingua
 */
function cml_get_languages( $only_enabled = true, $no_default = false ) {
  $langs = ( $only_enabled ) ? CMLLanguage::get_enableds() : CMLLanguage::get_all();
  
  if( $no_default ) unset( $langs[ cml_get_default_language_id() ] );
  
  return $langs;
}

/**
 * Return the path of flag by language id
 *
 * @param $id - id/slug of language
 * @param $size - flag size
 * 						tiny
 * 						small
 */
function cml_get_flag_by_lang_id( $id, $size = CML_FLAG_TINY ) {
  return CMLLanguage::get_flag_src( $id, $size );
}

/**
 * return the path of flag
 */
function cml_get_flag( $flag, $size = CML_FLAG_TINY ) {
  if( empty( $flag ) ) return "";
  
  if( file_exists( CML_UPLOAD_DIR . "/$size/$flag.png" ) )
    $url = CML_UPLOAD_DIR . "/$size/$flag.png";
  else
    $url = CML_PLUGIN_URL . "flags/$size/$flag.png";
    
  return esc_url( $url );
}

/**
 * cerco nel database la traduzione per la frase
 *
 *  @param string - stringa da cercare
 *  @param id - id della lingua in cui tradurre la frase
 *  @param - wpgettext - utilizza la funzione __ per cercare la traduzione della parola
 *  @param - gettext - indica se utilizzare la funzione "gettext" di "Danilo"
 *  @return - la frase tradotta se esiste la traduzione, altrimeni la stringa passata
 */
function cml_translate($string, $id = null, $type = "", $gettext = false, $ignore_po = false) {
  if( ! $gettext )
    return CMLTranslations::get( $id, $string, $type, false, $ignore_po );
  else
    return CMLTranslations::gettext( $id, $string, $type, $ignore_po );
}

/**
 * restituisco la descrizione della lingua
 *
 * @param id - id della lingua
 *
 * @return - il titolo della lingua
 */
function cml_get_language_title( $id = null ) {
  return CMLLanguage::get_name( $id );
}


/*
 * return current language object
 */
function cml_get_current_language() {
  return CMLLanguage::get_current();
}

function cml_get_current_language_id() {
  return CMLLanguage::get_current_id();
}

function cml_get_default_language() {
  return CMLLanguage::get_default();
}

function cml_get_default_language_id() {
  return CMLLanguage::get_default_id();
}

/*
 * Return array of linked posts
 */
function cml_get_linked_post( $post_id, $lang ) {
  return CMLPost::get_translation( $lang, $post_id );
}

function cml_get_linked_posts( $post_id ) {
  return CMLPost::get_translations( $post_id );
}
?>

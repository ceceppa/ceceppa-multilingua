<?php
if ( ! defined( 'ABSPATH' ) ) die( "Access denied" );

/**
 * Print html dropdown list with all enabled languages
 *
 * @param string $className - class to assign to <ul> list
 * @param int/string $selected - id / slug of selected language
 * @param boolean $link - if true change language and refresh page
 * @param boolean $none - if true show "no language" item
 * @param string $none_text - the text of "none" element
 * @param int/string $none_id - id of none element
 * @param boolean $only_enabled - show only enabled languages?
 * @param string $size - size of flags ( "tiny" or "small" )
 * @param $style - item styles
 * <ul>
 *                <li>
 *                  show ( string ) - choose what to display:<br />
 *                  <i>default: text</i>
 *                </li>
 *                <ul>
 *			         <li>text: show only language name</li>
 *			         <li>slug: show language slug</li>
 *			         <li>none: show no text</li>
 *			     </ul>
 * </ul>
 */
function cml_dropdown_langs( $className, $selected, $link = false, $none = false, $none_text = null, $none_id = "", $only_enabled = 1, $size = CML_FLAG_TINY, $style = "both" ) {
  $classNoLink = ( $link ) ? "" : " cml-lang-js-sel ";
  echo '<ul id="cml-lang" class="cml-lang-sel ' . $className . $classNoLink . '">';

  if( ! is_numeric( $selected ) ) {
    $selected = CMLLanguage::get_id_by_slug( $selected );
  }

  //All enabled languages
  $langs = ( $only_enabled ) ? CMLLanguage::get_enableds() : CMLLanguage::get_all();

  //selected language
  if( $selected != 0 ) {
    $url = ( $link ) ? cml_get_the_link( $langs[ $selected ], true, false, true ) : "#";
    echo @_cml_dropdown_lang_li( $url, $langs[ $selected ], 0, false, $size, $style );
  } else {
    echo _cml_dropdown_lang_li( "#", $none_text, "", false, $size, $style );
  }

  echo '<input type="hidden" name="cml-lang" value="' . $selected . '" />';
  echo '<ul>';
  foreach( $langs as $lang ) {
    $url = ( $link ) ? cml_get_the_link( $lang, true, false, true ) : "#";

    echo _cml_dropdown_lang_li( $url, $lang, $selected, 0, $size, $style );
  }

  //Append None element at bottom of the list
  if( $none ) {
    echo '</li>';

    if( $none )
      echo _cml_dropdown_lang_li( "#", $none_text, ( $selected == "0" ) ? "x" : "", 0, false, $style );
  }

echo <<< EOT
    </li>
  </ul>
</ul>
EOT;
}

/**
 * @ignore
 * @internal
 */
function _cml_dropdown_lang_li( $url, $lang = null, $hide_id = 0, $close = true, $size = CML_FLAG_TINY, $style ) {
  $id = ( is_object( $lang ) ) ? $lang->id : "x";
  $hide = ( $hide_id === $id ) ? "item-hidden" : "";

  $show = array( "", "both", "text", "flag", "slug", "fslug" );
  $display = array_search( $style, $show );

  $img = ( ( $display != 2 || $display != 4 ) && $id != "x" ) ? CMLLanguage::get_flag_img( $id, $size ) : "";
  $name = ( $display < 3 && $id != "x" ) ? CMLLanguage::get_by_id( $id )->cml_language : $lang;
  $name = ( $display == 4 ) ? CMLLanguage::get_by_id( $id )->cml_language_slug : $name;

  if( ! empty( $name ) ) $name = "<span>$name</span>";

  if( "#" == $url ) $url = "javascript:void()";

  $close = ( $close ) ? "</li>" : "";
return <<< EOT
  <li class="cml-lang-$id $hide">
    <a cml-lang="$id" href="$url">
      $img
      $name
    </a>
  $close
EOT;
}

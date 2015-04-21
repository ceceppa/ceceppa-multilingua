<?php
if ( ! defined( 'ABSPATH' ) ) die( 'Access denied' ); // Exit if accessed directly

class MyTranslations_Table extends WP_List_Table {
    private $_groups = null;
    private $_all_items = array();

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct( $groups ){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'mytranslation',     //singular name of the listed records
            'plural'    => 'mytranslations',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        $this->_groups = $groups;
    }

    function column_title($item){
      //Build row actions
      $actions = array(
          'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
          'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
      );

      //Return the title contents
      return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
          /*$1%s*/ $item['title'],
          /*$2%s*/ $item['ID'],
          /*$3%s*/ $this->row_actions($actions)
      );
    }

    function column_cb($item){
      return sprintf( '<img src="%sremove.png" title="Remove" />',
                     CML_PLUGIN_IMAGES_URL );
        //return sprintf(
        //    '<input type="checkbox" name="%1$s[]" value="%2$s" />',
        //    /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
        //    /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        //);
    }

    function get_columns(){
        $columns = array(
            'remove' => sprintf( '<img src="%sremove.png" alt="Remove" />',
                          CML_PLUGIN_IMAGES_URL ),
            'group' => __( 'Group name', 'ceceppaml' ),
            'string' => __( 'String', 'ceceppaml' ),
            'translation' => __( 'Translation', 'ceceppaml' ),
        );

      return $columns;
    }

    function get_sortable_columns() {
      $sortable_columns = array(
          'group' => array( 'group', true ),
          'string'  => array( 'string',false ),
      );

      return $sortable_columns;
    }


    function get_bulk_actions() {
      return array();
    }

    function process_bulk_action() {
      global $wpdb;
    }

    function prepare_items() {
      global $wpdb;

      /**
       * First, lets decide how many records per page to show
       */
      $per_page = 10;

      /**
       */
      $columns = $this->get_columns();
      $hidden = array( 'id' );			//L'id mi serve ma non deve essere visibile ;)
      $sortable = array(); //$this->get_sortable_columns();

      $this->_column_headers = array( $columns, $hidden, $sortable );
      $this->process_bulk_action();

      /* -- Preparing your query -- */
      $search = isset( $_GET[ 's' ] ) ? mysql_real_escape_string( $_GET[ 's' ] ) : '';

      $keys = array_keys( $this->_groups );
      $query = "SELECT min(id) as id, UNHEX(cml_text) as cml_text, cml_type FROM " . CECEPPA_ML_TRANSLATIONS .
                                " WHERE cml_type in ( '" . join( "', '", $keys ) . "' ) GROUP BY cml_text ORDER BY cml_type, UNHEX( cml_text ) ";

      $data = $wpdb->get_results( $query );

      $current_page = $this->get_pagenum();

      $total_items = count( $data );

      $this->_all_items = $data;
      $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

      $this->items = $data;

      $this->set_pagination_args( array(
          'total_items' => $total_items,                  //WE have to calculate the total number of items
          'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
          'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
      ) );
    }

    function display_rows() {
      global $wpdb;

      //Get the records registered in the prepare_items method
      // $records = $this->items;
      $records = $this->_all_items;

      //Get the columns registered in the get_columns and get_sortable_columns methods
      list( $columns, $hidden ) = $this->get_column_info();

      $alternate = "";

      //Loop for each record
      if( ! empty( $records ) ) {
        //Check for what language I have to hide translation field for default language
        $hide_for = apply_filters( "cml_my_translations_hide_default", array( 'S' ) );

        $langs = CMLLanguage::get_all();

        foreach( $records as $row => $rec ) {
          //Open the line
          $alternate = ( empty ( $alternate ) ) ? "alternate" : "";

          $group = $rec->cml_type;
          if( in_array( $rec->cml_text, array( "_notice_page", "_notice_post" ) ) ) {
            $group = "_cml_";
          }

          if( $row > 10 ) {
            $alternate .= " hidden ";
          }
          echo '<tr id="record_' . $rec->id . '" class="' . $alternate . ' row-domain string-' . $group . '">';


          foreach ( $columns as $column_name => $column_display_name ) {
            //Style attributes for each col
            $attributes = "class='$column_name column-$column_name'";

            //Display the cell
            switch ( $column_name ) {
            case "remove":
              echo '<td ' . $attributes . '>';

              if( ! in_array( $rec->cml_text, array( "_notice_page", "_notice_post" ) ) ) {
                echo '<input type="checkbox" name="delete[]" value="' . esc_attr( $rec->cml_text ) . '" id="id-' . $rec->id . '" class="id-' . $rec->id . '" />';
              }

              echo '<input type="hidden" name="id[]" value="' . intval( $rec->id ) . '" class="id-' . $rec->id . '" />';
              echo '<input type="hidden" name="group[]" value="' . $rec->cml_type . '" class="id-' . $rec->id . '" />';
              echo '</td>';
              break;
            case "group":
              echo '<td ' . $attributes . '>';
              echo '<label for="id-' . $rec->id . '">';
              echo $this->_groups[ $rec->cml_type ];
              echo '</label>';
              echo '</td>';
              break;
            case "string":
              echo '<td ' . $attributes . '>';

              $title = $rec->cml_text;
              if( "_notice_post" == $title ) {
                $title = __( "Post notice:", "ceceppaml" );
              }

              if( "_notice_page" == $title ) {
                $title = __( "Page notice:", "ceceppaml" );
              }

              if( "_" == $title[ 0 ] ) {
                $group = strtolower( $rec->cml_type ) . "_";
                $title = str_replace( $group, "", $title );
              }

              $title = apply_filters( 'cml_my_translations_label', $title, $rec->cml_type );
              echo '<input type="hidden" class="original" name="string[]" value="' . $rec->cml_text . '"/>';
              echo "<span>{$title}";
              echo '</td>';
              break;
            case "translation":
              echo '<td ' . $attributes . '>';

              /*
               * Number of elements $values must be same for each language !
               */
              foreach( $langs as $lang ) {
                $class = ( in_array( $rec->cml_type, $hide_for )
                         && CMLLanguage::is_default( $lang->id )
                         ) ? "cml-hidden" : "";

                echo '<div class="cml-myt-flag ' . $class . '">';
                echo CMLLanguage::get_flag_img( $lang->id );

                $value = CMLTranslations::get( $lang->id,
                                           $rec->cml_text,
                                           $rec->cml_type, true, true );

                $q = sprintf( "SELECT id FROM %s WHERE cml_text = '%s' AND cml_lang_id = %d", CECEPPA_ML_TRANSLATIONS, bin2hex( $rec->cml_text ), $lang->id );
                $recid = $wpdb->get_var( $q );

                echo '<input type="hidden" name="ids[' . $rec->id . '][' . $lang->id .  ']" value="' . intval( $recid ) . '" />';
                echo '&nbsp;<input type="text" name="values[' . $lang->id .  '][]" value="' . esc_attr( $value ) . '" style="width: 90%" />';
                echo '</div>';
              }
              echo '</td>';
              break;
            default:
              echo $column_name;
            } //switch
          } //endforeach; 	//$columns as $column_name

      	  echo'</tr>';
        } //foreach
      } //if
    }
}
?>

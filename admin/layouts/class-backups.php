<?php
if ( ! defined( 'ABSPATH' ) ) die( 'Access denied' ); // Exit if accessed directly

class MyBackups_Table extends WP_List_Table {
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'mybackup',     //singular name of the listed records
            'plural'    => 'mybackups',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
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
    }

    function get_columns(){
        $columns = array(
            'date' => __( 'Date', 'ceceppaml' ),
            'database' => '<img src="' . CML_PLUGIN_IMAGES_URL . 'col-database.png" alt="' . __( 'Database', 'ceceppaml' ) . '">',
            'settings' => '<img src="' . CML_PLUGIN_IMAGES_URL . 'col-settings.png" alt="' . __( 'Settings', 'ceceppaml' ) . '">',
            'delete' => '<img src="' . CML_PLUGIN_IMAGES_URL . 'remove.png" alt="' . __( 'Delete', 'ceceppaml' ) . '">',
        );

        return $columns;
    }

    function get_sortable_columns() {

        return null;
    }


    function get_bulk_actions() {
      return array();
    }

    function process_bulk_action() {
      global $wpdb;
    }

    function prepare_items() {
      $columns = $this->get_columns();
      $hidden = array( 'id' );			//L'id mi serve ma non deve essere visibile ;)
      $sortable = array(); //$this->get_sortable_columns();

      $this->_column_headers = array( $columns, $hidden, $sortable );

      //Get all file in the backup folder
      $files = glob( CECEPPAML_BACKUP_PATH . "*" );

      $data = array();
      foreach( $files as $id => $file ) {
        $info = pathinfo( $file );

        $date = $info[ 'filename' ];
        $ext  = $info[ 'extension' ];

        if( ! isset( $data[ $date ] ) ) {
          $data[ $date ] = array(
                          'ID' => $id,
                          'date' => filemtime( $file ),
                          'basename' => $info[ 'basename' ],
                          'filename' => $info[ 'filename' ],
                          'database' => ( $ext == "db" ),
                          'settings' => ( $ext == "settings" ),
          );
        } else {
          $data[ $date ][ 'database' ] = ( $ext == "db" ) ? 1 : $data[ $date ][ 'database' ];
          $data[ $date ][ 'settings' ] = ( $ext == "settings" ) ? 1 : $data[ $date ][ 'settings' ];
        }
      }

      $per_page = 20;
      $current_page = $this->get_pagenum();

      $total_items = count( $data );

      $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

      $this->items = $data;

      $this->set_pagination_args( array(
          'total_items' => $total_items,                  //WE have to calculate the total number of items
          'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
          'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
      ) );


    }

    function display_rows() {
      //Get the records registered in the prepare_items method
      $records = $this->items;

      //Get the columns registered in the get_columns and get_sortable_columns methods
      list( $columns, $hidden ) = $this->get_column_info();

      $alternate = "";

      //Loop for each record
      $i = 0;
      if( ! empty( $records ) ) {
        foreach( $records as $rec ) {
          $alternate = ( empty ( $alternate ) ) ? "alternate" : "";

          echo '<tr id="record_' . $i . '" class="' . $alternate . '">';

          foreach ( $columns as $column_name => $column_display_name ) {
            $attributes = "class='$column_name column-$column_name'";

            switch ( $column_name ) {
              case "date":
                $format = get_option('date_format') . " " . get_option('time_format');
                echo "<td>";
                echo date( $format, $rec[ 'date' ] );
                echo "</td>";
              break;
              case "database":
                echo '<td>';

                $icon = ( $rec[ 'database' ] == 1 ) ? "yes" : "none";
                $title = ( $rec[ 'database' ] == 1 ) ? __( 'Download', 'ceceppaml' ) : __( 'Backup not available', 'ceceppaml' );

                $link = "#";

                if( $rec[ 'database' ] == 1 ) {
                  $link = esc_url( add_query_arg( array(
                                                'download' => 1,
                                                'file' => $rec[ 'filename' ] . ".db"
                  ) ) );
                }

                echo '<a class="switch-me tipsy-me restore-' . $icon . '" href="' . $link . '" title="' . $title . '">';
                echo '</a>';

                echo "</td>";
                break;
              case "settings":
                echo '<td>';

                $icon = ( $rec[ 'settings' ] == 1 ) ? "yes" : "none";
                $title = ( $rec[ 'settings' ] == 1 ) ? __( 'Download', 'ceceppaml' ) : __( 'Backup not available', 'ceceppaml' );
                $link = "#";

                if( $rec[ 'settings' ] == 1 ) {
                  $link = esc_url( add_query_arg( array(
                                                'download' => 1,
                                                'file' => $rec[ 'filename' ] . ".settings"
                  ) ) );
                }

                echo '<a class="switch-me tipsy-me restore-' . $icon . '" href="' . $link . '" title="' . $title . '">';
                echo '</a>';

                echo "</td>";
                break;
              case "delete":
                echo '<td>';

                $link = esc_url( add_query_arg( array(
                                            'delete' => 1,
                                            'file' => $rec[ 'filename' ]
                                    ) )
                 );

                echo '<a class="switch-me tipsy-me icon-delete" title="' . __( 'Delete', 'ceceppaml' ) . '" href="' . $link . '">';
                echo '</a>';

                echo "</td>";
              break;
            default:
              echo "<td>$column_name</td>";
            } //switch
          } //endforeach; 	//$columns as $column_name

      	  echo'</tr>';
        } //foreach
      } //if
    }
}

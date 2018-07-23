<?php
/**
 * Creates a table with users from d5 database.
 *
 * @param array $atts: Holds the parameters for the query, uid, pahse and time period.
 *
 * @return HTML code for the table to show.
 */

class Msa_Users_Test_Table {
	
/**
     * Display the list table page
     *
     * @return Void
     */
    public function msa_users_table_page( $atts )
    {
        $msaUsersListTable = new Msa_Migrate_Test_Table();

				// Code necessary to control the pagers on frontend pages (by default table pager only works on admin pages)
				if(!isset($_REQUEST['paged'])) {
						$_REQUEST['paged'] = explode('/page/', $_SERVER['REQUEST_URI'], 2);
						if(isset($_REQUEST['paged'][1])) list($_REQUEST['paged'],) = explode('/', $_REQUEST['paged'][1], 2);
						if(isset($_REQUEST['paged']) and $_REQUEST['paged'] != '') {
						$_REQUEST['paged'] = intval($_REQUEST['paged']);
						if($_REQUEST['paged'] < 2) $_REQUEST['paged'] = '';
					} else {
						$_REQUEST['paged'] = '';
					}
				}	

        $msaUsersListTable->prepare_items( $atts );

        //$html .= $msaUsersListTable->search_box( 'search', 'search_id' ); // still not working
        //$table_display = $msaUsersListTable->display();

        $html = $msaUsersListTable->display();

			  // Fix pagination "first page link" for the table
				ob_start();
				$deals = ob_get_clean();
				$pagination = explode("<span class='pagination-links'", $deals);
				if(count($pagination) > 1) {
					$deals = "";
					foreach($pagination as $k => $links) {
						$url = array();
						$first = explode("<a class='first-page' href='", $links, 2);
						if(isset($first[1])) {
							$url = explode("'>", $first[1], 2);
							$url[0] .= (false === strpos($url[0], "?"))?"?":"&";
							$links = $first[0]."</a><a class='first-page' href='".$url[0]."paged=1'>".$url[1];
						}
						$deals .= $links;
						if($k < count($pagination)-1) $deals .= "<span class='pagination-links";
					}
				}
				echo $deals;        


        return $html;
    }
}


/**
 * Test table class
 */
// WP_List_Table is not loaded automatically so we need to load it in our application
	if( ! class_exists( 'WP_List_Table' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

/**
 * This is where the table is assembled, columns, number of items, etc.
 */

	class Msa_Migrate_Test_Table extends WP_List_Table {

    function __construct(){
	    global $status, $page;
	        parent::__construct( array(
	            'singular'  => __( 'user', 'mylisttable' ),     //singular name of the listed records
	            'plural'    => __( 'users', 'mylisttable' ),   //plural name of the listed records
	            'ajax'      => false        //does this table support ajax?
	    ) );
	    //add_action( 'admin_head', array( &$this, 'admin_header' ) );            
    }

	  function admin_header() {
	    $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	    if( 'my_list_test' != $page )
	    return;
	   /* echo '<style type="text/css">';
	    echo '.wp-list-table .column-id { width: 5%; }';
	    echo '.wp-list-table .column-booktitle { width: 40%; }';
	    echo '.wp-list-table .column-author { width: 35%; }';
	    echo '.wp-list-table .column-isbn { width: 20%;}';
	    echo '</style>';*/
	  }

	  function no_items() {
	    _e( 'No Users found.' );
	  }

	  function column_default( $item, $column_name ) {
	    switch( $column_name ) { 
	        case 'phase':
	        case 'id':
	        case 'name':
	        case 'email':
	        case 'created':
	        case 'last_login':
	        case 'access';
	        case 'ip':
	        case 'migrated':
	            return $item[ $column_name ];
	        default:
	            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
	    }
	  }

		function get_sortable_columns() {
		  $sortable_columns = array(
		    'name'  => array('name',false),
		    'id' => array('id',false),
		  );
		  return $sortable_columns;
		}

		function get_columns(){
		        $columns = array(
		            //'cb'        => '<input type="checkbox" />',
		            'phase'      => __( 'Phase', 'mylisttable' ),
		            'id'         => __( 'ID', 'mylisttable' ),
		            'name'       => __( 'Name', 'mylisttable' ),
		            'email'      => __( 'Email', 'mylisttable' ),
		            'created'    => __('Created', 'mylisttable'),
		            'last_login' => __('Last Login', 'mylisttable'),
		            'access'     => __('Access', 'mylisttable'),
		            'ip'         => __('IP', 'mylisttable'),
		            'migrated'   => __('Migrated', 'mylisttable')
		        );
		         return $columns;
		    }

		function usort_reorder( $a, $b ) {
		  // If no sort, default to title
		  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		  // If no order, default to asc
		  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		  // Determine sort order
		  $result = strcmp( $a[$orderby], $b[$orderby] );
		  // Send final sort direction to usort
		  return ( $order === 'asc' ) ? $result : -$result;
		}

		function column_name( $item ) {

			if ( !is_numeric( $item['migrated'] ) ){
				$actions = array(
					'edit'      => sprintf('<a class="button migrate-user-button" href="?page=%s&action=%s&ID=%s" data-uid="%s" >Migrate</a>','migrate-user','migrate',$item['id'], $item['id']),
				);
				return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions) );
			} else {
				$actions = array(
					'profile' => sprintf('<a href="%s" >Visit profile</a>', bp_core_get_user_domain($item['migrated'])),
				);
				return sprintf('%1s %2s', $item['name'], $this->row_actions($actions) );
			}
		}

		/*function column_booktitle($item){
		  $actions = array(
		            'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
		            'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
		        );
		  return sprintf('%1$s %2$s', $item['booktitle'], $this->row_actions($actions) );
		}*/
		/*
		function get_bulk_actions() {
		  $actions = array(
		    'delete'    => 'Delete'
		  );
		  return $actions;
		}*/

		/*function column_cb($item) {
		        return sprintf(
		            '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
		        );    
		    }*/

		function prepare_items( $atts ) {

			$query = new Msa_Migrate_Users();
			$example_data = $query->msa_migrate_list_users_preview( $atts['uid'], $atts['phase'], $atts['year'] );

			// Frontend fix to pager for the table   
			$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * 2) : 0;

		  $columns  = $this->get_columns();
		  $hidden   = array();
		  $sortable = $this->get_sortable_columns();
		  $this->_column_headers = array( $columns, $hidden, $sortable );
		  usort( $example_data, array( &$this, 'usort_reorder' ) );
		  
		  $per_page = 10;
		  $current_page = $this->get_pagenum(); 
		  $total_items = count( $example_data );
		  // only ncessary because we have sample data
		  $found_data = array_slice( $example_data, $paged, $per_page );
		  $this->set_pagination_args( array(
		    'total_items' => $total_items,                  //WE have to calculate the total number of items
		    'per_page'    => $per_page                     //WE have to determine how many items to show on a page
		  ) );
		  $this->items = $found_data;
		}

	} //class	
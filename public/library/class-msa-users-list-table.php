<?php
/**
 * Displays Users table
 */

class Msa_Users_List_Table {
	
/**
     * Display the list table page
     *
     * @return Void
     */
    public function msa_users_table_page( $users )
    {
        $msaUsersListTable = new Msa_Users_Table();
        $msaUsersListTable->prepare_items( $users );

        $table_display = $msaUsersListTable->display();

        $html = '<div class="wrap">';
        $html .= '    <div id="icon-users" class="icon32"></div>';
        $html .= '    <h2>Migrated Users</h2>';
        $html .= $table_display;
        $html .= '</div>';


        return $html;
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Msa_Users_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items( $users )
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data( $users );
        usort( $data, array( &$this, 'sort_data' ) );
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
        		'phase'   => 'Phase',
            'id'          => 'ID',
            'name'       => 'Name',
            'email' => 'Email',
            'created'        => 'Created',
            'last_login'    => 'Last Login',
            'access'      => 'Access',
            'ip'          => 'Last User IP',
        );
        return $columns;
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data( $users )
    {
        $data = array();

        foreach ($users as $uid => $info ) {
	        $data[] = array(
	        'phase'     => $info['phase'],	
          'id'          => $info['id'],
          'name'       => $info['name'],
          'email' => $info['email'],
          'created'        => $info['created'],
          'last_login'    => $info['last_login'],
          'access'      => $info['access'],
          'ip'          => $info['ip'],
          );
      	}	

        return $data;
    }
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
        		case 'phase':
            case 'id':
            case 'name':
            case 'email':
            case 'created':
            case 'last_login':
            case 'access':
            case 'ip':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'name';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }

}
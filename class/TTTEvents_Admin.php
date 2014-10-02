<?php

class TTTEvents_Admin extends TTTEvents_Common {
    
    public function init() {
        parent::init();

        if( current_user_can('edit_posts') ) {
            add_action('add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( &$this, 'save_postdata' ) );
            add_action('admin_menu', array( &$this, 'menu' ) );

        }
    }

    public function menu() {
        $s = add_submenu_page( 'options-general.php', __('TTT Events',parent::sname), __('TTT Events',parent::sname), 'edit_posts', 'ttt-events-menu', array( &$this, 'menu_page') );
    }


    public function menu_page()  {
        global $wpdb;

        require_once( TTTINC_EVENTS .'/template/admin/page.inc.php' );

    }


    public function save_postdata() {
        
        // Secondly we need to check if the user intended to change this value.
        // if ( ! isset( $_POST['myplugin_noncename'] ) || ! wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
        //     return;
        
        // Thirdly we can save the value to the database
        
        //if saving in a custom table, get post_ID
        $post_ID = get_the_ID();
        // //sanitize user input

        $_count = 1;
        unset( $_events );

        foreach( $_POST['tttevent'] as $event ) {

            $_start = $this->str_to_timestamp( $event['start_at'] ); 
            $_end = $this->str_to_timestamp( $event['end_at'] );
            if ( $_start > $_end ) {
                $_end = $_start + 3600;
                $event['end_at'] = date( 'Y-m-d H:i:s', $_end );
            }
            elseif ( !$_end ) {
                $_end = $_start + 3600;
                $event['end_at'] = date( 'Y-m-d H:i:s', $_end );
            }

            $_events[] = array_merge($event,array('id'=>$_count++));
        }
        
            
        // either using 
        add_post_meta(    $post_ID, 'tttevent', $_events, true) or
        update_post_meta( $post_ID, 'tttevent', $_events);

        $this->rebuild( $post_ID );

    }


    public function add_meta_boxes() {

        $screens = $this->get_valid_post_types();

        foreach ($screens as $screen) {
            add_meta_box(
                $this->_s('metabox'),
                __( 'TTT Events metabox', parent::sname ),
                array( &$this, 'metabox' ),
                $screen
            );
        }

    }

    public function metabox() {
        
        $post_meta = get_post_meta( get_the_ID(), 'tttevent',true );
        if ( $post_meta == '' ) $post_meta = false;

        $defaults = array(
            'id' => '',
            'start_at' => '',
            'end_at' => '',
            'description' => ''
        );

        if ( $extras = apply_filters('tttevent_meta_extra_vars', $defaults) ) {
            foreach( $extras as $key => $value ) {
                $defaults[ $key ] = $value;
            }
            if (is_array($post_meta)) {
                foreach ($post_meta as $i => $meta ) {
                    if (is_array( $extras )) {
                        foreach( $extras as $key => $value ) {
                            if ( !isset($post_meta[$i][$key] )) $post_meta[$i][$key] = $value;
                        }
                    }
                }
            }
        }

        wp_enqueue_style(  'ttt-events-metabox-css-ui', plugins_url('template/admin/jquery-ui-themes/themes/ui-darkness/jquery-ui.min.css' , dirname(__FILE__) ) );
        wp_enqueue_style(  'ttt-events-metabox-css-ui-theme', plugins_url('template/admin/jquery-ui-themes/themes/ui-darkness/jquery.ui.theme.css' , dirname(__FILE__) ) );
        wp_enqueue_style(  'ttt-events-metabox-css-ui-addon', plugins_url('template/admin/css/jquery-ui-timepicker-addon.css' , dirname(__FILE__) ) );
        wp_enqueue_style(  'ttt-events-metabox-css', plugins_url('template/admin/css/metabox.css' , dirname(__FILE__) ) );


        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'ttt-events-metabox-js-datapicker-addon', plugins_url('template/admin/js/jquery-ui-timepicker-addon.js' , dirname(__FILE__) ), array( 'jquery','underscore','jquery-ui-datepicker' ) );

        // wp_enqueue_style(  'ttt-events-metabox-css', plugins_url('template/admin/css/metabox.css' , dirname(__FILE__) ) );
        // wp_enqueue_script( 'ttt-events-metabox-js', plugins_url('template/admin/js/metabox.js' , dirname(__FILE__) ), array( 'ttt-events-js','jquery-ui-draggable','jquery-ui-droppable' ) );
        wp_localize_script('ttt-events-metabox-js-datapicker-addon', 'ttteventsPost', $post_meta );
        wp_localize_script('ttt-events-metabox-js-datapicker-addon', 'ttteventsConf', array(
            'ajax' => admin_url('admin-ajax.php'),
            'post' => get_the_ID(),
            'defaults' => $defaults
        ));
        
        require_once( TTTINC_EVENTS .'/template/admin/metabox.inc.php' );
    }

    public function rebuild( $post_id = false ) {

        global $wpdb;

        $args = array(
            'post_type' => $this->get_valid_post_types(),
            'post_status' => array('publish','private'),
            'posts_per_page'=>-1,

        );
        if ($post_id) {
            $args['p'] = $post_id;
        }

        $query = new WP_Query( $args );
        if ($query == false) return false;

        if ( $post_id ) {
            $r = $wpdb->query( $wpdb->prepare( "DELETE FROM ".$this->table_name." WHERE post_id = '".$post_id."'" ) );
        }
        else
            $wpdb->query( $wpdb->prepare( "DELETE FROM ".$this->table_name ) );

        foreach ( $query->posts as $p ) {

            $meta = get_post_meta( $p->ID, 'tttevent', true );

            if ( is_array($meta) && count($meta) > 0 ) {
                foreach ( $meta as $event ) {
                    $wpdb->insert( 
                        $this->table_name,
                        array( 
                            'post_id' => $p->ID, 
                            'start_at' => $this->str_to_timestamp( $event['start_at'], 'str' ) ,
                            'end_at' => $this->str_to_timestamp( $event['end_at'], 'str' )  
                        ) 
                    );
                }
            }
        }

        return true;


    }

    public function uninstall() {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        $this->del('version');

        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = "DROP TABLE `".$wpdb->table_name."`";
        $wpdb->query( $sql );
    }



}

?>

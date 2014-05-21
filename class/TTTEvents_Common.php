<?php

class TTTEvents extends WP_Query {
    
    public $tttevent_count;

    public function __construct( $params ) {
        $TTTEvents_Common = $this->TTTEvents_Common = new TTTEvents_Common();
        $TTTEvents_Common->init();

        $events = $TTTEvents_Common->get_events( $params );


        if (!$events || ( isset($events['count']) && $events['count'] <= 0 )) return false;



        unset( $this->tttevent_count );
        foreach ($events as $event) {
            if ( !$event->post_id ) continue;
            $evn = $this->tttevent_counter( $event->post_id );
            $event_meta[ $event->post_id ][ $evn ] = $event;
            $post__in[] = $event->post_id;
        }

        $params['post_type'] = $TTTEvents_Common->get_valid_post_types();
        $params['post__in'] = $post__in;
        $params['ignore_sticky_posts'] = 1;
        $params['orderby'] = 'post__in';
        $params['_tttevents_events'] = $events;

        add_filter('posts_join', array( &$this, 'join_event_table' ));
        $this->query($params);
        remove_filter('posts_join', array( &$this, 'join_event_table' ));

        unset( $this->tttevent_count );
        foreach ($this->posts as $k => $s ) {
            $evn = $this->tttevent_counter( $s->ID );
            $this->posts[$k]->tttevent = array_shift($event_meta[ $s->ID ]);
            //$this->posts[$k]->tttevent = $event_meta[ $s->ID ][ $env ];
            $this->posts[$k]->tttevent_meta = $this->tttevent_load_post_meta( $s->ID, $k );
        }


        // foreach( (array) $this->posts as $k => $s ) {
        //     $this->posts[$k]['_tttevent'] = $event_meta[ $s->ID ];
        // }
    }

    public function tttevent_counter( $post_id) {
        if ( !isset($this->tttevent_count[ $post_id ])) {
            $this->tttevent_count[ $post_id ] = 0;
        }
        else {
            $this->tttevent_count[ $post_id ]++;
        }
        return $this->tttevent_count[ $post_id ];
    }

    public function join_event_table($join) {
        global $wp_query, $wpdb;
        
        $join .= "LEFT JOIN ".$this->TTTEvents_Common->table_name." ON $wpdb->posts.ID = ".$this->TTTEvents_Common->table_name.".post_id ";
        
        return $join;
    }
    


    public function tttevent_date_start( $format = 'd/m/Y H:i', $k = false ) {
        if ($k)
            return date( $format, $this->posts[$k]->tttevent->start_at_timestamp );
        else
            return date( $format, $this->post->tttevent->start_at_timestamp );

    }
    public function tttevent_date_end( $format = 'd/m/Y H:i', $k = false ) {
        if ($k)
            return date( $format, $this->posts[$k]->tttevent->end_at_timestamp );
        else
            return date( $format, $this->post->tttevent->end_at_timestamp );
    }
    public function tttevent_load_post_meta( $post_id, $k ) {
        if ( !$meta = get_post_meta( $post_id, 'tttevent', true ) ) return false;

        foreach ( $meta as $data ) {
            if ( $data['start_at'] != $this->tttevent_date_start('Y-m-d H:i', $k) && $data['start_at'] != $this->tttevent_date_start('Y-m-d', $k) ) continue;
            if ( $data['end_at'] != $this->tttevent_date_end('Y-m-d H:i', $k) && $data['end_at'] != $this->tttevent_date_end('Y-m-d', $k) ) continue;

            return $data;
        }
        return false;
    }
    public function tttevent_description() {
        return $this->post->tttevent_meta['description'];
    }
    public function tttevent_meta( $key ) {
        return $this->post->tttevent_meta[ $key ];
    }

}

class TTTEvents_Common {

    const sname = 'tttevents';

    public function __construct() {
        global $wpdb;

        $this->wpdb =& $wpdb;
        $this->table_name = $wpdb->prefix.'tttevents';
    }

    public function init() {
        global $wpdb;

        $this->wpdb =& $wpdb;
        $this->table_name = $wpdb->prefix.'tttevents';
        $this->version_control();
    }


    public function get_valid_post_types() {
        $screens = get_post_types('','names');

        unset( $screens['attachment'] );
        unset( $screens['revision'] );
        unset( $screens['nav_menu_item'] );
    
        return apply_filters( 'ttt-events_post_types', $screens );
    }

    public function str_to_timestamp( $str, $ret = false ) {

        if (
            ! preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2})\:(\d{2})/',$str,$regs)
            &&
            ! preg_match('/^(\d{4})-(\d{2})-(\d{2})/',$str,$regs)
        ) return false;


        if (!isset($regs[4])) $regs[4] = 0;
        if (!isset($regs[5])) $regs[5] = 0;

        // int mktime ([ int $hour = date("H") [, int $minute = date("i") [, int $second = date("s") [, int $month = date("n") [, int $day = date("j") [, int $year = date("Y") [, int $is_dst = -1 ]]]]]]] )
        $d = mktime( (int) $regs[4], (int) $regs[5], 0, $regs[2], $regs[3], $regs[1] );
        if ( $ret )
            return date( 'Y-m-d H:i:s', $d);

        return $d;

    }


    public function get_events( $params ) {

        $defaults = array(
            '_tttevent_order' => 'start_at',
            '_tttevent_dir' => 'asc',
            //'_tttevent_between' => false
            //'_tttevent_between' => array( 'now','endmonth' )
        );

        if ( !isset($params['_tttevent_order']) ) $params['_tttevent_order'] = $defaults['_tttevent_order'];
        if ( !isset($params['_tttevent_dir']) )     $params['_tttevent_dir'] = $defaults['_tttevent_dir'];
        //if ( !isset($params['_tttevent_between']) ) $params['_tttevent_between'] = $defaults['_tttevent_between'];

        if (is_array( $params['_tttevent_between'] )) {
            $params['_tttevent_between'] = $this->dates_keys( $params['_tttevent_between'][0], $params['_tttevent_between'][1] );
        }

        // $params['debug'] = array(
        //     date('l % Y-m-d H:i', $params['_tttevent_between'][0] ),
        //     date('l % Y-m-d H:i', $params['_tttevent_between'][1] )
        // );

        //var_dump($params);

        return $this->db_query( $params );
        //var_dump($r);
        //return $r;
        
    }

    public function dates_keys( $_start, $_end ) {

        if ( ! $_time_start = $this->_keys_switch( trim(mb_strtolower($_start)), time() ) ) {
            $_time_start = $this->str_to_timestamp( $_start ); 
        }

        if ( ! $_time_end = $this->_keys_switch( trim(mb_strtolower($_end)), $_time_start, true ) ) {
            $_time_end = $this->str_to_timestamp( $_end ); 
        }

        return array( $_time_start, $_time_end );

    }

    public function _keys_switch( $k = 'now', $t = false, $end = false ) {

        switch( $k ) {
            case 'now':
                $r = time();
                break;
            case 'today':
                $r = mktime(0,0,0, date("m",$t), date("d",$t), date("Y",$t) );
                break;
            case 'monday':
                $n = date('d',$t) - date('w',$t) + 1;
                $r = mktime(0,0,0, date("m",$t), $n, date("Y",$t) );
                break;
            case 'friday':
                $t = strtotime('next Friday',$t);
                $r = mktime(0,0,0, date("m",$t), date('d',$t), date("Y",$t) );
                break;
            case 'saturday':
                $t = strtotime('next Saturday',$t);
                $r = mktime(0,0,0, date("m",$t), date('d',$t), date("Y",$t) );
                break;
            case 'sunday':
                $t = strtotime('next Sunday',$t);
                $r = mktime(0,0,0, date("m",$t), date("d",$t)+$n, date("Y",$t) );
                break;
            case 'startmonth':
                $r = mktime(0,0,0, date("m",$t), 1, date("Y",$t) );
                break;
            case 'endmonth':
                $n = date('t',$t);
                $r = mktime(0,0,0, date("m",$t), $n, date("Y",$t) );
                break;
            case 'infinite':
                if ( $end ) $r = strtotime('+10 Year',$t );
                break;
            default:
                $r = false;
                break;

        }

        if ( $r && $end ) {
            $r = strtotime('+23 hours +59 minutes',$r);
        }
    
        return $r;
    }

    public function db_query( $params = false ) {
        global $wpdb;

        $this->_db_request = "SELECT *, UNIX_TIMESTAMP(start_at) AS `start_at_timestamp`, UNIX_TIMESTAMP(end_at) AS `end_at_timestamp` FROM ".$this->table_name." ".$this->db_sql( $params );

        $e = $wpdb->get_results( $this->_db_request );

        if ( count($e) <= 0 ) {
            return false;
        }

        $e['count'] = $wpdb->num_rows;
        $e['_request'] = $this->_db_request;

        return $e;
    }

    public function db_sql( $params ) {
        return $this->db_where($params).' '.$this->db_order($params);
    }

    public function db_where( $params ) {
        
        if ( isset($params['_tttevent_between']) && $params['_tttevent_between'] ) {
            $_start = "FROM_UNIXTIME(".$params['_tttevent_between'][0].")";
            $_end = "FROM_UNIXTIME(".$params['_tttevent_between'][1].")";

            $_between = '';
            $_between .= "`start_at` BETWEEN ".$_start." AND ".$_end;
            $_between .= ' OR ';
            $_between .= "`end_at` BETWEEN ".$_start." AND ".$_end;
            $_SQL[] = $_between;
        }

        if ( isset($params['p']) && is_numeric($params['p']) && $params['p'] > 0 ) {
            $_SQL[] = "`post_id` = '". $params['p'] . "'";
        }

        if ( is_array($params['post__in']) && count($params['post__in']) > 0 ) {
            $_SQL[] = "`post_id` IN (".join(',',$params['post__in']).")";
        }

        if ( !is_array($_SQL) || count($_SQL) < 0 ) return false;
        
        foreach ($_SQL as $_s) {
            $_sql_final[] = '( '.$_s.' )';
        }

        return 'WHERE '.join(' AND ',$_sql_final);
        
    }
    
    public function db_order ( $params ) {
    
        if ( !preg_match('/^(start_at|end_at)$/i',$params['_tttevent_order'])) return false;
        if ( !preg_match('/^(asc|desc|rand)$/i',$params['_tttevent_dir'])) return false;
        
        return 'ORDER BY `'.$params['_tttevent_order'].'` '.$params['_tttevent_dir'];
        
    }

    public function _s( $s = false ) {
        if ( $s === false) return self::sname;
        return self::sname.'_'.$s;
    }
    
    public function del( $name = false ) {
        return delete_option( $this->_s( $name ) );
    }
    
    public function get( $name = false ) {
        return get_option( $this->_s( $name ) );
    }
    
    public function set( $name = false, $value ) {
        if (!get_option( $this->_s( $name ) ))
            add_option( $this->_s( $name ), $value);
        
        update_option( $this->_s( $name ) , $value);
    }

    public function version_control() {
        //$this->set('version', 0 );

        // Create & Install tables
        if ( (float) $this->get('version') < (float) TTTVERSION_EVENTS ) {
        
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $dir = TTTINC_EVENTS.'/sql';

            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if (preg_match('/^([0-9\.]+)\.inc\.php$/',$file,$regs)) {
                            $versions[] = $regs[1];
                        }
                    }
                closedir($dh);
                }
            }

            sort($versions);
            foreach( $versions as $v ) {
                if ( (float) $v >= (float) TTTVERSION_EVENTS ) break;
                 require_once( TTTINC_EVENTS.'/sql/'.$v.'.inc.php');
            }
            
            $this->set('version', TTTVERSION_EVENTS );
        }
    }

    public function get_post_events( $num = 1, $post = false ) {
        if (!$post) $post = get_the_ID();

        $num--;
        $meta = get_post_meta( $post, 'tttevents', true);

        if ( isset($meta[ $num ]) )
            return $this->query_events( (array) $meta[ $num ] );

        return false;
    }

    public function query_events( $_events = false, $_required = false, $page = false, $search = false ) { 

        // if ( is_array($_events) ) {
        //     foreach ( $_events as $_t ) {
        //         if (is_numeric($_t) and $_t > 0) {
        //             $_clean[] = $_t;
        //         }
        //     }
        //     $_gallery = $_clean;
        // }


        // $_sql = "SELECT * FROM ".$this->table_name;
        // if ( $_gallery ) {
        //     $_sql .= " WHERE id IN (".implode(',',$_gallery).")";
        //     $_sql .= " ORDER BY FIELD(id, ".implode(',',$_gallery).") ";
        // }
        // elseif ( $search ) {
        //     $search = '%'.$search.'%';
        //     $search = preg_replace('/\s+/','%',$search);
        //     $search = preg_replace('/%+/','%',$search);
        //     $_sql .= " WHERE `description` LIKE '".$search."'";
        //     $_sql .= " ORDER BY created_at DESC ";
        // }
        // elseif ( $_required == true ) {
        //     return false;
        // }
        // else {
        //     $_sql .= " ORDER BY created_at DESC ";
        // }

        // if ( $page !== false ) {
        //     $_sql .= ' LIMIT '.($page*10).', 10';
        // }


        // $rows = $this->wpdb->get_results( $_sql );

        // foreach($rows as $row) {
        //     $row->medias = $this->query_attachements( preg_split('/,/', $row->medias ) );
        //     $gallery[] = $row;
        // }

        // return $gallery;
    }

}

?>

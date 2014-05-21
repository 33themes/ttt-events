<?php

class TTTEvents_Front extends TTTEvents_Common {

	private $shortcode_counter;
	private $template_styles;

	public function init() {
		parent::init();

		add_shortcode('tttevents',array( &$this, 'shortcode_callback') );
	}

	public function load_styles( $template = 'default' ) {
		if ( !isset($this->template_styles[ $template ]) ) {
			$_s = array(
				get_template_directory().'/ttt-events/'.$template.'/styles.php',
				TTTINC_GALLERY . '/template/front/'.$template.'/styles.php'
			);
			foreach( $_s as $_template ) {
				if (!is_file($_template) || !is_readable($_template)) continue;
				
				require_once $_template;
				break;
			}
		}
	
	}

	public function template( $ttt_events ) {

		$this->load_styles( $ttt_events->template );
	
		ob_start();
		$_s = array(
			get_template_directory().'/ttt-events/'.$ttt_events->template.'/template.php',
			TTTINC_GALLERY . '/template/front/'.$ttt_events->template.'/template.php'
		);

		foreach( $_s as $_template ) {
			if (!is_file($_template) || !is_readable($_template)) continue;
			
			require $_template;
			break;
		}

		return ob_get_clean();
	}

	public function shortcode_callback( $attr ) {
		if ( ! $_id = get_the_ID() )
			$_id = 0;

		$_post = false;

		if ( isset($attr['post']) ) {
			$_post = $attr['post'];
			$_id = $_post;
		}

		if ( !isset($this->shortcode_counter[ $_id ]) || $this->shortcode_counter[ $_id ] <= 0 )
			$this->shortcode_counter[ $_id ] = 1;

		$events = $this->get_post_events( $this->shortcode_counter[$_id], $_post );
		if (!$events) return false;

		$events = array_shift($events);
		if (!$events) return false;

		if ( isset($attr['template']) )
			$events->template = $attr['template'];

		if ( !$events->template ) 
			$events->template = 'default';

		$events->rel = $_id.'-'.$events->id;

		$this->shortcode_counter[$_id]++;

		return $this->template( $events );
	}

}

?>

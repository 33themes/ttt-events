<?php

class Events_widget extends WP_Widget {
        public function __construct() {
               // widget actual processes
               parent::WP_Widget(false,'Event Calendar','description=Display Posts, Pages & CPT events info.');
        }

        public function form( $instance ) {
               //echo 'include html coding in here';
        }

        public function update( $new_instance, $old_instance ) {
               // processes widget options to be saved
        }

        public function widget( $args, $instance ) {
		?>												
		<!--BEGIN #events-->
			<aside id="events" class="widget panel vcalendar">
				<?php $_t = mktime(0,0,0,12,01,date('Y')-1); ?>
				<h4 class="widget-title"><?php _e('Events','tttevents'); ?></h4>
				<dl id="months" class="sub-nav">
					<dt><?php _e('Meses','tttevents'); ?></dt>
					<dd class="event-setdate active" data-datestart="<?php echo date('Y-m-d'); ?>" data-dateend="infinite">All</?php>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Jan</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Feb</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Mar</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Abr</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">May</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Jun</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Jul</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Aug</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Sep</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Oct</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Nov</dd>
					<dd class="event-setdate" data-datestart="<?php $_t = strtotime('+1 Month',$_t); echo date('Y-m-d',$_t); ?>" data-dateend="endmonth">Dic</dd>
				</dl>

					<a class="view-all" href="<?php bloginfo('url'); ?>/events" itemprop="url"><?php _e('View More','tttevents'); ?></a><!-- To use this link must to create new page with the slug /events -->
			</aside>

			<script type="text/javascript">
			jQuery(document).ready(function($) {
				var data = {
					action: 'widget_events',
					datestart: '<?php echo date('Y-m-d',mktime(0,0,0,date('m'),1,date('Y'))); ?>',
					dateend: 'infinite'
				};

				$('#events h4.event-setdate').addClass('active');
				
				$('ul#dates').fadeOut('fast');

				
				$.get(ajax_object.ajax_url, data, function(response) {
					$('#events_list').html( response );
					$('ul#dates').fadeIn();
				});

				$('#events .event-setdate').css('cursor','pointer').click(function() {
					$('#events .event-setdate').removeClass('active');
					$('#events_list').html('loadding...');
					var d = data;
					d['datestart'] = $(this).attr('data-datestart');
					d['dateend'] = $(this).attr('data-dateend');
					$('#events .event-setdate[data-datestart='+d.datestart+']').addClass('active');
					$.get(ajax_object.ajax_url, d, function(response) {
						$('#events_list').html( response );
						$('ul#dates').fadeIn();
					});
				});
			});
			</script>
		<!--END #events-->					
		<?php
        }

}
register_widget( 'Events_widget' );




add_action('wp_enqueue_scripts','widget_events_init',11);

function widget_events_init() {
	wp_localize_script( 'app', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}

add_action('wp_ajax_widget_events', 'widget_events_callback');

function widget_events_callback() {
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$_REQUEST['datestart'])) return false;
	if (!preg_match('/^(endmonth|infinite)$/',$_REQUEST['dateend'])) return false;


	widget_events_query( $_REQUEST['datestart'], $_REQUEST['dateend'] );
	//global $wpdb; // this is how you get access to the database
	
	// $whatever = intval( $_POST['whatever'] );
	// 
	// $whatever += 10;
	// 
	// echo $whatever;
	
	die(); // this is required to return a proper result
}

function widget_events_query( $_start, $_end ) {

	$query = new TTTEvents(array(
		'_tttevent_between' => array( $_start, $_end ),
		'posts_per_page' => 5
	));?>


	<?php //var_dump($query); ?>

	<?php if ( $query->have_posts() ): ?>
	<ul id="dates" class="side-nav" style="display: none">
		<?php while ( $query->have_posts() ):  $query->the_post();?>
		<li>
			<div class="vevent row" itemprop="event" itemscope="" itemtype="http://schema.org/Event">
				<time class="hide" itemprop="startDate" datetime="<?php echo $query->tttevent_date_start('c');?>"><?php echo $query->tttevent_date_start('M d, h:iA');?></time>
				<time class="hide" itemprop="endDate" datetime="<?php echo $query->tttevent_date_end('c');?>"><?php echo $query->tttevent_date_end('M d, h:iA');?></time>
				<meta itemprop="startDate" content="<?php echo $query->tttevent_date_start('Y-m-d');?>">
				
				<div class="large-2 small-2 columns event-meta event-meta-day">
					<div class="day"><?php echo $query->tttevent_date_start('d');?></div>
					<div class="month"><?php echo $query->tttevent_date_start('m');?></div>
				</div>
				
				<div class="event-info">
					<div class="entry-thumbnail">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'View more %s', 'tttevents' ), the_title_attribute( 'echo=0' ) ) ); ?>"><div class="bweffect"><?php the_post_thumbnail(); ?></div></a>
					</div>
					<header class="event-header">
						<h3 class="event-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'View more %s', 'tttevents' ), the_title_attribute( 'echo=0' ) ) ); ?>" itemprop="url"><span itemprop="summary"><?php echo $query->tttevent_description(); ?></span></a></h3>
					</header>
					<ul class="meta-event inline-list">
						<li><span itemprop="location" class="<?php echo tttevent_event_type( $query->tttevent_meta('type') ); ?> event-icon"><?php echo $query->tttevent_meta('type'); ?></span></li>			
						<li><span itemprop="location" class="event-location icon-location"><?php echo $query->tttevent_meta('location'); ?></span></li>
					</ul>						
				</div>
			</div>
		</li>
		<?php endwhile; ?>
	</ul>
	
	<?php else: ?>
	
		<?php _e('No events','tttevents'); ?>

	<?php endif; ?>
	
	<?php

	unset( $query );
	wp_reset_query();
}


if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'widget_events') {
	add_action('wp_loaded','_widget_ajax_init');
	function _widget_ajax_init() {
		do_action('wp_ajax_'.$_REQUEST['action']);
	}
}
?>

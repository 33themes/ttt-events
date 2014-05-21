=== TTT Events ===
Contributors: 33themes, gabrielperezs, lonchbox, tomasog
Tags: event, dates, start date, end date, location, metadata, metabox
Requires at least: 3.7
Tested up to: 3.8.1
Stable tag: 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

Add an event details to your Posts, Pages or PostTypes.


== Description ==

Best way to use event information in your posts, ready to translate into multilanguage. Total template & design Freedom :)

= Features =

* How to use the TTTEvent loop. ie:
`
<?php	
	$args = array( //here you can use all WP_query parameters.
	'posts_per_page' => 30
	);
	
	if (isset($_REQUEST['startdate'])) {
	$args['_tttevent_between'] = array( $_REQUEST['startdate'], 'endmonth' );
	}
	
	$query = new TTTEvents( $args );
?>
<?php if ( $query->have_posts() ) : ?>
	<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( has_post_thumbnail() ): ?>
				<div class="entry-thumbnail">
					<?php the_post_thumbnail(); ?>
				</div>
			<?php endif; ?>
			<header class="entry-header">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'view more %s', 'your-theme-name' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>		
			</header><!-- .entry-header -->
			<ul>
				<li>
					<div class="vevent" itemprop="event" itemscope="" itemtype="http://schema.org/Event">
						<time itemprop="startDate" datetime="<?php echo $query->tttevent_date_start('c');?>"><?php echo $query->tttevent_date_start('M d, h:iA');?></time>
						<time itemprop="endDate" datetime="<?php echo $query->tttevent_date_end('c');?>"><?php echo $query->tttevent_date_end('M d, h:iA');?></time>
						<meta itemprop="startDate" content="<?php echo $query->tttevent_date_start('Y-m-d');?>">
						<meta itemprop="endDate" content="<?php echo $query->tttevent_date_end('Y-m-d');?>">
						<header class="event-header">
							<h3 class="event-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'view more %s', 'your-theme-name' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><span itemprop="summary"><?php echo $query->tttevent_description(); ?></span></a></h3>
						</header>
						<div class="event-meta start-date">
							<div class="day"><?php echo $query->tttevent_date_start('M, d');?></div> - <div class="time"><?php echo $query->tttevent_date_start('h:iA');?></div>
						</div>
						<div class="event-meta end-date">
							<div class="day"><?php echo $query->tttevent_date_end('M, d');?></div> - <div class="time"><?php echo $query->tttevent_date_end('h:iA');?></div>
						</div>
						<ul class="event-meta">
							<li><span itemprop="location"><?php echo $query->tttevent_meta('type'); //check Hacks Tab to know how to create new custom fields for the event details ?></span></li>
						</ul>										
					</div>
				</li>
			</ul>						
			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->
		</article><!-- #post-## -->
	<?php endwhile; ?>
<?php endif; ?>
`




= Hacks ==

* How to create extra event fields?
`
<?php
// TTT-event extra fields
add_filter('tttevent_meta_extra_vars', 'custom_tttevent_meta_extra_vars');
function custom_tttevent_meta_extra_vars() {
	return array(
		'type' => '',
		'location' => ''		
	);
}

add_filter('tttevent_meta_extra_template', 'custom_tttevent_meta_extra_template');
function custom_tttevent_meta_extra_template() {
	?>
	<br>
	<br>
	<div style="width: 100%">
		<label>Type</label>
		<select name="tttevent[<%=id%>][tipo_evento]">
			<!-- <option value="0">none</option> -->
			<option value="Concert" <% if (tipo_evento == "Concert") { %> selected <% } %>>Concert</option> <!-- ALl values have to be iqual Value, tipo_evento, option text -->
			<option value="Expo" <% if (tipo_evento == "Expo") { %> selected <% } %>>Expo</option>
			<option value="Party" <% if (tipo_evento == "Party") { %> selected <% } %>>Party</option>
			<option value="Movie" <% if (tipo_evento == "Movie") { %> selected <% } %>>Movie</option>
			<option value="Meeting" <% if (tipo_evento == "Meeting") { %> selected <% } %>>Meeting</option>
		</select>
	</div>
	<br>
	<div style="width: 100%">
		<label>Location</label>
		<input name="tttevent[<%=id%>][location]" value="<%=location%>"/>
	</div>
	<br>
	<?php
}

// TTT-event icons class
function tttevent_event_type($value) {
	if ( $value == 'Concerts' ) { return "event-type-concert icon-music"; }
	elseif ( $value == 'Expo' ) { return "event-type-espo icon-rocket"; }	
	elseif ( $value == 'Party' ) { return "event-type-party icon-glass"; }		
	elseif ( $value == 'Movie' ) { return "event-type-movie icon-film"; }		
	elseif ( $value == 'Meeting' ) { return "event-type-meeting icon-briefcase"; }
}
?>
`


* Remove TTT-Event from specific CPTs
`
<?php
//Remove TTT-Event from specific CPTs
add_filter('ttt-events_post_types', 'custom_remove_cpt_event' );
function custom_remove_cpt_event ( $post_types ) {
   unset($post_types['my_custom_post_type']);
   unset($post_types['my_other_custom_post_type']);
   return $post_types;
};
?>
`


== Recomendations ==



== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `ttt-crop` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
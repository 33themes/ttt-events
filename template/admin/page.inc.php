<div id="tttevents-page" class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>
	<h2><?php _e('Events', parent::sname ) ; ?></h2>

	<?php if (!isset($_POST['submit'])): ?>
	<form action="options-general.php?page=ttt-events-menu" method="post" class="">
		<h3><?php _e('Click to rebuild index for events', parent::sname );?></h3>
		<input type="submit" name="submit" class="button button-primary" value="<?php _e('Rebuild index', parent::sname ); ?>">
	</form>
	<?php else: ?>

	<h3><?php _e('Rebuild status', parent::sname ); ?>: <?php echo ( $this->rebuild() ? __('OK',parent::sname) : __('Error',parent::sname) ); ?></h3>
	<?php endif; ?>
	</br>
	</br>	
	<h3><?php _e('All events', parent::sname );?></h3>
	<ol>
		<?php
		$query = new TTTEvents(array(
		//'_tttevent_between' => array('startmonth','infinite'),
		'posts_per_page' => -1,
		'ignore_sticky_posts' => 1
		));

		?>

		<?php if ( $query->have_posts() ): ?>
			<?php  while ( $query->have_posts() ): $query->the_post(); ?>
				<li>
					<strong><?php _e('Post Title:', parent::sname );?></strong> <?php echo get_the_title(); ?>. <?php edit_post_link(); ?><br> 
					<strong><?php _e('Event Title:', parent::sname );?></strong> <?php echo $query->tttevent_description(); ?>. <br>
					<strong><?php _e('Start:', parent::sname );?></strong> <?php echo $query->tttevent_date_start('M, d');?> - <?php echo $query->tttevent_date_start('h:iA');?><br>
					<strong><?php _e('End:', parent::sname );?></strong> <?php echo $query->tttevent_date_end('M, d');?> - <?php echo $query->tttevent_date_start('h:iA');?><br>
					<strong><?php _e('Type:', parent::sname );?></strong> <?php echo $query->tttevent_meta('tipo_evento'); ?> - <strong><?php _e('Place:', parent::sname );?></strong> <?php echo $query->tttevent_meta('lugar'); ?>
				</li>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php wp_reset_query(); ?>
		<?php wp_reset_postdata(); ?>
	</ol>

</div>

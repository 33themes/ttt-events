<div class="tttevents-invoke-wrapper">
	<div class="tttevents-content">
	</div>
	<br/>
	<a class="button tttevents-invoke"><?php _e('Create event',parent::sname); ?></a>

</div>

<script type="text/html" id="tttevents-tmpl-metabox">
<div class="tttevents-metabox tttevents-event" data-eventid="<%=id%>" data-post="<?php the_ID(); ?>">
	<div class="content">
		<label><?php _e('Description or title', parent::sname); ?></label>
		<input name="tttevent[<%=id%>][description]" class="text" type="text" value="<%=description%>" />
		<div class="date start_at">
			<label><?php _e('Start at',parent::sname); ?></label>
			<input name="tttevent[<%=id%>][start_at]" class="text" type="text" value="<%=start_at%>" />
		</div>
		<div class="date end_at">
			<label><?php _e('End at',parent::sname); ?></label>
			<input name="tttevent[<%=id%>][end_at]" class="text" type="text" value="<%=end_at%>" />
		</div>
		<?php echo apply_filters('tttevent_meta_extra_template',true); ?>
		<a class="button tttevents-invoke-remove"><?php _e('Remove event',parent::sname); ?></a>
	</div>
</div>
</script>

<script type="text/javascript">

jQuery(document).ready(function($) {

	$('.tttevents-content').on('tttevents:addItem',function(event, args) {

		var template = $("#tttevents-tmpl-metabox").html();

		$(".tttevents-content").append(_.template( template , args ) );
		
		var elEvent = $("div.tttevents-event[data-eventid='"+args.id+"']",this);
		
		$( ".start_at input", elEvent ).datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "HH:mm",
			addSliderAccess: true,
			sliderAccessArgs: { touchonly: false },
			defaultDate: "+2d",
			changeMonth: true,
			numberOfMonths: 3,
			onClose: function( selectedDate ) {
				$( ".end_at input", elEvent ).datetimepicker( "option", "minDate", selectedDate );
			}
		});
		$( ".end_at input", elEvent ).datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "HH:mm",
			addSliderAccess: true,
			sliderAccessArgs: { touchonly: false },
			defaultDate: "+2d",
			changeMonth: true,
			numberOfMonths: 3,
			onClose: function( selectedDate ) {
				$( ".end_at input", elEvent ).datetimepicker( "option", "maxDate", selectedDate );
			}
		});
		
		$('a.tttevents-invoke-remove', elEvent).on('click',function() {
			$(elEvent).remove();
		});

		$(this).trigger('tttevents:addItem:after', [ elEvent, args ] );

		return true;

	});
	
	var template = $("#tttevents-tmpl-metabox").html();


	if ( ttteventsPost && ttteventsPost[0] ) {
		for ( var i in ttteventsPost ) {
			$(".tttevents-content").trigger('tttevents:addItem', ttteventsPost[i] );
		}
	}

	$('a.tttevents-invoke').on('click',function() {
		var n = Number( $(".tttevents-content > div:last").attr('data-eventid') )  + 1;
		if ( !n) n = 0;
		var _params = $.extend( ttteventsConf.defaults, {
			'id': n
		});
		console.log( _params );
		$('.tttevents-content').trigger('tttevents:addItem', _params );
	});

});
</script>

 <?php //if( empty($args['post_type']) ) { ?>
	<div class="exclude_container">
		<hr>
		<h3>
			Exclude
		</h3>
		<p class="description">
			You can exclude pages from the plugin functionality by checking them in the select box below. (Arranged alphabetically)<br>
			Use Shift or Ctrl buttons pressed while clicking, to select multiple values.
		</p>
		<?php 			
			WpobPlugin::draw_posts_select_box('multiple', /*$pt,*/ 'DO NOT EXCLUDE '.strtoupper($args['wpob-post-type-label']), $args['wpob-exclude-posts']); 
		?>
	</div>
	<?php //} ?>
<div class="col-1-<?php echo $grid; ?> mason-item" style="padding-right: <?php echo $img_padding; ?>">
	<div class="mega-post-carousel4 <?php echo $css_class; ?>" style="margin-bottom: 40px;">
		<div class="mega-post-image" style="height: <?php echo $height; ?>;">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail('large', array('style' => 'height:'.$height.';')); ?>
			</a>
		</div>

		<div class="mega-post-category" style="display: <?php echo $catg; ?>">
			<?php $categories = get_the_category();
				$separator = ' ';
				$output = '';
				if ( ! empty( $categories ) ) {
				    foreach( $categories as $category ) {
				        $output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>' . $separator;
				    }
				    echo trim( $output, $separator );
				} ?>
		</div>
		<span class="mega-post-date" style="color: <?php echo $dateclr; ?>;">
			<i class="fa fa-clock-o"></i>
			<?php echo get_the_date() ?>
		</span>
		<h3 class="mega-post-title">
			<a style="font-size: <?php echo $txtsize; ?>; color: <?php echo $txtclr ?>" href="<?php the_permalink(); ?>"><?php echo get_the_title() ?></a>
		</h3>
		<div class="clearfix"></div>
		<div class="mega-post-para">
			<p style="font-size: <?php echo $descsize; ?>px; color: <?php echo $descclr ?>">
				<?php $content = get_the_content();
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>',']]&gt;', $content);
				echo substr(strip_tags($content),0,$excerpt); ?> ...

			</p>
		</div>
		<span class="mega-post-meta" style="color: <?php echo $dateclr; ?>;">
			<i class="fa fa-user"></i>
			<span style="color: <?php echo $dateclr; ?>; padding-right: 15px;">
				<?php echo get_the_author(); ?>
			</span>
		</span>
		<span class="mega-comment-box" style="display: <?php echo $comment; ?>">
			<span class="mega-post-comment">
				<i class="fa fa-comment"></i> <a><?php comments_number( '0', '1', '%' ); ?> </a>
			</span>					
		</span>
		<div class="clearfix"></div>
	</div>
</div>
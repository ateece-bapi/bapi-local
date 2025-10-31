<div class="col-1-<?php echo $grid; ?> mason-item" style="padding-right: <?php echo $img_padding; ?>">
	<div class="mega-post-carousel2 <?php echo $css_class; ?>" style="margin-bottom: 40px;">
		<div class="mega-post-image" style="height: <?php echo $height; ?>;">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail('large', array('style' => 'height:'.$height.';')); ?>
			</a>
		</div>
		<div class="mega-post-content">
			<h3 class="mega-post-title">
				<a style="font-size: <?php echo $txtsize; ?>; color: <?php echo $txtclr ?>" href="<?php the_permalink(); ?>"><?php echo get_the_title() ?></a>
			</h3>
			<span class="mega-post-meta" style="color: <?php echo $dateclr; ?>;">
				<i class="fa fa-user"></i>
				<span style="color: <?php echo $dateclr; ?>; padding-right: 15px;">
					<?php echo get_the_author(); ?>
				</span>
			</span>
			<span class="mega-post-date" style="color: <?php echo $dateclr; ?>;">
				<i class="fa fa-clock-o"></i>
				<?php echo get_the_date() ?>
			</span>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
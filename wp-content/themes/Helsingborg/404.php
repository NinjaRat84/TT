<?php get_header(); ?>

<div class="row">
	<div class="404">
		<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<header>
				<h1 class="entry-title"><?php _e('Sidan kan inte hittas', 'Helsingborg'); ?></h1>
			</header>
			<div class="small-12 large-12 columns" role="main">
			<div class="entry-content">
				<div class="search-container row">
						<div class="search-inputs large-12 columns">
								<form action="/TT/sok" method="GET"> <!-- OBS! SÖKVÄGEN -->
									<input type="text" placeholder="Vad letar du efter?" name="q"/>
									<input type="submit" value="Sök">
								</form>
						</div>
				</div><!-- /.search-container -->
				<div class="error">
					<p class="bottom"><?php _e('Den sida du vill nå kan inte hittas. Du kan använda sökrutan här ovanför för att försöka hitta det du letar efter.', 'Helsingborg'); ?></p>
				</div>
				<p><?php _e('<h2>Det här kan du göra för att hitta det du söker:</h2>', 'Helsingborg'); ?></p>
				<ul>
					<li><?php _e('Kontrollera stavningen. Har du skrivit rätt adress?', 'Helsingborg'); ?></li>
					<li><?php _e('Använd sökrutan ovanför för att söka rätt på det du letar efter.', 'Helsingborg'); ?></li>
					<li><?php printf(__('Gå till <a href="%s">startsidan</a>', 'Helsingborg'), home_url()); ?></li>
					<li><?php _e('Använd <a href="javascript:history.back()">webbläsarens bakåt-knapp</a>', 'Helsingborg'); ?></li>
				</ul>
				<p><?php _e('<h2>Vill du komma i kontakt med Helsingborgs stad?</h2> Du kan ringa till Helsingborg kontaktcenter på telefonnummer 042-10 50 00 för att bli lotsad rätt.', 'Helsingborg'); ?></p>
			</div>
		</div>
		</article>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>

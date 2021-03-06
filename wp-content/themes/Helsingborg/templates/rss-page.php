<?php
/*
Template Name: RSS
*/

$numposts = 5;
$lastpost = $numposts - 1;
$meta = get_post_meta($post->ID,'_helsingborg_meta',TRUE);
$selected_node = $meta['rss_select'];
$get_all_children = !empty($meta['rss_check']) ? -1 : $selected_node;

$args = array(
	'sort_order' => 'DESC',
	'sort_column' => 'post_modified',
	'child_of' => $selected_node,
	'parent' => $get_all_children,
	'post_type' => 'page',
	'post_status' => 'publish'
);

$pages = get_pages($args);
$numberOfPages = count($pages);
$lastpost = $numberOfPages - 1;

function helsingborg_rss_date( $timestamp = null ) {
  $timestamp = ($timestamp==null) ? time() : $timestamp;
  echo date(DATE_RSS, $timestamp);
}

function helsingborg_rss_text_limit($string, $length, $replacer = '...') {
  $string = strip_tags($string);
  if(strlen($string) > $length)
    return (preg_match('/^(.*)\W.*$/', substr($string, 0, $length+1), $matches) ? $matches[1] : substr($string, 0, $length)) . $replacer;
  return $string;
}

header("Content-Type: application/rss+xml; charset=UTF-8");
echo '<?xml version="1.0"?>';
?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">
<channel>
  <title>Titel</title>
  <link>http://localhost/</link>
  <description>Description</description>
  <pubDate><?php helsingborg_rss_date( strtotime($ps[$lastpost]->post_modified_gmt) ); ?></pubDate>
  <lastBuildDate><?php helsingborg_rss_date( strtotime($ps[$lastpost]->post_modified_gmt) ); ?></lastBuildDate>
<?php foreach ($pages as $post) { ?>
  <item>
    <title><?php echo get_the_title($post->ID); ?></title>
    <link><?php echo get_permalink($post->ID); ?></link>
    <description><?php echo '<![CDATA['.helsingborg_rss_text_limit($post->post_content, 50).'<br/><br/>Läs mer: <a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a>'.']]>';  ?></description>
    <pubDate><?php helsingborg_rss_date( strtotime($post->post_modified_gmt) ); ?></pubDate>
    <guid><?php echo get_permalink($post->ID); ?></guid>
  </item>
<?php } ?>
</channel>
</rss>

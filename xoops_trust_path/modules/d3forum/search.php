<?php

eval( '

function '.$mydirname.'_global_search( $keywords , $andor , $limit , $offset , $userid )
{
	return d3forum_global_search_base( "'.$mydirname.'" , $keywords , $andor , $limit , $offset , $userid ) ;
}

' ) ;


if( ! function_exists( 'd3forum_global_search_base' ) ) {

function d3forum_global_search_base( $mydirname , $keywords , $andor , $limit , $offset , $userid )
{
	$myts =& MyTextsanitizer::getInstance() ;
	$db =& Database::getInstance() ;

	$andor = strtoupper( $andor ) ;
	$userid = intval( $userid ) ;

	// XOOPS Search module
	$showcontext = empty( $_GET['showcontext'] ) ? 0 : 1 ;
	$select4con = $showcontext ? "p.post_text" : "'' AS post_text" ;

	require_once dirname(__FILE__).'/include/common_functions.php' ;
	$whr_forum = "t.forum_id IN (".implode(",",d3forum_get_forums_can_read( $mydirname )).")" ;

	$whr_uid = $userid > 0 ? "p.uid=$userid" : "1" ;

	$whr_query = $andor == 'OR' ? '0' : '1' ;
	if( is_array( $keywords ) ) foreach( $keywords as $word ) {
		// I know this is not a right escaping, but I can't believe $keywords :-)
		$word4sql = addslashes( stripslashes( $word ) ) ;
		$whr_query .= $andor == 'EXACT' ? ' AND' : ' '.$andor ;
		$whr_query .= " (p.subject LIKE '%$word4sql%' OR p.post_text LIKE '%$word4sql%')" ;
	}

	$sql = "SELECT p.post_id,p.topic_id,p.post_time,p.uid,p.subject,p.html,p.smiley,p.xcode,p.br,$select4con FROM ".$db->prefix($mydirname."_posts")." p LEFT JOIN ".$db->prefix($mydirname."_topics")." t ON t.topic_id=p.topic_id WHERE ($whr_forum) AND ($whr_uid) AND ($whr_query) AND ! topic_invisible ORDER BY p.post_time DESC" ;

	$result = $db->query( $sql , $limit , $offset ) ;
	$ret = array() ;
	$context = '' ;
 	while( list( $post_id , $topic_id , $post_time , $uid , $subject , $html , $smiley , $xcode , $br , $text ) = $db->fetchRow( $result ) ) {

		// get context for module "search"
		if( function_exists( 'search_make_context' ) && $showcontext ) {
			if( function_exists( 'easiestml' ) ) $text = easiestml( $text ) ;
			$full_context = strip_tags( $myts->displayTarea( $text , $html , $smiley , $xcode , 1 , $br ) ) ;
			$context = search_make_context( $full_context , $keywords ) ;
		}

		$ret[] = array(
			'link' => "index.php?post_id=$post_id" ,
			'title' => $subject ,
			'time' => $post_time ,
			'uid' => $uid ,
			"context" => $context ,
		) ;
	}
	return $ret;

}

}


?>
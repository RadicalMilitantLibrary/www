<?php
// ============================================================================
//  Forum for Radical Militant Library
//  Copyright (C) Jotunbane 
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// ============================================================================

function RMLdisplayforum($print_on = true)
{
	$karma = RMLgetkarma(RMLgetcurrentuser());
	$out = '';
	
	global $id;
	
	if($id > 0) { 
		RMLforumread($id);
	} else {
		if($karma > 1) {
			$out .= '<a class="button" href="./?forum=add">New Forum</a>';
		}

		$result = RMLfiresql("SELECT author,body,posted_on,id FROM forum WHERE sticky_id <> 0 ORDER BY posted_on");
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thisbody = $thisrow->body;
			$thisauthor = $thisrow->author;
			$thisposted = RMLfixdate($thisrow->posted_on);
			$out .= '<div class="forumsticky"><a href="./?forum=view&id='.$thisid.'">'.$thisbody.'</a></div>';
			$out .= '<div style="float:right;margin-top:-1.4em;font-size:small">by : '.$thisauthor.' ('.$thisposted.')</div>';
			$out .= '<hr class="forumseperator" />';
		}
	
		$result = RMLfiresql("SELECT author,body,posted_on,id FROM forum WHERE misc_id <> 0 ORDER BY posted_on DESC");
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thisbody = $thisrow->body;
			$thisauthor = $thisrow->author;
			$thisposted = RMLfixdate($thisrow->posted_on);
			$out .= '<div class="forummisc"><a href="./?forum=view&id='.$thisid.'">'.$thisbody.'</a></div>';
			$out .= '<div style="float:right;margin-top:-1.4em;font-size:small">by : '.$thisauthor.' ('.$thisposted.')</div>';
			$out .= '<hr class="forumseperator" />';
		}
	
		$out .= '<div class="boxheader"><b>The Books</b></div>';
		$result = RMLfiresql("SELECT author,body,posted_on,id,book_id FROM forum WHERE book_id <> 0 ORDER BY posted_on DESC");
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thisbook = $thisrow->book_id;
			$thisbody = $thisrow->body;
			$thisauthor = $thisrow->author;
			$thisposted = RMLfixdate($thisrow->posted_on);
			$out .= '<a href="./?forum=view&id='.$thisid.'"><img src="./covers/cover'.$thisbook.'" /></a>';
			if($row < pg_numrows($result) - 1) {
				$out .= '<hr class="forumseperator" />';
			}
		}	
		
		$out .= '<div class="boxheader"><b>The Authors</b></div>';
		$result = RMLfiresql("SELECT author,body,posted_on,id,author_id FROM forum WHERE author_id <> 0 ORDER BY posted_on DESC");
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thisauthorid = $thisrow->author_id;
			$thisbody = $thisrow->body;
			$thisauthor = $thisrow->author;
			$thisposted = RMLfixdate($thisrow->posted_on);
			$out .= '<a href="./?forum=view&id='.$thisid.'"><img style="width:150px" src="./authors/author'.$thisauthorid.'" /></a>';
			if($row < pg_numrows($result) - 1) {
				$out .= '<hr class="forumseperator" />';
			}
		}
	}
	
	return processOutput( $out, $print_on);	
}

// ============================================================================

function RMLgetforumtitle($forumid) {
	$result = RMLfiresql("SELECT book_id,author_id,misc_id,sticky_id,body FROM forum WHERE id=$forumid AND parent_id=0");
	$thisrow = pg_Fetch_Object($result,0);
	$thisbook = $thisrow->book_id;
	$thisauthor = $thisrow->author_id;
	$thismisc = $thisrow->misc_id;
	$thissticky = $thisrow->sticky_id;
	$thisbody = $thisrow->body;
	
	if( $thisbook > 0 ) {
		$result = RMLgetdocumenttitle($thisbook);
	}
	
	if( $thisauthor > 0 ) {
		$result = RMLgetauthorname($thisauthor);
	}
	
	if( $thismisc > 0 ) {
		$result = $thisbody;
	}
	
	if( $thissticky > 0 ) {
		$result = $thisbody;
	}
	
	return $result;
}

// ============================================================================

function RMLforumread($forumid) {
// thinking...	
	
}

// ============================================================================

function RMLforumpost( $print_on = true ) {
	global $id;
	
	if( RMLgetcurrentuser() == null ) {
		//return false;
		$author = "Anonymous";
	} else {
		$author = RMLgetcurrentuser();
	}

	$score_options='';
	for ( $i = 0; $i <= 10; $i++ ) {
		$score_options .= "\n".'<option value="'.$i.'">'.$i.'</option>';
	}

	$out = "\n".'<form method="post" action="?comment=save&amp;id=' .$id .'">
<input type="hidden" name="author" value="$author">
<input type="hidden" name="id" value="' .$id .'">
<table class="form">
<tr><td valign="top">Comment : </td><td><textarea class="norm" rows="10" cols="41" wrap="none" name="body"></textarea></td></tr>
<tr><td>Score : </td><td><select class="norm" name="score">' .$score_options .'</select></td></tr>
<tr><td></td><td><input type="submit" value="Post comment"></td></tr></table>
<input type="hidden" name="message" value="save"></form>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLsaveforum( $id, $print_on = true ) {
	global $parent, $subject, $body, $score;

	if( RMLgetcurrentuser() == null ) {
		$out = "ERROR: Action not permitted.";
		//$author = "Anonymous";
	} elseif( strlen($body) < 50 ) {
		$out = "ERROR: Comment too short...";
	} else {
		$author = RMLgetcurrentuser();
		$parent_id = intval($parent);

		if( $parent_id ) {
			$result = RMLfiresql("SELECT thread_id,level,thread_pos FROM forum WHERE id=$parent_id");
			$temp = pg_Fetch_Object( $result, 0 );
			$level = $temp->level + 1;
			$thread_id = $temp->thread_id;
			$parentthread_pos = $temp->thread_pos;

			$thread_pos = $parentthread_pos + 1;

			RMLfiresql("UPDATE forum SET thread_pos = thread_pos + 1 WHERE thread_id = $thread_id AND thread_pos >= $thread_pos");
		}

		$docname = RMLgetdocumenttitle( $id );

		RMLfiresql("INSERT INTO forum (id,thread_id,parent_id,posted_on,level,author,body) VALUES(DEFAULT,$id,$parent_id,NOW(),$score,'$author','$body')");

	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaycomments( $id, $print_on = true )
{
	$out = "\n".'<div class="box"><div class="boxheader"><b>Comments</b></div>';

	$sql = RMLfiresql("SELECT author,body,posted_on,level FROM forum WHERE thread_id=$id ORDER BY posted_on");

	if(pg_numrows($sql) == 0) {
		$out .= "\n".'<div class="boxtext">No comments on this book.</div>';
	}

	$numrows = pg_numrows($sql)-1;

	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisauthor = $thisrow->author;
		$thisauthorID = RMLgetuserID( $thisauthor );
		$thisbody = $thisrow->body;
		$thisposted = $thisrow->posted_on;
		$thisposted = RMLfixdate($thisposted);
		$thislevel = $thisrow->level;
		$thisbody = nl2br($thisbody);

		if ( !file_exists( './users/' .$thisauthorID .'.png' ) ) {
			$image = 'Anonymous';
		} else {
			$image = $thisauthorID;
		}

		$separator = ($row < $numrows) ? '<div class="inlineclear"><hr class="forumseperator" /></div>' : '';

		$out .= "\n".'<div class="boxtext">
<span class="right-float">' .getRatingDisplay( $thislevel ) .'</span>
<img style="float : left;padding-right : 10px;padding-bottom : 5px" src="./users/'.$image.'.png" />
<small>from : <b>'.$thisauthor.'</b> (<i>'.$thisposted.'</i>)</small></div>
<div class="boxtext"><small>' .$thisbody .'</small></div>' .$separator ;
	}
	//todo: hasRights( 'addcomment' )
	if( RMLgetcurrentuser() != null ) {
		$out .= "\n".'<div class="inlineclear"></div>
<p class="boxtext"><a class="button add" href="?comment=new&amp;id='.$id.'">Add Comment</a></p>';
	}
	$out .= "\n</div>";

	return processOutput( $out, $print_on );
}


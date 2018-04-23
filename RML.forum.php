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
	$out = '';
	
	$out .= '<div class="boxheader"><b>... about The Library</b></div>';
	
	$out .= '<div class="boxheader"><b>... about The Books</b></div>';
	
	$out .= '<div class="boxheader"><b>... about The Authors</b></div>';
	
	$out .= '<div class="boxheader"><b>... about Shit</b></div>';


	return processOutput( $out, $print_on);	
}

// ============================================================================

function RMLforumread() {
	global $id, $order;
	
	$result = RMLfiresql("SELECT thread_id,body,posted_on,level,author,parent_id FROM forum WHERE id=$id");
	$numres = pg_numrows($result);
	if($numres == 0) {
		die("Forum Read : Bad id...");
	}

	$thismessage = pg_Fetch_Object($result,0);
	$threadid = $thismessage->thread_id;
	$headlevel = $thismessage->level;
	$author = $thismessage->author;
	$date = $thismessage->posted_on;
	$parent = $thismessage->parent_id;
	$body = $thismessage->body;
	$body = nl2br($thismessage->body);
	
	$date = RMLfixdate($date);
	print("\n<div class=\"order\"><small>by : <b>$author</b> <i>$date</i></small></div>");
	RMLdisplay("$body",5);

	if(RMLgetcurrentuser()) {
		print("\n<div class=\"functions\">");

		if(RMLgetcurrentuser() == $author) {
			print("\n<a href=\"?forum=edit&amp;id=$id&amp;parent=$parent&amp;order=$order\">");
			print("\n<img class=\"button\" alt=\"Edit comment\" src=\"./img/edit.png\"/></a>");
			if($parent == 0) {
				print("\n<a href=\"?forum=delete&amp;id=$id&amp;parent=$parent&amp;order=$order\">");
                print("\n<img float=\"right\" class=\"button\" alt=\"Delete comment\" src=\"./img/delete.png\"/></a>");
			}
		}
		print("\n<a href=\"?forum=post&amp;parent=$id&amp;order=$order\">");
        print("\n<img class=\"button\" alt=\"Add comment\" src=\"./img/add.png\"/></a>");
	}
	print("\n</div>");

	$result = RMLfiresql("SELECT id,body,subject,author,posted_on FROM forum WHERE thread_id=$threadid AND parent_id=$id ORDER BY posted_on DESC");
	
	$numres = pg_numrows($result);
	$count = 0;

	while($count < $numres) {
        print ("\n<hr class=\"forumseperator\">");
		$thismessage = pg_Fetch_Object($result,$count);
		$body = nl2br($thismessage->body);
		$thisid = $thismessage->id;

		setTimeZone();
		$date = $thismessage->posted_on;
		$date = strtotime($date);
		$date = strftime('%d %b %Y %H:%M',$date);
		$author = $thismessage->author;

		$children = RMLfiresql("SELECT id FROM forum WHERE parent_id=$thisid");
		$children = pg_numrows($children);

		if($children > 0) {
			if($children > 1) {
				RMLdisplay("<b>$thismessage->subject</b> (<a href=\"?forum=read&amp;id=$thisid&amp;parent=$id&amp;order=$order\">$children replys</a>)",5);
			} else {
				RMLdisplay("<b>$thismessage->subject</b> (<a href=\"?forum=read&amp;id=$thisid&amp;parent=$id&amp;order=$order\">$children reply</a>)",5);
			}
		} else {
			RMLdisplay("<b>$thismessage->subject</b>",5);
		}
		print("<div class=\"order\"><small>by: <b>$thismessage->author</b> <i>$date</i></small></div>");
		RMLdisplay("<br>$body",8);

		if(RMLgetcurrentuser()) {
			if(RMLgetcurrentuser() == $author) {
				print("\n<a href=\"?forum=edit&amp;id=$thisid&amp;parent=$id&amp;order=$order\">");
	            print("\n<img class=\"button\" alt=\"Edit\" src=\"./img/edit.png\"/></a>");
				if($children == 0) {
					print("\n<a href=\"?forum=delete&amp;id=$thisid&amp;parent=$id&amp;order=$order\">");
                    print("\n<img float=\"right\" class=\"button\" src=\"./img/delete.png\"/></a>");
				}
			}
			print("\n<a href=\"?forum=post&amp;parent=$thismessage->id&amp;order=$order\">");
            print("\n<img class=\"button\" alt=\"Add\" src=\"./img/add.png\"/></a>");
		}
		$count++;
	}
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


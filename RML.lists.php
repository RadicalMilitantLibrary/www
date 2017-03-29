<?php
// ============================================================================
//  Reading Lists for Radical Militant Library
//  Copyright (C) 2016 Jotunbane 
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

function RMLdisplaylists($print_on = true) {
	global $id;
	
	$user = RMLgetcurrentuser();

	if($id == 0) {
		$result = RMLfiresql("SELECT id,name,owner,visible, (SELECT COUNT(doc_id) FROM listitems WHERE (list_id = id AND listitems.doc_id IN (SELECT id FROM document WHERE status > 2))) AS count FROM lists ORDER BY name");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisid = $thisrow->id;
			$thisname = $thisrow->name;
			$thisowner = $thisrow->owner;
			$thisvisible = $thisrow->visible;
			$thiscount = $thisrow->count;
			
			if($thisvisible == 't') { //implemented this way so an option to show all lists can be added
				$out .= "\n<div class=\"box\"><div class=\"boxheader\"><table width=\"100%\"><tr><td><a href=\"?lists=view&amp;id=$thisid\"><b>$thisname</b></a></td><td align=\"right\"><b>$thiscount</b> books</td></tr></table></div></div>";
			}
		}
		if($user) {
			$out .= '<a class="button star" href="?lists=create">New List</a>';
		}
	} else {
		$result = RMLfiresql("SELECT description,owner,visible FROM lists WHERE id=$id");
		$thisrow = pg_Fetch_Object($result,0);
		$thisdescription = nl2br($thisrow->description);
		$thisowner = $thisrow->owner;
		$thisvisible = $thisrow->visible;
		
		if($thisowner == $user || $user == "admin" || $user == "Shadilay") {
			$out .= "<table width=\"100%\"><tr><td><form method=\"post\" action=\"?lists=add&id=$id\">
  Document ID:
  <input type=\"number\" name=\"docid\" min=\"1\">
  <input type=\"submit\" value=\"Add to list\">
</form></td>"."<td align=\"right\"><form method=\"post\" action=\"?lists=visible&id=$id\">
				Visible ($thisvisible) - <input type=\"submit\" class=\"button\" value=\"Toggle visibility\">
			</form></td></tr></table>";
		}
		
		$out .= RMLdisplay($thisdescription,5,false);
		$out .= "<div class=\"inlineclear\"> &nbsp; </div>";
		
		$result = RMLfiresql("SELECT list_id,doc_id,(SELECT AVG(level) FROM forum WHERE thread_id=listitems.doc_id AND level > 0) AS score,(SELECT title FROM document WHERE id=listitems.doc_id) AS title,(SELECT status FROM document WHERE id=listitems.doc_id) AS status,(SELECT name FROM author where id=(SELECT author_id FROM document WHERE id=listitems.doc_id)) as authorname,(SELECT year FROM document WHERE id=listitems.doc_id) as year,(SELECT teaser FROM document WHERE id=listitems.doc_id) as description FROM listitems WHERE list_id=$id ORDER BY year");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisid = $thisrow->doc_id;
			$thisname = $thisrow->title;
			$thisauthor = $thisrow->authorname;
			$thisyear = $thisrow->year;
			$thisscore = $thisrow->score;
			$thisstatus = $thisrow->status;
			$thisdescription = $thisrow->description;

			if( strlen( $thisdescription ) > 400) {
				$thisdescription = substr( $thisdescription, 0, 400 ) .' ...';
				$thisdescription = strip_tags( $thisdescription );
			}
		
			$out .= "\n<div class=\"box\">";
			if($thisstatus<3) {
				$thisdescription = 'Not yet published.';
				$out .= "<div class=\"boxheader wrench\" style=\"color: #ff0000\"> ";
			} else {
				$out .= "<div class=\"boxheader\">";
			}
			
			$out .= "<a href=\"?document=view&amp;id=$thisid\">";
			if($thisstatus>2){
				$out .= "<img class=\"Cover\" alt=\"Cover\" src=\"./covers/cover$thisid\"/>";
			}
			else {
				//$out .= "<img class=\"Cover\" alt=\"Cover\" src=\"./covers/cover$thisid\"/>";
			}
			$out .= "<b>$thisname</b></a></div>
			<div class=\"boxtext\"><small>by <b>$thisauthor</b>, $thisyear</small>"
			.'<span class="right-float">' .getRatingDisplay( $avgscore ) .'</span></div>'
			.'<p class="boxtext">'.$thisdescription.'</p>'
			."<div class=\"inlineclear\"></div></div>";
		}
	}
	
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLaddlist($print_on = true) {
$out = "\n<table><form enctype=\"multipart/form-data\" method=\"post\" action=\"?lists=new\">
<tr><td align=right>Name </td><td><input class=norm type=\"text\" name=\"headline\"></td></tr>
<tr><td align=right valign=top>Description </td><td><textarea class=norm rows=\"12\" name=\"bodytext\"></textarea></td></tr>
<tr><td></td><td><input type=\"submit\" value=\"Hit It\"></td></form></table>";
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLnewlist($print_on = true) {
	global $bodytext, $headline;
	$thisuser = RMLgetcurrentuser();
	if(($thisuser) && ($headline)) {
		RMLfiresql( "INSERT INTO lists (id,owner,name,description) VALUES (DEFAULT,'$thisuser','$headline','$bodytext')" );
	}
}

// ============================================================================
function RMLgetlistname($id) {
	$result = RMLfiresql("SELECT name FROM lists WHERE id=$id");
	$thisrow = pg_Fetch_Object($result,0);
	return $thisrow->name;

}

// ============================================================================

function RMLaddtolist($listid , $print_on = true) {
	global $docid;
	$thisuser = RMLgetcurrentuser();
	$result = RMLfiresql("SELECT owner FROM lists WHERE id=$listid");
	$thisrow = pg_Fetch_Object($result,0);
	$thisowner = $thisrow->owner;
	
	if($thisuser == $thisowner || $thisuser == "admin" || $thisuser == "Shadilay") {
		RMLfiresql("INSERT into listitems (list_id,doc_id) VALUES($listid,$docid)");
	}
}
function RMLtogglelistvisibility($listid) {
	$thisuser = RMLgetcurrentuser();
	$result = RMLfiresql("SELECT owner, visible FROM lists WHERE id=$listid");
	$thisrow = pg_Fetch_Object($result,0);
	$thisowner = $thisrow->owner;
	$thisvisibility = $thisrow->visible;
	$visibility = "TRUE";
	if($thisvisibility=='t'){
		$visibility = "FALSE";
	}
	
	if($thisuser == $thisowner || $thisuser == "admin" || $thisuser == "Shadilay") {
		$result = RMLfiresql("UPDATE lists SET visible = $visibility WHERE id = $listid;");
	}
}

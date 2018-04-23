<?php
// ============================================================================
//  Reading Lists for Radical Militant Library
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

function RMLdisplaylists($print_on = true) {
	global $id;
	
	$user = RMLgetcurrentuser();

	if($id == 0) {
		$result = RMLfiresql("SELECT id,name,owner FROM lists ORDER BY name");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisid = $thisrow->id;
			$thisname = $thisrow->name;
			$thisowner = $thisrow->owner;
		
			$out .= "\n<div class=\"box\"><div class=\"boxheader\"><a href=\"?lists=view&amp;id=$thisid\"><b>$thisname</b></a></div></div>";
		}
		if($user) {
			$out .= '<a class="button star" href="?lists=create">New List</a>';
		}
	} else {
		$result = RMLfiresql("SELECT description,owner FROM lists WHERE id=$id");
		$thisrow = pg_Fetch_Object($result,0);
		$thisdescription = nl2br($thisrow->description);
		$thisowner = $thisrow->owner;
		
		if($thisowner == $user) {
			$out .= "<form method=\"post\" action=\"?lists=add&id=$id\">
  Document ID:
  <input type=\"number\" name=\"docid\" min=\"1\">
  <input type=\"submit\" value=\"Add to list\">
</form>";
		}
		
		$out .= RMLdisplay($thisdescription,5,false);
		$out .= "<div class=\"inlineclear\"> &nbsp; </div>";
		
		$result = RMLfiresql("SELECT list_id,doc_id,(SELECT title FROM document WHERE id=listitems.doc_id) AS title,(SELECT name FROM author where id=(SELECT author_id FROM document WHERE id=listitems.doc_id)) as authorname,(SELECT year FROM document WHERE id=listitems.doc_id) as year,(SELECT teaser FROM document WHERE id=listitems.doc_id) as description FROM listitems WHERE list_id=$id ORDER BY year");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisid = $thisrow->doc_id;
			$thisname = $thisrow->title;
			$thisauthor = $thisrow->authorname;
			$thisyear = $thisrow->year;
			$thisdescription = $thisrow->description;
		
			$out .= "\n<div class=\"box\"><div class=\"boxheader\"><a href=\"?document=view&amp;id=$thisid\"><img class=\"Cover\" alt=\"Cover\" src=\"./covers/cover$thisid\"/><b>$thisname</b></a></div><div class=\"boxtext\"><small>by <b>$thisauthor</b>, $thisyear</small></div><div class=\"inlineclear\"></div></div>";
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
	
	if($thisuser == $thisowner) {
		RMLfiresql("INSERT into listitems (list_id,doc_id) VALUES($listid,$docid)");
	}
}

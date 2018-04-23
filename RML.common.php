<?php

// ============================================================================
//  Common functions for Radical Militant Library
//  Copyright (C) 2009-2018 Jotunbane
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

function RMLdisplayhead( $print_on = true ) {
	$pagetitle = RMLgetpagetitle();
	$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><title>'.$pagetitle.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="Scimus quae legis, et non dicimus" />
<link rel="stylesheet" type="text/css" href="./style/default.css"/>
</head>
<body>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaytop( $print_on = true )
{
	$out = "\n\n".'<!-- TOP START -->'
	.RMLdisplaymenu( false )
	.RMLdisplaylocation( false )
	.'<div class="inlineclear"></div>'
	.RMLdisplaytitle( false )
	."\n";
	return processOutput( $out, $print_on );
}

// ============================================================================

// assemble button for navigation
function RMLMenuButton( $btntile, $href = "", $class = "", $decoration = 'star' ) {
	return  "\n<a class=\"$class $decoration\" href=\"$href\">$btntitle</a>";
}

// ============================================================================

function RMLdisplaymenu( $print_on = true )
{
	global $author, $subject, $news, $document, $function, $message, $style, $lists, $forum;
	
	$currentuser = RMLgetcurrentuser();

	$out = "\n\n".'<!-- MENU START --><div class="menu"><a href="."><img class="logo" alt="Logo" src="./img/logo.png" /></a><a class="button home" href=".">Home</a>
';

	if($function == 'about') {
		$out .= "\n<a class=\"activebutton\" href=\"?function=about\">About</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?function=about\">About</a>";
	}

	if($news) {
		$out .= "\n<a class=\"activebutton\" href=\"?news=view\">News</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?news=view\">News</a>";
	}

	if(($author == 'view') || ($document == 'view')) {
		$out .= "\n<a class=\"activebutton\" href=\"?author=view&amp;letter=A\">Authors</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?author=view&amp;letter=A\">Authors</a>";
	}

	if($subject == 'view') {
		$out .= "\n<a class=\"activebutton\" href=\"?subject=view&amp;letter=All&amp;id=0\">Subjects</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?subject=view&amp;letter=All&amp;id=0\">Subjects</a>";
	}

	if(($lists == 'view') || ($lists == 'add')){
		$out .= "\n<a class=\"activebutton\" href=\"?lists=view&ampid=0\">Lists</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?lists=view&amp;id=0\">Lists</a>";
	}

	if($function == 'manual') {
		$out .= "\n<a class=\"activebutton\" href=\"?function=manual\">Manual</a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?function=manual\">Manual</a>";
	}

	$out .= "\n<a class=\"button\" href=\"?function=rss\">RSS</a>";

	if( ( !$currentuser ) && ( $function <> 'login' ) ) {
		$out .= "\n<a class=\"button\" href=\"?function=login\">Login</a>";
	}
	if( ( !$currentuser ) && ( $function == 'login' ) ) {
		$out .= "\n<a class=\"activebutton\" href=\"?function=login\">Login</a>";
	}

	if(($currentuser) && ($forum == 'view') ) {
		$out .= "\n<a class=\"activebutton\" href=\"?forum=view\">Forum</a>";
	}
	
	if($currentuser && !$forum) {
		$out .= "\n<a class=\"button\" href=\"?forum=view\">Forum</a>";
	}

	$karma = RMLgetkarma($currentuser);
	$karma = $karma . '(' . RMLgetrating($karma) . ')';
	
	if(($currentuser) && ($function <> 'user') && ($message <> 'new') && ($style <> 'new') && ($document <> 'new')) {
		$out .= "\n<a class=\"button\" href=\"?function=user\">$karma</a>";
	}
	if(($function == 'user') || ($message == 'new') || ($style == 'new') || ($document == 'new')) {
		$out .= "\n<a class=\"activebutton\" href=\"?function=user\">$karma</a>";
	}

	$out .= "\n</div>";

	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaymain( $id, $print_on = true ) {
	global $function, $subject, $static, $message, $document,
		$author, $section, $comment, $news, $footnote, $note, $style, $lists, $forum;

	$out = "\n\n".'<!-- MAIN START -->'."\n\n".'<div class="main">';

	$frontpage = true; // HACK

	// ======================================
	switch( $function ) {
	case 'login':
		$out .= RMLdisplaysignup( false );
		$frontpage = false;
	break;
	case 'user':
		$out .= RMLdisplayuserpage( false );
		$frontpage = false;
	break;
	case 'upload':
		$out .= RMLdisplaydocumentupload( $id, false );
		$frontpage = false;
	break;
	case 'import':
		$out .= RMLimportdocument( $id, false );
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditelement( $id, false );
		$frontpage = false;
	break;
	case 'about':
		$out .= RMLdisplayabout( false );
		$frontpage = false;
	break;
	case 'manual':
		$out .= RMLdisplaymanual( false );
		$frontpage = false;
	break;
	}

	// ======================================
	switch( $subject ) {
	case 'view':
		$out .= RMLdisplaysubject( $id, false );
		$frontpage = false;
	break;
	case 'add':
		$out .= RMLaddsubject( false );
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditsubject( $id, false );
		$frontpage = false;
	break;
	}
	
	// ======================================
	
	switch($lists) {
	case 'view':
		$out .= RMLdisplaylists( false );
		$frontpage = false;
	break;
	case 'create':
		$out .= RMLaddlist(false);
		$frontpage = false;
	break;
	}

	// ======================================
	switch( $forum ) {
	case 'view':
		$out .= RMLdisplayforum( false );
		$frontpage = false;
	break;
	}

	// ======================================
	switch( $news ) {
	case 'view':
		$out .= RMLdisplaynews( false );
		$frontpage = false;
	break;
	case 'add':
		$out .= RMLaddnews( false );
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditnews( $id, false );
		$frontpage = false;
	break;
	}

	// ======================================
	switch( $message ) {
	case 'view':
		$out .= RMLdisplaymessage( $id, false );
		$frontpage = false;
	break;
	case 'new':
		$out .= RMLdisplaynewmessage( false );
		$frontpage = false;
	break;
	case 'reply':
		$out .= RMLreplymessage( $id, false );
		$frontpage = false;
	break;
	}

	// ======================================
	switch( $document ) {
	case 'new':
		$out .= RMLnewdocument( false );
		$frontpage = false;
	break;
	case 'view':
		if( $section ) {
			$out .= RMLreaddocument( $id, $section, false );
		} else {
			$out .= RMLviewdocument( $id, false );
		}
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditdocument( $id, false );
		$frontpage = false;
	break;
	case 'verify':
		$out .= RMLdisplayreview(false);
		$frontpage = false;
	break;

	}

	// ======================================
	switch( $author ) {
	case 'view':
		$out .= RMLdisplayauthor( $id, false );
		$frontpage = false;
	break;
	case 'new':
		$out .= RMLnewauthor( false );
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditauthor( $id, false );
		$frontpage = false;
	break;
	}

	// ======================================

	switch ( $comment ) {
	case 'new':
		$out .= RMLforumpost( false );
		$frontpage = false;
	break;
	}

	// ======================================

	switch( $footnote ) {
	case 'view':
		$out .= RMLdisplayfootnote( $id, $note, false );
		$frontpage = false;
	break;
	}

	// ======================================

	switch($style) {
	case 'new':
		$out .= RMLnewstylesheet( false );
		$frontpage = false;
	break;
	case 'edit':
		$out .= RMLeditstylesheet( $id, false );
		$frontpage = false;
	break;
	}

	// ======================================

	if($frontpage) {
		$out .= RMLdisplayfrontpage( false );
	}

	$out .= "\n</div>";
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayreview($print_on = true) {
	global $id, $section, $sequence;
	
	$karma = RMLgetkarma(RMLgetcurrentuser());
	if($karma < 50) die("Karma Police");
	
	$out = '';
	$table = RMLgetactivetable($id);
		
	$before = RMLfiresql("SELECT body,paragraphtype FROM $table WHERE doc_id=$id AND id=$sequence");
	$thisrow = pg_Fetch_Object( $before, 0 );
	$before = $thisrow->body;
	$beforetype = $thisrow->paragraphtype;
	$out .= '<div class="boxheader"><b>Current</b></div><div class="boxtext">';
	$out .= RMLdisplay( $before, $beforetype , false);
	$out .= '</div>';
	
	$after = RMLfiresql("SELECT body, type FROM korrektur WHERE doc_id=$id and sequence=$sequence");
	$thisrow = pg_Fetch_Object($after, 0);
	$after = $thisrow->body;
	$aftertype = $thisrow->type;
	
	$out .= '<div class="boxheader"><b>Suggested edit</b></div><div class="boxtext">';
	$out .= RMLdisplay( $after, $aftertype , false);
	$out .= '</div>';
	
	$out .= '<a href="./?para=confirm&id='.$id.'&sequence='.$sequence.'" class="button edit">Confirm</a> &nbsp; <a href="./?para=reject&id='.$id.'&sequence='.$sequence.'" class="button delete">Reject</a>';
	
	return processOutput( $out, $print_on);
}

// ============================================================================

function RMLconfirmedit($id,$sequence) {
	$karma = RMLgetkarma(RMLgetcurrentuser());
	if($karma < 50) die("Karma Police...");
	
	$table = RMLgetactivetable($id);
	$edit = RMLfiresql("SELECT body,type,user_id FROM korrektur WHERE doc_id=$id AND sequence=$sequence");
	$thisedit = pg_Fetch_Object($edit,0);
	$thisbody = $thisedit->body;
	$thistype = $thisedit->type;
	$thisuser = $thisedit->user_id;
	
	$thisbody = RMLpreparestring($thisbody);
	
	$edit = RMLfiresql("UPDATE $table SET body='$thisbody', paragraphtype=$thistype WHERE doc_id=$id AND id=$sequence");
	$edit = RMLfiresql("DELETE FROM korrektur WHERE doc_id=$id AND sequence=$sequence");
	
	RMLgivekarma($thisuser);
}

// ============================================================================

function RMLrejectedit($id,$sequence) {
	$karma = RMLgetkarma(RMLgetcurrentuser());
	if($karma < 50) die("Karma Police...");
	
	$edit = RMLfiresql("DELETE FROM korrektur WHERE doc_id=$id AND sequence=$sequence");
}

// ============================================================================

function RMLdisplaybottom( $print_on = true )
{
	$prevlink = RMLgetprevlink();
	$nextlink = RMLgetnextlink();
	$uplink = RMLgetuplink();

	$out = "\n\n".'<!-- BOTTOM START --><div class="order">'
.( isset( $prevlink ) ? '<a class="button prev" href="'.$prevlink.'">Prev</a> ' : '' )
.( isset( $uplink ) ? '<a class="button up" href="'.$uplink.'">Up</a> ' : '' )
.( isset( $nextlink ) ? '<a class="button next" href="'.$nextlink.'">Next</a> ' : '' );

	$out .= "</div>";	
	
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayend( $print_on = true )
{
	global $SQLcounter, $SQLtime, $SQLsize, $starttime, $Version;

	RMLclosedb();//ensure no more db action and its clean thereafter
	$now = microtime();

	$out = "\n\n".'<!-- END START -->
<div class="inlineclear"></div>
<div class="end"><a href="https://github.com/RadicalMilitantLibrary">Radical Militant Library</a> <b>'.$Version.'</b><br />
<small><b>' .getNumberFormatted( $SQLcounter, 0 ) .'</b> statements,
<b>' .getNumberFormatted( $now - $starttime, -5 ) .'</b> seconds,
<b>' .sizeFormat( $SQLsize, -3 ) .'</b></small>
<div class="inlineclear"></div>
<a href="bitcoin:19czh9hk8v7hMptokenBFZDNGX4aGiyTRN"><img style="border: 0; margin : 5px;" src="./img/btc.png"/></a>
<div class="inlineclear"></div>
<a href="https://www.catb.org/hacker-emblem/"><img style="border: 0; margin : 5px;" src="./img/hacker.png"/></a>
</div>
</body>
</html>
<!-- END OF LINE -->';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaylocation( $print_on = true ) {
	global $author, $subject, $document, $id;
	if( $subject ) {
		$out .= RMLdisplaysubjectlocation( false );
	}
	if( $author == 'view' ) {
		$out .= RMLdisplayauthorlocation( $id, false );
	}
	if( $document == 'view' ) {
		$out .= RMLdisplaydocumentlocation( false );
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaytitle( $print_on = true ) {
	global $function, $subject, $static, $message, $document, $author, $id, $section, $sequence, $format, $comment, $news, $footnote, $note, $style, $lists, $forum;

	//default
	$title = '~ Paranoid Proofreaders ~';

	$out = '<p class="pagetitle">';

	switch($author) {
	case 'view':
		$authorname = RMLgetauthorname($id);
		$title = "~ $authorname ~";
	break;
	case 'new':
		$title ="Add Author";
	break;
	case 'edit':
		$title = "Edit Author";
	break;
	}

	switch( $document ) {
	case 'view':
		if($section) {
			$docname = RMLgetsectiontitle( $id, $section );
		} else {
			$docname = RMLgetdocumenttitle( $id );
		}
		if($docname) {
			$title = "$docname";
		} else {
			$title = "&nbsp;";
		}
	break;
	case 'edit':
		$docname = RMLgetdocumenttitle( $id );
		$title = "$docname";
	break;
	case 'verify':
		$title = "Review Edit";
	break;
	}

	switch($function) {
	case 'login':
		$title = 'Login';
	break;
	case 'user':
		$title = RMLgetcurrentuser();
		$title = 'User #'.$title;
	break;
	case 'upload':
		$docname = RMLgetdocumenttitle( $id );
		$title = "Upload '$docname'";
	break;
	case 'import':
		$docname = RMLgetdocumenttitle( $id );
		$title = "Uploading '$docname'";
	break;
	case 'edit':
		$title = "Edit Element";
	break;
	case 'about':
		$title = "~ All Your Books Are Belong to Us !!! ~";
	break;
	case 'manual':
		$title = "~ Manual ~";
	break;
	}

	switch( $subject ) {
	case 'view':
		$title = RMLgetsubjecttitle( $id );
		$title = "~ $title ~";
	break;
	case 'add':
		$title = "Add Subject";
	break;
	case 'edit':
		$title = "Edit Subject";
	break;
	}

	switch($lists) {
	case 'view' :
		if($id > 0) {
			$title = RMLgetlistname($id);
		} else {
			$title = '~ Reading Lists ~';
		}			
	break;
	case 'create' :
		$title = "~ New Reading List ~";
	break;
	}

	switch($news) {
	case 'view':
		$title = "~ Latest News ~";
	break;
	case 'add':
		$title = "~ Add News ~";
	break;
	case 'edit':
		$title = "~ Edit News ~";
	break;
	}

	switch($footnote) {
	case 'view':
		$title = RMLgetdocumenttitle( $id );
		$title = $title . " : Footnote $note";
	break;
	}

	switch($message) {
	case 'view':
		$result = RMLfiresql("SELECT handle,subject FROM message WHERE id=$id");
		$thisrow = pg_Fetch_Object( $result, 0 );
		$handle = $thisrow->handle;
		$subject = $thisrow->subject;

		if( hasRights( 'readmsg', array( $handle ) ) ) {
			$title = "~ $subject ~";
		} else {
			$title = "Cookiii baaaaaadddd...";
		}
	break;
	case 'new':
		$title = "~ New Message ~";
	break;
	case 'reply':
		$title = "~ Reply Message ~";
	break;
	}

	switch($document) {
	case 'new':
		$title = "Create New Document";
	break;
	}

	switch($style) {
	case 'new':
		$title = "New Stylesheet";
	break;
	case 'edit':
		$title = "Edit Stylesheet";
	break;
	}
	
	switch ($forum) {
	case 'view':
		$title = "Readers talking Shit ...";
	break;
	}

	$out .= $title .'</p>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayfrontpage( $print_on = true ) {

	$out = "\n".'<div class="order" style="text-align:center">Protecting your <a href="http://www.ala.org/advocacy/intfreedom/statementspols/freedomreadstatement">Freedom to Read</a> since 2010</div>';

	$sql = '';
	
	if( RMLgetkarma(RMLgetcurrentuser()) > 50) {
		
		$result = RMLfiresql("SELECT DISTINCT(doc_id) AS id FROM korrektur");
	
		if(pg_numrows($result) > 0) {
			$out .= '<div class="BoxStart"><div class="boxheader"><b>Books with edits</b></div><p class="boxtext">';	
		}
	
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisid = $thisrow->id;
			$out .= "\n<a href=\"?document=view&amp;id=$thisid\"><img class=\"FrontCover\" alt=\"Cover\" src=\"./covers/cover$thisid\" /></a>";
		}
		if(pg_numrows($result) > 0) {
			$out .= "\n</p></div>";
		}
	}

	$out .= '<p class="boxtext" style="text-align:center">';
	$result = RMLfiresql("SELECT id FROM document  WHERE status=3 ORDER BY posted_on DESC LIMIT 20");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisid = $thisrow->id;

		$out .= "\n<a href=\"?document=view&amp;id=$thisid\">
<img class=\"FrontCover\" alt=\"Cover\" src=\"./covers/cover$thisid\" /></a>";
	}
	$out .= "\n</p>";

	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLgetlatestcomment( $print_on = true ) {
	$result = RMLfiresql("SELECT author,body,level,thread_id,posted_on FROM forum WHERE level > 0 ORDER BY posted_on DESC LIMIT 1");
	$thisrow = pg_Fetch_Object($result,0);
	$thishandle = $thisrow->author;
	$thisuserID = RMLgetuserID( $thishandle );
	$thisbody = nl2br($thisrow->body);
	$thisrating = $thisrow->level;
	$thisdocument = $thisrow->thread_id;
	$thisdate = RMLfixdate($thisrow->posted_on);
	
	if( !file_exists( './users/'.$thisuserID.'.png' ) ) {
		$image = 'Anonymous';
	} else {
		$image = $thisuserID;
	}
	
	$result = "<div class=\"box\"><div class=\"boxheader\"><a href=\"?document=view&amp;id=$thisdocument\"><img class=\"FrontCover\" style=\"float : right;margin : 0;margin-left : 10px;margin-bottom : 5px\" src=\"./covers/cover$thisdocument\" /></a><img class=\"docicon\" src=\"./users/$image.png\" /> &nbsp;" . getRatingDisplay($thisrating) . "</div><div class=\"boxtext\"><sup>Added by : <b>$thishandle</b> (<i>$thisdate</i>)</sup><br />$thisbody</div><div class=\"inlineclear\"></div></div>";
	return processOutput( $result, $print_on );
}

// ============================================================================

function RMLdisplay( $text , $type, $print_on = true ) {
	$out = '';
	/* vv01f: put options in array to reuse elsewhere and shorten code
	$arr[0] = "\n<p class=\"Error\">$text</p>";
	$arr[1] = "\n<p class=\"Head1\">$text</p>";
	* */
	switch($type) {
	case '0':
		$out .= "\n<p class=\"Error\">$text</p>";
	break;
	case '1':
		$out .= "\n<p class=\"Head1\">$text</p>";
	break;
	case '2':
		$out .= "\n<p class=\"Head2\">$text</p>";
	break;
	case '3':
		$out .= "\n<p class=\"Head3\">$text</p>";
	break;
	case '4':
		$out .= "\n<p class=\"ParaIndent\">$text</p>";
	break;
	case '5':
		$out .= "\n<p class=\"ParaBlankOver\">$text</p>";
	break;
	case '6':
		$out .= "\n<p class=\"QuoteIndent\">$text</p>";
	break;
	case '7':
		$out .= "\n<p class=\"QuoteBlankOver\">$text</p>";
	break;
	case '8':
		$out .= "\n<p class=\"ParaNoIndent\">$text</p>";
	break;
	case '9':
		$out .= "\n<p class=\"QuoteNoIndent\">$text</p>";
	break;
	case '17':
		$out .= "\n<p class=\"PreBlankOver\">$text</p>";
	break;
	case '18':
		$out .= "\n<p class=\"PreNoIndent\">$text</p>";
	break;
	case '20':
		$out .= "\n<p class=\"Picture\">$text</p>";
	break;
	case '21':
		$out .= "\n<table class=\"main\">\n<tr>\n<td>$text</td>";
	break;
	case '22':
		$out .= "\n<td>$text</td>";
	break;
	case '23':
		$out .= "</tr>\n<tr>\n<td>$text</td>";
	break;
	case '24':
		$out .= "\n<td>$text</td>\n</tr>\n</table>";
	break;
	case '25':
		$out .= "\n<ul><li>$text";
	break;
	case '26':
		$out .= "</li>\n<li>$text";
	break;
	case '27':
		$out .= "</li>\n<li>$text</li></ul>";
	break;
	case '28':
		$out .= "\n<ol><li>$text";
	break;
	case '29':
		$out .= "</li>\n<li>$text";
	break;
	case '30':
		$out .= "</li>\n<li>$text</li></ol>";
	break;
	case '31':
		$out .= "\n<p class=\"HangingBlankOver\">$text</p>";
	break;
	case '32':
		$out .= "\n<p class=\"HangingIndent\">$text</p>";
	break;
	case '33':
		$out .= "\n<p class=\"ParaVignet\">$text</p>";
	break;
	case '34':
		$out .= "\n<div class=\"BoxStart\">";
	break;
	case '35':
		$out .= "\n</div>";
	break;
	case '36':
		$out .= "\n<p class=\"BoxHead\">$text</p>";
	break;
	}
	return processOutput( $out, $print_on );
}


// ============================================================================

function RMLdisplayfootnote( $docid, $noteid, $print_on = true )
{
	$result = RMLfiresql( "SELECT body FROM footnote WHERE docid=$docid AND id=$noteid" );
	$thisnote = pg_Fetch_Object( $result, 0 );
	$body = $thisnote->body;

	$out = RMLdisplay( $body, 5, false );
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayabout( $print_on = true )
{
	$out = "\n".'<div class="order" style="text-align:center"><img src="./img/about.jpg" style="border: 0; float: right; margin-left: 20px;margin-top: 5px;margin-bottom:10px;"/>'
	.RMLdisplay( "This site is made by readers, for readers. It's about the books.", 8, false )
	.RMLdisplay( "We store our books in a PostgreSQL database. Or, to be exact, we store all the paragraphs in all our books in a database. In that way it is easy to correct mistakes and spelling errors, so if you see any, you can send a message to the librarian in charge of the book. And it saves us from having to store all those ePub files, when you borrow a book we just create a new one with all the latest updates, just for you.", 5, false )
	.RMLdisplay( "This gives us a lot of flexibility. We can output the books in any format we like (ePub only currently, HTML and plaintext are implemented but turned off). We can change the layout of all the books in one operation.", 4, false )
	.RMLdisplay( "If you want to chat, we hang out in the #readingclub channel on <a href=\"http://www.oftc.net/\">OFTC</a>. Or you can try to reach jotunbane@<a href=\"http://cloak.dk\">cloak.dk</a> on jabber (OTR required), or at <a href=\"http://ricochet.im\">Ricochet</a> ricochet:i4oltgzz53xy7aqm.", 4, false )
	.RMLdisplay( "The logo is released under a Creative Commons Attribution-ShareAlike license by <a href=\"http://readersbillofrights.info\">Readers Bill of Rights</a>. It is created by cartoonist and <a href=\"http://questioncopyright.org/\">QuestionCopyright.org</a> artist-in-residence <a href=\"http://blog.ninapaley.com/\">Nina Paley</a>. You can support Nina's work and view her amazing and Creative Commons licensed film, <a href=\"http://www.sitasingstheblues.com/\">Sita Sings the Blues</a>, over at her website.", 5, false );
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaymanual( $print_on = true )
{
	setTimeZone();
	$out = ''
	.RMLdisplay( 'A helpful documentation for all of you that are willing to rise from ordinary Reader to Librarian or are eager to know sligtly more about this place and how it works.', 8, false )
/*	.RMLdisplay( '<i>kittyhawk</i>&#39;s original draft ... --> <a href="./manual.odt">HERE</a> <--', 5, false ) */
	.RMLdisplay( 'The <a href="https://github.com/RadicalMilitantLibrary/manual/blob/master/README.markdown">RML communities continuous manual</a> hosted on the <a href="https://github.com/RadicalMilitantLibrary/">GitHub-Organization</a> for mor collaboration and user input, also you can build yourself or dowload the available files (built on ' .date( "d. M Y H:i:s.", filemtime( './readingclub-man.pdf' ) ).') here as :',5,false)
	.RMLdisplay('<a href="./readingclub-man.md">Markdown</a>',25,false)
	.RMLdisplay('<a href="./readingclub-man.epub">EPUB</a>',26,false)
	.RMLdisplay('<a href="./readingclub-man.pdf">PDF</a>',27,false)
	
	.RMLdisplay('Resources',1,false)
	.RMLdisplay( 'You will need at the very least:',8,false)
	.RMLdisplay('most important, the <a href="http://c3jemx2ube5v5zpg.onion/reading_club.odt">template for ODT</a>',25,false)
	.RMLdisplay('<a href="https://www.libreoffice.org/download/">Libre Office</a> for editing the ODT',27,false)
	.RMLdisplay('More is described in the manual obove.', 5, false );
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayreaders( $print_on = true )
{
	$out = '';
	$sql = RMLfiresql("SELECT DISTINCT(author) AS users, COUNT(id) AS comments, AVG(level) AS avgrating FROM forum WHERE level>0 AND author<>'Anonymous' GROUP BY users ORDER BY comments DESC;");
	for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
		$thisrow = pg_Fetch_Object( $sql, $row );
		$thisuser = $thisrow->users;
		$thisuserID = RMLgetuserID( $thisuser );
		$avgrating = round($thisrow->avgrating,2);
		$numcomments = RMLgetrating( $thisrow->comments );

		if(!file_exists("./users/$thisuserID.png")) {
			$image = "Anonymous";
		} else {
			$image = $thisuserID;
		}

		$out .= "\n".'<div class="box">
<div class="boxheader"><img class="docicon" src="./users/'.$image.'.png" /><b>'.$thisuser.'</b> ('.$numcomments.')</div><div class="boxtext">Read <b>'.$thisrow->comments.'</b> books, scoring <b>'.$avgrating.'</b> on average.</div>
<div class="inlineclear"></div></div>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaylibrarians( $print_on = true )
{
	$out = '';
	$sql = RMLfiresql( "SELECT DISTINCT(handle) AS owner, COUNT(handle) AS docs, MIN(posted_on) AS first, MAX(posted_on) AS last FROM document WHERE status=3 GROUP BY owner ORDER BY docs DESC, first DESC" );
	for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
		$thisrow = pg_Fetch_Object( $sql, $row );
		$thisuser = $thisrow->owner;
		$thisuserID = RMLgetuserID( $thisuser );
		$numdocs = RMLgetrating( $thisrow->docs );
		$daysactive = abs((strtotime($thisrow->last) - strtotime($thisrow->first)) / (60*60*24)) + 1;
		// +1 because from today to today is 1 day and not 0
		// awoids division by zero on users active for just 1 day (Jotunbane)
		$booksperweek = getNumberFormatted( ($thisrow->docs / $daysactive)*7 ,1);

		if( !file_exists( './users/'.$thisuserID.'.png' ) ) {
			$image = 'Anonymous';
		} else {
			$image = $thisuserID;
		}

		$out .= "\n".'<div class="librarian box">
<div class="boxheader"><img class="docicon" src="./users/'.$image.'.png" /><b>'.$thisuser.'</b> ('.$numdocs.')</div>
<div class="boxtext">Added <b>' .$thisrow->docs .'</b> books between <b>' .RMLfixdate( $thisrow->first ) .'</b> and <b>' .RMLfixdate( $thisrow->last ) .'</b> (~<b>' .$booksperweek .'</b>&nbsp;books/week)</div><div class="inlineclear"></div></div>';

//<ul>
//<li><span>Books</span>: ' .$thisrow->docs .'</li>
//<li><span>First</span>: ' .RMLfixdate( $thisrow->first ) .'</li>
//<li><span>Last</span>: ' .RMLfixdate( $thisrow->last ) .'</li>
//</ul>


	}
	return processOutput( $out, $print_on );
}

// ============================================================================

/* ewa: Maybe use some SERVER variables instead,
 * but not sure with onion services on that purpose without seeing php_info().
 * */
function getRMLURL()
{
	return 'http://c3jemx2ube5v5zpg.onion/';
}

/* ewa: single place to influence how a number looks like,
 *   issue #99 (formatting numbers in e.g. document view, about, ...)
 *   for the time when we have multilanguage RML, here we can take care of it
 *   negative $decplaces is for vanishing zeroes at the end as positive sets zeroes
 * */
function getNumberFormatted( $n, $decplaces = 2, $decsep = '.', $tsdsep = ',' ) {
	if( $decplaces < 0 ) {
		$decplaces = abs( $decplaces );
		$n = ''.round( $n , $decplaces );
		$n = number_format( $n, $decplaces, $decsep, $tsdsep );
		$n = preg_replace( array( '/\\'.$decsep.'+0+$/', '/(\\'.$decsep.'+[0-9]*)0+$/' ), array( '', '\1' ), $n/** /, -1, $cnt/**/ );
	} else {
		$n = number_format( $n, $decplaces, $decsep, $tsdsep );
	}
	return $n;
}

/* ewa: max days for reviewer (subject owners) to review, otherwise submitter of a book can just go on publishing herself */
// should maybe move to settings
function getMaxReviewDays() {
	return 14;
}

function checkSettings( $settingsFilename ) {
  if (! is_readable( $settingsFilename ) ) {
      die('ERROR: Configuration in settings.php not readable or missing!');
  }
// todo: more tests on settings
// e.g. check if salt is set properly, if not die('settings: need the salt to be set properly')
// if (empty($secret_salt)) { die('ERROR: Setting: need salt to be set properly. Current value:'.$secret_salt); }
}

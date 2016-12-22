<?php

// ============================================================================
//  Common functions for Radical Militant Library
//  Copyright (C) 2009-2016 Jotunbane
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
	$out = "\n\n".'<!-- TOP START -->
<table class="body">
<tr><td colspan="3" class="location">
<a href="."><img class="logo" alt="Logo" src="./img/logo.png" /></a><a href="https://github.com/RadicalMilitantLibrary"><img style="float : right" src="./img/github.png" /></a>'
	.RMLdisplaylocation( false )
	.RMLdisplaytitle( false )
	."\n".'</td></tr>';
	return processOutput( $out, $print_on );
}

// ============================================================================

// assemble button for navigation
function RMLMenuButton( $btntile, $href = "", $class = "", $decoration = 'star' ) {
	return  "\n<a class=\"$class $decoration\" href=\"$href\">$btntitle</a>";
}

function RMLdisplayleft( $print_on = true )
{
	global $author, $subject, $news, $document, $function, $message, $style, $lists;
	$currentuser = RMLgetcurrentuser();

	$out = "\n\n".'<!-- LEFT START -->
<tr><td class="left">';

	if($function == 'about') {
		$out .= "\n<a class=\"activebutton about\" href=\"?function=about\">About</a>";
	} else {
		$out .= "\n<a class=\"button about\" href=\"?function=about\">About</a>";
	}

	if($news) {
		$out .= "\n<a class=\"activebutton email\" href=\"?news=view\">News</a>";
	} else {
		$out .= "\n<a class=\"button email\" href=\"?news=view\">News</a>";
	}

	if(($author == 'view') || ($document == 'view')) {
		$out .= "\n<a class=\"activebutton like\" href=\"?author=view&amp;letter=A\">Authors</a>";
	} else {
		$out .= "\n<a class=\"button like\" href=\"?author=view&amp;letter=A\">Authors</a>";
	}

	if($subject == 'view') {
		$out .= "\n<a class=\"activebutton star\" href=\"?subject=view&amp;letter=All&amp;id=0\">Subjects</a>";
	} else {
		$out .= "\n<a class=\"button star\" href=\"?subject=view&amp;letter=All&amp;id=0\">Subjects</a>";
	}

	if(($lists == 'view') || ($lists == 'add')){
		$out .= "\n<a class=\"activebutton star\" href=\"?lists=view&ampid=0\">Reading Lists</a>";
	} else {
		$out .= "\n<a class=\"button star\" href=\"?lists=view&amp;id=0\">Reading Lists</a>";
	}

	if($function == 'librarians') {
		$out .= "\n<a class=\"activebutton star\" href=\"?function=librarians\">Librarians</a>";
	} else {
		$out .= "\n<a class=\"button star\" href=\"?function=librarians\">Librarians</a>";
	}

//	if($function == 'readers') {
//		$out .= "\n<a class=\"activebutton star\" href=\"?function=readers\">Readers</a>";
//	} else {
//		$out .= "\n<a class=\"button star\" href=\"?function=readers\">Readers</a>";
//	}

	if($function == 'manual') {
		$out .= "\n<a class=\"activebutton star\" href=\"?function=manual\">Manual</a>";
	} else {
		$out .= "\n<a class=\"button star\" href=\"?function=manual\">Manual</a>";
	}

	if( ( !$currentuser ) && ( $function <> 'login' ) ) {
		$out .= "\n<a class=\"button star\" href=\"?function=login\">Login</a>";
	}
	if( ( !$currentuser ) && ( $function == 'login' ) ) {
		$out .= "\n<a class=\"activebutton star\" href=\"?function=login\">Login</a>";
	}

	if(($currentuser) && ($function <> 'user') && ($message <> 'new') && ($style <> 'new') && ($document <> 'new')) {
		$out .= "\n<a class=\"button like\" href=\"?function=user\">My Page</a>";
	}
	if(($function == 'user') || ($message == 'new') || ($style == 'new') || ($document == 'new')) {
		$out .= "\n<a class=\"activebutton like\" href=\"?function=user\">My Page</a>";
	}

	$out .= "\n"
	// banner ad
	.'<div class="center"><a href="http://answerstedhctbek.onion"><img style="border:0;margin-top:10px" src="./img/banner.gif" alt="hidden answers" /></a></div>'
	// bitcoin donation address
	.'<div class="center"><a href="bitcoin:1MjAY5FZ9To6M1VHvgWa95WzsVtD3X9NaA"><img style="border:0" src="./img/qrcode.png" alt="I can haz bitcoinz" /></a></div>'
	// glider
	.'<div class="center"><a href="http://www.catb.org/hacker-emblem/"><img style="border:0" src="./img/hacker.png" alt="Glider => Hacker" /></a></div>'
	// pgp pubkey
	.'<div class="center"><a href="./jotunbane.asc"><img src="./img/pgp.png" alt="PGP Public Key" /></a></div>'
.'</td>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaymain( $id, $print_on = true ) {
	global $function, $subject, $static, $message, $document,
		$author, $section, $comment, $news, $footnote, $note, $style, $lists;

	$out = "\n\n".'<!-- MAIN START -->
<td class="main">';

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
	case 'readers':
		$out .= RMLdisplayreaders( false );
		$frontpage = false;
	break;
	case 'librarians':
		$out .= RMLdisplaylibrarians( false );
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
	switch( $comment ) {
	case 'view':
		$out .= RMLdisplaycomment( false );
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

	$out .= "\n".'</td><td class="right"></td></tr>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaybottom( $print_on = true )
{
	$prevlink = RMLgetprevlink();
	$nextlink = RMLgetnextlink();
	$uplink = RMLgetuplink();

	$out = "\n\n".'<!-- BOTTOM START -->
<tr><td></td><td class="bottom">'
.( isset( $prevlink ) ? '<a class="button prev" href="'.$prevlink.'">Prev</a> ' : '' )
.( isset( $uplink ) ? '<a class="button up" href="'.$uplink.'">Up</a> ' : '' )
.( isset( $nextlink ) ? '<a class="button next" href="'.$nextlink.'">Next</a> ' : '' )
.'</td><td></td></tr>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayend( $print_on = true )
{
	global $SQLcounter, $SQLtime, $SQLsize, $starttime, $Version;

	RMLclosedb();//ensure no more db action and its clean thereafter
	$now = microtime();

	$out = "\n\n".'<!-- END START -->
<tr><td colspan="3" class="end">
Radical Militant Library <b>'.$Version.'</b><br />
<small><b>' .getNumberFormatted( $SQLcounter, 0 ) .'</b> statements,
<b>' .getNumberFormatted( $now - $starttime, -5 ) .'</b> seconds,
<b>' .sizeFormat( $SQLsize, -3 ) .'</b></small>
</td></tr>
</table>
</body>
</html>
<!-- END OF LINE -->';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaylocation( $print_on = true ) {
	global $author, $subject, $document, $id;
	$out = '<a class="button home" href="."><b>Home</b></a>';
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
	global $function, $subject, $static, $message, $document, $author, $id, $section, $sequence, $format, $comment, $news, $footnote, $note, $style, $lists;

	//default
	$title = '~ Radical Militant Library ~';

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

	switch($comment) {
	case 'view':
		$title = "~ Radical Militant Comments ~";
	break;
	case 'new':
		$title = RMLgetdocumenttitle( $id );
		$title = "Comment on <i>" . $title . "</i>";
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
	}

	switch($function) {
	case 'login':
		$title = 'Radical Militant Sign-Up';
	break;
	case 'user':
		$title = "Radical Militant User Page";
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
		$title = "~ Scimus quae legis, et non dicimus ~";
	break;
	case 'manual':
		$title = "~ Radical Militant Manual ~";
	break;
	case 'readers':
		$title = "Radical Militant Readers";
	break;
	case 'librarians':
		$title = "Radical Militant Librarians";
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
			$title = '~ Radical Militant Reading Lists ~';
		}			
	break;
	case 'create' :
		$title = "~ New Radical Militant Reading List ~";
	break;
	}

	switch($news) {
	case 'view':
		$title = "~ Radical Militant News ~";
	break;
	case 'add':
		$title = "~ Add Radical Militant News ~";
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

	$out .= $title .'</p>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayfrontpage( $print_on = true ) {

	$out = "\n".'<div class="order" style="text-align:center">Upholding the <a href="http://readersbillofrights.info/">Readers Bill of Rights</a> since 2010</div>';
	
	$out .= RMLgetlatestcomment( false );

	$out .= '<div class="box"><div class="boxheader"><b>New Books</b></div>
<p class="boxtext" style="text-align:center">';
	$result = RMLfiresql("SELECT id,title FROM document  WHERE status=3 ORDER BY posted_on DESC LIMIT 15");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisid = $thisrow->id;
		$thistitle = $thisrow->title;
		$out .= "\n<a href=\"?document=view&amp;id=$thisid\">
<img class=\"FrontCover\" alt=\"$thistitle Cover\" src=\"./covers/cover$thisid\" /></a>";
		if(($row == 4) || ($row == 9)) {
			$out .= "\n<br/>";
		}
	}
	$out .= "\n</p></div>";

		$out .= "\n".'<div class="box"><div class="boxheader"><b>Highest Rated Books</b></div>
<p class="boxtext" style="text-align:center">';

	$result = RMLfiresql("SELECT DISTINCT thread_id, AVG(level) AS score FROM forum WHERE level > 0 GROUP BY thread_id ORDER BY score DESC LIMIT 15");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisid = $thisrow->thread_id;

		$out .= "\n<a href=\"?document=view&amp;id=$thisid\"><img class=\"FrontCover\" alt=\"Cover\" src=\"./covers/cover$thisid\" /></a>";

		if(($row == 4) || ($row == 9)) {
			$out .= "\n<br/>";
		}
	}
	$out .= "\n</p></div>";

/*	$out .= "\n<div class=\"box\"><div class=\"boxheader\"><b>Most Downloaded Books</b></div>
<p class=\"boxtext\" style=\"text-align:center\">";

	$result = RMLfiresql("SELECT id,downloads FROM document ORDER BY downloads DESC LIMIT 10");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisid = $thisrow->id;

		$out .= "\n<a href=\"?document=view&amp;id=$thisid\">
<img class=\"FrontCover\" alt=\"Cover\" src=\"./covers/cover$thisid\" /></a>";

		if(($row == 4)) {
			$out .= "\n<br/>";
		}
	}
	$out .= "\n</p></div>"; */
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLgetlatestcomment( $print_on = true ) {
	$result = RMLfiresql("SELECT author,body,level,thread_id,posted_on FROM forum WHERE level > 0 ORDER BY posted_on DESC LIMIT 1");
	$thisrow = pg_Fetch_Object($result,0);
	$thishandle = $thisrow->author;
	$thisbody = nl2br($thisrow->body);
	$thisrating = $thisrow->level;
	$thisdocument = $thisrow->thread_id;
	$thisdate = RMLfixdate($thisrow->posted_on);
	
	if( !file_exists( './users/'.$thishandle.'.png' ) ) {
		$image = 'Anonymous';
	} else {
		$image = $thishandle;
	}
	
	$result = "<div class=\"box\"><div class=\"boxheader\"><a href=\"?document=view&amp;id=$thisdocument\"><img class=\"FrontCover\" style=\"float : right;margin : 0;margin-left : 10px;margin-bottom : 5px\" src=\"./covers/cover$thisdocument\" /></a><img class=\"docicon\" src=\"./users/$image.png\" /><b>$thishandle</b> &nbsp;" . getRatingDisplay($thisrating) . "</div><div class=\"boxtext\"><small><sup>$thisdate</sup></small><br />$thisbody</div><div class=\"inlineclear\"></div></div>";
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
	$text_size = sizeFormat( filesize( '/home/jotunbane/workspace/backup.sql' ) );

	$cover_size = getDirectorySize( './covers' );
	$cover_size = sizeFormat( $cover_size['size'] );

	$picture_size = getDirectorySize( './pictures' );
	$picture_size = sizeFormat( $picture_size['size'] );

	$sql = RMLfiresql( "SELECT COUNT(id) AS doccount, COUNT(DISTINCT(author_id)) AS authorcount, SUM(downloads) as total, count(DISTINCT(handle)) as usercount FROM document WHERE status=3" );
	$thiscount = pg_Fetch_Object( $sql, 0 );
	$doccount = getNumberFormatted( $thiscount->doccount, 0 );
	$authorcount = getNumberFormatted( $thiscount->authorcount, 0 );
	$total = getNumberFormatted( $thiscount->total, 0 );
	$users = getNumberFormatted( $thiscount->usercount, 0 );

	$sql = RMLfiresql( "SELECT COUNT(DISTINCT(handle)) AS usercount FROM \"user\"" );
	$thiscount = pg_Fetch_Object( $sql, 0 );
	$readers = getNumberFormatted( $thiscount->usercount, 0 );

	$out = "\n".'<div class="order" style="text-align:center"><small><b>'.$users.'</b> Radical Militant Librarians manage
<b>'.$doccount.'</b> books from
<b>'.$authorcount.'</b> authors.<br />Database :
<b>'.$text_size.'</b> text,
<b>'.$cover_size.'</b> covers,
<b>'.$picture_size.'</b> pictures.<br />
<b>'.$readers.'</b> Radical Militant Readers have borrowed
<b>'.$total.'</b> Books.</small></div>
<img src="./img/about.jpg" style="border: 0; float: right; margin-left: 20px;margin-top: 5px;margin-bottom:10px;"/>'
	.RMLdisplay( "We are the Radical Militant Librarians, these are our books. We will accept no barriers between readers and our books. We will never register who borrows what and when. All books are welcome in our library (bring your own books).", 8, false )
	.RMLdisplay( "We store our books in a PostgreSQL database. Or, to be exact, we store all the paragraphs in all our books in a database. In that way it is easy to correct mistakes and spelling errors, so if you see any, you can send a message to the librarian in charge of the book. And it saves us from having to store all those ePub files, when you borrow a book we just create a new one with all the latest updates, just for you.", 5, false )
	.RMLdisplay( "This gives us a lot of flexibility. We can output the books in any format we like (ePub only currently, HTML and plaintext are implemented but turned off). We can change the layout of all the books in one operation. Readers can define a different layout for each book, author or subject. Or just make their own \"default\" and completely change the layout of all the books they borrow.", 4, false )
	.RMLdisplay( "If you want to chat, we hang out in the #readingclub channel on <a href=\"http://www.oftc.net/\">OFTC</a>. Or you can try to reach jotunbane@<a href=\"http://cloak.dk\">cloak.dk</a> on jabber (OTR required), at <a href=\"http://ricochet.im\">Ricochet</a> ricochet:i4oltgzz53xy7aqm, or <a href=\"https://en.wikipedia.org/wiki/Bitmessage\">Bitmessage</a> BM-2cV7JNNkafKxDbJiNeLfSK8q6uPJNDQ8gj.", 4, false )
	.RMLdisplay( "<a href=\"?function=login\">Sign up</a> and you too can become a Radical Militant Librarian, or you can start by becoming a Radical Militant Reader by telling us what books you liked (or hated).", 4, false )
	.RMLdisplay( "The logo is released under a Creative Commons Attribution-ShareAlike license by <a href=\"http://readersbillofrights.info\">Readers Bill of Rights</a>. It is created by cartoonist and <a href=\"http://questioncopyright.org/\">QuestionCopyright.org</a> artist-in-residence <a href=\"http://blog.ninapaley.com/\">Nina Paley</a>. You can support Nina's work and view her amazing and Creative Commons licensed film, <a href=\"http://www.sitasingstheblues.com/\">Sita Sings the Blues</a>, over at her website.", 5, false )
	;
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaymanual( $print_on = true )
{
	setTimeZone();
	$out = ''
	.RMLdisplay( 'A helpful documentation for all of you that are willing to rise from ordinary Reader to Librarian or are eager to know sligtly more about this place and how it works.', 5, false )
/*	.RMLdisplay( '<i>kittyhawk</i>&#39;s original draft ... --> <a href="./manual.odt">HERE</a> <--', 5, false ) */
	.RMLdisplay( 'The <a href="https://github.com/RadicalMilitantLibrary/manual/blob/master/README.markdown" target="_blank">RML communities continuous manual</a> hosted on the <a href="https://github.com/RadicalMilitantLibrary/" target="_blank">GitHub-Organization</a> for mor collaboration and user input…<br>
	also you can build yourself or dowload the available files (built on ' .date( "d. M Y H:i:s.", filemtime( './readingclub-man.pdf' ) ).') here: <ul>
	<li><a href="./readingclub-man.md">Markdown</a>,</li>
	<li><a href="./readingclub-man.epub">EPUB</a> and</li>
	<li><a href="./readingclub-man.pdf">PDF</a></li>
	</ul>', 5, false )
	.'<h2>Resources</h2>'
	.RMLdisplay( 'You will need at the very least:
	<ul>
	<li>most important, the <a href="http://c3jemx2ube5v5zpg.onion/reading_club.odt">template for ODT</a></li>
	<li><a href="https://www.libreoffice.org/download/">Libre Office</a> for editing the ODT</li>
	</ul> More is described in the manual obove.', 5, false );
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayreaders( $print_on = true )
{
	$out = '';
	$sql = RMLfiresql("SELECT DISTINCT(author) AS users, COUNT(id) AS comments FROM forum WHERE level>0 AND author<>'Anonymous' GROUP BY users ORDER BY comments DESC;");
	for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
		$thisrow = pg_Fetch_Object( $sql, $row );
		$thisuser = $thisrow->users;
		$numcomments = RMLgetrating( $thisrow->comments );

		if(!file_exists("./users/$thisuser.png")) {
			$image = "Anonymous";
		} else {
			$image = $thisuser;
		}

		$out .= "\n".'<div class="box">
<p class="boxheader"><img class="docicon" src="./users/'.$image.'.png" /><b>'.$thisuser.'</b> ('.$numcomments.')</p>
</div>
<div class="inlineclear">&nbsp;</div>';
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
		$numdocs = RMLgetrating( $thisrow->docs );

		if( !file_exists( './users/'.$thisuser.'.png' ) ) {
			$image = 'Anonymous';
		} else {
			$image = $thisuser;
		}

		$out .= "\n".'<div class="librarian box">
<div class="boxheader"><img class="docicon" src="./users/'.$image.'.png" /><b>'.$thisuser.'</b> ('.$numdocs.')</div>
<div class="boxtext">Added <b>' .$thisrow->docs .'</b> books between <b>' .RMLfixdate( $thisrow->first ) .'</b> and <b>' .RMLfixdate( $thisrow->last ) .'</b></div><div class="inlineclear"></div></div>';

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
function getNumberFormatted( $n, $decplaces = 2, $decsep = '.', $tsdsep = ',' )
{
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
function getMaxReviewDays()
{
	return 14;
}

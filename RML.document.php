<?php
// ============================================================================
//  Functions for 'document' manipulation
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

function getAuthorOptions( $author ) {
	$a = '';
	$result = RMLfiresql("SELECT name, sort_name, id FROM author ORDER BY sort_name");
	for( $row = 0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisname = $thisrow->name;
		$a .= "\n".'<option value="'.$thisname.'"'.( ( $thisrow->id == $author ) ? ' selected="yes"' : '' ).'>'.$thisrow->sort_name.'</option>';// ewa: sort-name is more user convenient to jump through
	}
	return $a;
}

// ============================================================================

function getSubjectOptions( $subject ) {
	$a = '';
	$result = RMLfiresql("SELECT subject_name, id FROM subject ORDER BY subject_name");
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisname = $thisrow->subject_name;
		$a .= '<option value="'.$thisname.'"'.( ( $thisrow->id == $subject ) ? ' selected="yes"' : '' ).'>'.$thisname.'</option>';
	}
	return $a;
}

// ============================================================================


function RMLnewdocument( $print_on = true )
{
	global $author, $subject, $title, $subtitle, $year, $keywords,
		$copyright, $teaser;

	//todo: move to createdocument is that is less error prone
	$ISBN = RMLgetuniqueid();

	//todo: add automatic author/subject selection (attribute selected)
	$out = '';
	$options_auth = getAuthorOptions( $author );
	$options_subj = getSubjectOptions( $subject );

	$out .= "\n".'<form enctype="multipart/form-data" method="post" action="?document=create"><table class="form">
<tr><td>Author</td><td><select class="norm" name="author">' .$options_auth .'</select>
</td></tr>';
//todo: put add-author-button here (only for those who have the rights..)
//todo: wish for new author with data
	$out .= '<tr><td>Subject</td><td>
<select class="norm" name="subject">' .$options_subj .'</select></td></tr>';
//todo: put add-subject-button here (only for those who have the rights..)
//todo: wish for new subject with data
	$out .= "\n".'<tr><td>Title</td><td><input class="norm" type="text" name="title" value="'.$title.'"></td></tr>
<tr><td>(Sub-title)</td><td><input class="norm" type="text" name="subtitle" value="'.$subtitle.'"></td></tr>
<tr><td>(Year)</td><td><input class="norm" type="text" name="year" value="'.$year.'"></td></tr>
<tr><td>(Unique Id)</td><td><input class="norm" type="text" name="ISBN" value="'.$ISBN.'"></td></tr>
<tr><td>(Key,Words)</td><td><input class="norm" type="text" name="keywords" value="'.$keywords.'"></td></tr>
<tr><td valign="top">Colophon</td><td><textarea class="norm" rows="5" name="copyright">'.$copyright.'</textarea></td></tr>
<tr><td valign="top">(Teaser)</td><td><textarea class="norm" rows="10" name="teaser">'.$teaser.'</textarea></td></tr>
<tr><td>Cover </td><td><input type="file" size="49" name="picture"></td></tr>
<tr><td></td><td><input type="submit" value="Create document"></td></tr></table>
<input type="hidden" name="document" value="create"></form>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLeditdocument( $id ) {
	$result = RMLfiresql("SELECT id,title,subtitle,year,\"unique\",keywords,copyright,teaser,subject_id,author_id,handle FROM document WHERE id=$id");
	$thisres = pg_Fetch_Object($result,0);
	$title = $thisres->title;
	$subtitle = $thisres->subtitle;
	$year = $thisres->year;
	$ISBN = $thisres->unique;
	$keywords = $thisres->keywords;
	$copyright = $thisres->copyright;
	$teaser = $thisres->teaser;
	$subid = $thisres->subject_id;
	$autid = $thisres->author_id;
	$thisid = $thisres->id;

	if( ! hasRights( 'editdocument', array( $thisres->handle ) ) ) {
		$out = "ERROR: Document Update : Cookie baaaaaaaad...";
	} else {

		$options_auth = '';
		$result = RMLfiresql("SELECT name,id FROM author ORDER BY sort_name");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisname = $thisrow->name;
			$options_auth .= "\n".'<option value="'.$thisname.'"' .( ( $thisrow->id == $autid ) ? ' selected="yes"' : '' ).'>'.$thisname.'</option>';
		}

		$options_subj = '';
		$result = RMLfiresql("SELECT subject_name,id FROM subject ORDER BY subject_name");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisname = $thisrow->subject_name;
			$options_subj .= "\n".'<option value="'.$thisname.'"' .( ( $thisrow->id == $subid ) ? ' selected="yes"' : '' ).'>'.$thisname.'</option>';
		}

		$out = "\n".'<form enctype="multipart/form-data" method="post" action="?document=update&amp;id='.$thisid.'"><table class="form">
<tr><td>Author</td><td><select class="norm" name="author">'.$options_auth.'</select></td></tr>
<tr><td>Subject</td><td><select class="norm" name="subject">'.$options_subj.'</select></td></tr>
<tr><td>Title</td><td><input class="norm" type="text" name="title" value="'.$title.'"></td></tr>
<tr><td>(Sub-title)</td><td><input class="norm" type="text" name="subtitle" value="'.$subtitle.'"></td></tr>
<tr><td>(Year)</td><td><input class="norm" type="text" name="year" value="'.$year.'"></td></tr>
<tr><td>(Unique Id)</td><td><input class="norm" type="text" name="ISBN" value="'.$ISBN.'"></td></tr>
<tr><td>(Key,Words)</td><td><input class="norm" type="text" name="keywords" value="'.$keywords.'"></td></tr>
<tr><td valign="top">Colophon</td><td><textarea class="norm" rows="5" name="copyright">'.$copyright.'</textarea></td></tr>
<tr><td valign="top">(Teaser)</td><td><textarea class="norm" rows="10" name="teaser">'.$teaser.'</textarea></td></tr>
<tr><td>Cover </td><td><input type="file" size="49" name="picture"></td></tr>
<tr><td></td><td><input type="submit" value="Update document"></td></tr></table>
<input type="hidden" name="document" value="update"></form>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLupdatedocument( $id, $print_on = true ) {
	global $subject, $author, $title, $subtitle, $year, $ISBN,
		$keywords, $copyright, $teaser;

	$result = RMLfiresql("SELECT handle FROM document WHERE id=$id");
	$thisrow = pg_Fetch_Object( $result, 0 );
	$handle = $thisrow->handle;

	if( ! hasRights( 'editdocument', array( $handle ) ) ) {
		$out = "ERROR: Document Update : Cookie baaaaaaaad...";
	} else {

		$subject_id = RMLgetsubjectid($subject);
		$author_id = RMLgetauthorid($author);
		RMLfiresql("UPDATE document SET subject_id=$subject_id,author_id=$author_id,title='$title',subtitle='$subtitle',year='$year',\"unique\"='$ISBN',teaser='$teaser',keywords='$keywords',copyright='$copyright' WHERE id=$id");
		$target_path = "./covers/" . "cover" . "$id";

		if(is_uploaded_file($_FILES['picture']['tmp_name'])) {
			unlink($target_path);
			unlink($target_path . ".jpg");
		}

		if(move_uploaded_file($_FILES['picture']['tmp_name'], $target_path . ".jpg")) {
			$myimage = new RMLimage();
			$myimage->load($target_path . ".jpg");
			$myimage->resizeToWidth(150);
			$myimage->save($target_path);
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

// ewa: created the "jacket" for the content to be added later
function RMLcreatedocument( $print_on = true )
{
	global $subject, $author, $title, $subtitle, $year, $ISBN,
		$keywords, $copyright, $teaser;

	$out = '';
	$handle = RMLgetcurrentuser();
	if( ! hasRights( 'adddocument', array( $handle ) ) ) {
		$out = "ERROR: Document Create : Cookie baaaaaaaad...";
		return false;
	} else {
		//todo: fix wrong logic, first elms need to be added, later document can rely on that dependencies
		//todo: rename ISBN to its actual usage: RMLuuid

		$subject_id = RMLgetsubjectid( $subject );
		$author_id = RMLgetauthorid( $author );

		if( ! RMLfiresql( "INSERT INTO document VALUES(DEFAULT,DEFAULT,'$handle',$subject_id,$author_id,'$title','$subtitle','$year','$ISBN','$teaser','$keywords','$copyright',DEFAULT,DEFAULT)" ) ) {
			$out = "ERROR: Document Create failed: maybe you missed some details in the form.";
		}

		$sql = RMLfiresql( "SELECT id FROM document where \"unique\"='$ISBN'" );
		$thisrow = pg_Fetch_Object( $sql, 0 );
		$thisid = $thisrow->id;

		//todo: encapsule in e.g. createCoverImage( $id, $width = 150 )
		$target_path = './covers/cover' .$thisid;
		move_uploaded_file( $_FILES['picture']['tmp_name'], $target_path . '.jpg' );
		$myimage = new RMLimage();
		$myimage->load( $target_path .'.jpg' );
		$myimage->resizeToWidth( 150 );
		$myimage->save( $target_path );

		//ewa: DEFAULT SERIAL id is needed for nothing in this table? then better just drop the thing. or even use combined primary key.
		//maybe also a good idea to have a 2nd table for types of metadata to be more flexible to add new kinds
		//todo: encapsule in e.g. addMetadata( $id, 'metaname', $value )
		RMLfiresql( "INSERT INTO metadata VALUES(DEFAULT,$thisid,'creator','$handle')" );
		RMLfiresql( "INSERT INTO metadata VALUES(DEFAULT,$thisid,'editor','$handle')" );
		//todo: add array of ISBN (or other IDs) to metadata as 'id'
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaydocumentsbyauthor( $id, $print_on = true )
{
	$out = '';
	$sql = RMLfiresql( "SELECT name,bio,born,dead,(SELECT COUNT(id) FROM document WHERE author_id = author.id AND status=3) AS numdoc FROM author where id=$id" );
	if ( ! ( $thisrow = pg_Fetch_Object( $sql, 0 ) ) ) {
		$out = 'ERROR: This Author is not used yet, read how to add a book yourself <a href="?function=manual">in the manual</a>.';
	} else {
		$thisname = $thisrow->name;
		$bio = $thisrow->bio;
		$born = $thisrow->born;
		$dead = $thisrow->dead;
		$numdoc = $thisrow->numdoc;

		setTimeZone();

		$age = getAge( $born, $dead );
		$would = date('Y') - substr( $born ,0 ,4 );	//getAge( $born, date( 'Ymd' ) )
		if( $dead > 0 && is_numeric( $born ) ) {
			$doom = date('Y') - substr( $dead, 0, 4 );
			$dead = getDateFormatted( $dead );
		} else {
			$dead = 'No';
		}

		if( $born > 0 && is_numeric( $born ) ) {
			$born = getDateFormatted( $born );
		} else {
			$born = 'Unknown';
			$age = '';
		}

		$sql = RMLfireSQL( "SELECT sum(length(body)) as bodylength,count(body) as elementcount FROM author$id" );
		$thisrow = pg_Fetch_Object( $sql, 0 );
		$kilobyte = sizeFormat( $thisrow->bodylength );
		$element = $thisrow->elementcount;
		$bio = nl2br( $bio );

		$out .=
			"\n".'<img class="authorphoto" alt="Author #'.$id.'" src="./authors/author'.$id.'"/>
<div class="statusbox">	
	<div class="box"><p class="boxheader"><b><big>Status</big></b></p><p class="boxtext"><big>
	born <b>'.$born.'</b>'
			.( ( $born != 'Unknown' ) ? ' (+' .$would .')' : '' )
			.( ( strlen( $dead ) >= 4 ) ? ' died <b>' .$dead .'</b> at' : '' )
			.' age <b>' .$age .'</b> (+' . getNumberFormatted( $doom, 0 ) .')
	<br/><br/><b>'. getNumberFormatted( $numdoc, 0 ) .'</b>&nbsp;book'
			.( ( $numdoc > 1 ) ? 's' : '' )
			. ' online (<b>' .$kilobyte .'</b> in <b>' .getNumberFormatted( $element, 0 ) .'</b>&nbsp;paragraphs)</big></p></div></div>'
			.'</table>'
			.'<p class="ParaBlankOver">'.$bio.'</p>';

		// ewa: todo: show source links, later ...

		if( hasRights( 'editauthor' /*, array( $maintainer )*/ ) ) {
			$out .= "\n".'<a href="?author=edit&amp;id='.$id.'" class="button edit" title="edit author">Edit</a>' ;
		}

		$out .= "\n<div class=\"inlineclear\">&nbsp;</div>";

		$result = RMLfiresql( "SELECT id,title,year,keywords,subject_id,teaser,(SELECT AVG(level) FROM forum WHERE thread_id=document.id AND level > 0) AS score,(SELECT subject_name FROM subject WHERE id=document.subject_id) AS subjecttitle FROM document WHERE author_id=$id AND status=3 ORDER BY title" );
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thistitle = $thisrow->title;
			$thisyear = $thisrow->year;
			$thiskey = $thisrow->keywords;
			$thissubjectid = $thisrow->subject_id;
			$thissubject = $thisrow->subjecttitle;
			$thisteaser = $thisrow->teaser;
			$avgscore = $thisrow->score;

			if( strlen( $thisteaser ) > 400 ) {
				$thisteaser = substr( $thisteaser, 0, 400 ) .' ...';
				$thisteaser = strip_tags( $thisteaser );
			}

			$out .= '<div class="box">
	<p class="boxheader"><a href="?document=view&amp;id='.$thisid.'"><img class="Cover" alt="Cover" src="./covers/cover'.$thisid.'"/><b>'.$thistitle.'</b></p>
	<p class="boxtext">
	<small><a href="?subject=view&amp;id='.$thissubjectid.'">'.$thissubject.'</a>,
	<b>' .$thisyear .'</b></small>'
				. '<span class="right-float">' .getRatingDisplay( $avgscore ) .'</span>' 
				.'</p><p class="boxtext">'.$thisteaser.'</p>
	<div class="inlineclear"></div></div>';
		}
	}
	return processOutput( $out, $print_on );
}
// ============================================================================

/* ewa: I want to have metadate for academic citations like http://www.bibtex.org/Format/
 *   so it seems feasable for me to sue this for elaborating metadata in general before adding it
 *   please review and add your thought on what kind of metadata we might stumple over and under what name it is useable for what
 *
ALTER TABLE document
	ADD COLUMN language	integer CONSTRAINT FK_LANG_ID REFERENCES languages(lid);
CREATE TABLE IF NOT EXISTS metadata (
	docid	integer NOT NULL CONSTRAINT FK_DOC_ID REFERENCES document(id),
	type	varchar(), --could be name of all metadate we do not have in document: language
	value	varchar()
);
 * */
function RMLgetBibTeX( $data )	//get all data in this named list, e.g. via iterating a query and dropping $row
{
	if ( !isset( $data['id'] ) || $data['id'] == 0 ) {
		return false;
	}
	return '@' . ( ( !isset($data['doctype']) ) ? 'book' : $data['doctype'] ) .'{'	//may be article or another type of document
		. '	book:RML-'.$data['id']	//serial
		. ( !isset($data['title'])			|| $data['title']==='' ) ? ''		: '	title		= {'.$data['title'].'},'	//yesshue
		. ( !isset($data['subtitle'])		|| $data['subtitle']==='' ) ? ''	: '	subtitle	= {'.$data['subtitle'].'},'	//yesshue
//more than one subjects possible
		. ( !isset($data['authorname'])		|| $data['authorname']==='' ) ? ''	: '	author		= {'.$data['authorname'].'},'	//yep, as long as not unknown, also called creator for epub
//'author		= { [editor] },'
//contributor, role
//'keywords		= {},'
//'library		= {},'	//for e.g. sorting system/shelf etc.
//'address		= {},'	//addresses like places of production
//'booktitle		= {},'	//if in e.g. a row/collection of books, alike series but without order
		. ( !isset($data['subjecttitle'])	|| $data['subjecttitle']==='' ) ? '': '	subject		= {'.$data['subjecttitle'].'},'	//added
		. ( !isset($data['publisher'])		|| $data['publisher']==='' ) ? ''	: '	publisher	= {'.$data['publisher'].'},'	//rather not often
		. ( !isset($data['isbn'])			|| $data['isbn']==='' ) ? ''		: '	isbn		= {'.$data['isbn'].'},'	//maybe not, maybe a comma separated list
//other identifiers possible: MOBI-ASIN, GOOGLE, AMAZON, calibre, uuid_id
		. ( !isset($data['year'])			|| $data['year']==='' ) ? ''		: '	year		= {'.$data['year'].'},'	//most of times
		. ( !isset($data['language'])		|| $data['language']==='' ) ? 'en'	: '	language	= {'.$data['language'].'},'	// see http://www.i18nguy.com/unicode/language-identifiers.html ref. http://www.loc.gov/standards/iso639-2/langcodes.html
		. ( !isset($data['edition'])		|| $data['edition']==='' ) ? '' 	: '	edition		= {'.$data['edition'].'},'	//never seen
		. ( !isset($data['series'])			|| $data['series']==='' ) ? '' 		: '	series		= {'.$data['series'].'},'	//if then often in colophon
		. ( !isset($data['volume'])			|| $data['volume']==='' ) ? '' 		: '	volume		= {'.$data['volume'].'},'	//the number in series?
//description
//date
//rating
		. '	url			= {'.getRMLURL().'?document=view&id='.$data['id'].'}'	/* we always have, comma on last is optional */ //http://c3jemx2ube5v5zpg.onion/?document=view&id=1
	.'}';
}

// ============================================================================

function RMLviewdocument( $id, $print_on = true )
{
	$result = RMLfiresql( "SELECT handle,status,posted_on,subject_id,author_id,title,subtitle,year,\"unique\",keywords,copyright,teaser,downloads,(SELECT name FROM author WHERE id=document.author_id) AS authorname,(SELECT subject_name FROM subject WHERE id=document.subject_id) AS subjecttitle,(SELECT sort_name FROM author WHERE id=document.author_id) AS sort_name,(SELECT AVG(level) AS score FROM forum WHERE thread_id=document.id AND level > 0) AS score,(SELECT owner FROM subject WHERE id=document.subject_id) AS owner,(SELECT email FROM \"user\" WHERE handle=document.handle) AS mail FROM document WHERE id=".$id );
	$thisrow = pg_Fetch_Object( $result, 0 );
	$thishandle = $thisrow->handle;
	$thissubjectid = $thisrow->subject_id;
	$thisauthorid = $thisrow->author_id;
	$thissubtitle = $thisrow->subtitle;
	$thisstatus = $thisrow->status;
	$thisyear = $thisrow->year;
	$thisunique = $thisrow->unique;
	$thiskeywords = $thisrow->keywords;
	$thiscopyright = nl2br( $thisrow->copyright );
	$thisteaser = nl2br( $thisrow->teaser );
	$downloads = $thisrow->downloads;
	$thisauthor = $thisrow->authorname;
	$thissubject = $thisrow->subjecttitle;
	$letter = $thisrow->sort_name;
	$letter = $letter[0];
	$avgscore = round( $thisrow->score,2 );
	$reviewer = $thisrow->owner;
	$mail = $thisrow->mail;
	$posted = RMLfixdate( $thisrow->posted_on );

	$out = '';
	if($thissubtitle) {
		$out .= "\n".'<div class="Subtitle">'.$thissubtitle.'</div>';
	}

	if($thisyear == '0') {
		$thisyear = 'Unknown';
	}

	$out .= "\n"
		.'<table style="width:100%;font-size:12pt" cellspacing="0" cellpadding="0">
<tr valign="top" style="height:20px">
	<td style="width:150px" rowspan="20"><a href="./covers/cover'.$id.'.jpg"><img style="margin:0;width:150px;border-width:1px;border-style:solid" alt="Cover" src="./covers/cover'.$id.'"/></a></td>
	<td align="right">by : </td><td style="padding-left:10px"><b><a href="?author=view&amp;id='.$thisauthorid.( ( !isset($thisauthorid) || $thisauthorid=='' || $thisauthorid==0) ? '&amp;letter='.$letter : '' ).'"><big>'.$thisauthor.'</big></a></b></td>
</tr><tr valign="middle" style="height:20px">
	<td align="right">&nbsp; &nbsp; published :</td><td style="padding-left:10px"><b>'.$thisyear.'</b></td>
</tr><tr valign="middle" style="height:20px">
	<td align="right">subject :</td><td style="padding-left:10px"><b><a href="?subject=view&amp;id='.$thissubjectid.'">'.$thissubject.'</a></b></td>
</tr>';

	if( $thiskeywords ) {
		$out .= "\n".'<tr valign="middle" style="height:20px"><td align="right">keywords :</td><td style="padding-left:10px"><b>'.$thiskeywords.'</b></td></tr>';
	}

	$tablename = RMLgetactivetable( $id );
	$sql = RMLfiresql("SELECT sum(length(body)) as docsize,count(id) as doccount FROM $tablename WHERE doc_id=$id");
	$thisrow = pg_Fetch_Object( $sql, 0 );
	$docsize = $thisrow->docsize;
	$elementcount = $thisrow->doccount;

	$confirmed = $docsize;
	$filename = './covers/cover'.$id.'.jpg';
	if( file_exists( $filename ) ) {
		$coversize = sizeFormat( filesize( $filename ) );
	} else {
		$coversize = '-';
	}

	$out.= "\n".'<tr valign="middle" style="height:20px"><td align="right" valign="top">size :</td><td style="padding-left:10px">
<b>'.$coversize.'</b> in cover<br/>';

	$path = './pictures/'.$id;
	$ar = getDirectorySize( $path );

	if( $ar['count'] ) {
		$size = sizeFormat( $ar['size'] );
		$pictures = $ar['count'];
		$out .= '	<b>'.$size.'</b> in <b>'.$pictures.'</b> pictures<br/>';
	}

	$sql = RMLfiresql( "SELECT sum(length(body)) as notesize,count(id) as notecount FROM footnote WHERE docid=$id" );
	$thisrow = pg_Fetch_Object( $sql, 0 );
	$notesize = $thisrow->notesize;
	$notecount = $thisrow->notecount;

	if($notecount > 0) {
		$size = sizeFormat( $notesize );
		$confirmed += $notesize;
		$out .= '<b>' .$size .'</b> in <b>' .getNumberFormatted( $notecount, 0 ) .'</b> footnotes<br/>';
	}

	$pagecount = $confirmed / 2000 + $pictures;
	$docsize = sizeFormat( $docsize );

	$out .= '<b>'.$docsize.'</b> in <b>' .getNumberFormatted( $elementcount , 0 ).'</b> paragraphs<br/>
<b>~ ' .getNumberFormatted( $pagecount, 0 ) .'</b> pages<br/>
<b>' .getNumberFormatted( $downloads, 0 ).'</b> downloads</td></tr>
<tr valign="middle" style="height:20px"><td align="right">added by :</td><td style="padding-left:10px">
<b>'
.( ( $mail != '' ) ? '<a href="mailto:'.$mail.'">'.$thishandle.'</a>' : $thishandle )
.'</b> (<i>'.$posted.'</i>)</td></tr>';

$out .= "\n".'<tr style="height:30px"><td align="right" valign="middle">score :</td><td style="padding-left:10px">
<span class="left-float">' .getRatingDisplay( $avgscore ) .'</span> &nbsp; <b><big>'.$avgscore.'</big></b>
</td></tr>';

	$out .= "\n<tr><td>&nbsp;</td></tr></table>";
	$user = RMLgetcurrentuser();
	if( $thisstatus > 0		//book is published
		&& $user !== null	//and user logged in
	) {
		/* todo:	adding 404-handler so the webserver shows a nice error page when a document is not found 
		 * 			then this one can be used to serve the book if the requested filetype has the id and file extension we assume
		 * 			and this way filenames will work on all browsers even without considering the header (issue #105)
		 * */
		
		$out .= '<div class="center"><a class="button save" href="./?function=download&amp;id='.$id.'">Borrow Book</a></div>';
	} else if ( $user === null ) { // Anonymous downloads re enabled (jotunbane)
		$out .= '<div class="center"><a class="button star" href="./?function=download&amp;id='.$id.'">Borrow Book</a></div>';
	}

	$out .= '<div class="center">';
	if( $reviewer === $user && $thisstatus == 2 ) {
		$out .= "\n".'<a class="button save" href="?function=confirm&amp;id='.$id.'">Confirm</a>
<a class="button delete" href="?function=deny&amp;id='.$id.'">Deny</a>';
	}

	if( $thishandle === $user &&  $thisstatus < 3 ) {
		switch($thisstatus) {
		case '0':
			$out .= "\n".'<a class="button add" href="?function=upload&amp;id='.$id.'">Upload</a>
<a class="button edit" href="?document=edit&amp;id='.$id.'">Edit</a>
<a class="button delete" href="?function=delete&amp;id='.$id.'">Delete</a>';
		break;
		case '1':
			$out .= "\n".'<a class="button delete" href="?function=flush&amp;id='.$id.'">Flush</a>
<a class="button save" href="?function=publish&amp;id='.$id.'">Publish</a>
<a class="button edit" href="?document=edit&amp;id='.$id.'">Edit</a>
<a class="button delete" href="?function=delete&amp;id='.$id.'">Delete</a>';
		break;
		case '2':
			$out .= '&nbsp;<b>Awaiting review.</b>';
		break;
		}
	}

	if( ( ( $thishandle == RMLgetcurrentuser() ) || ( RMLgetcurrentuser() == 'admin') ) && ( $thisstatus == 3) ) {
		$out .= "\n<a class=\"button edit\" href=\"?document=edit&amp;id=$id\">Edit</a>"
			."\n<a class=\"button delete\" href=\"?function=withdraw&amp;id=$id\">Un-Publish</a>";
	}
	$out .= "</div>";

	$out .= "\n".'<div class="inlineclear">&nbsp;</div>
<p class="ParaNoIndent">'.$thisteaser.'</p>
<div class="inlineclear">&nbsp;</div>
<div class="box"><div class="boxheader"><b>Colophon</b></div><div class="boxtext"><small>'.$thiscopyright.'</small></div></div>'
		.RMLdisplaytoc( $id, false );
		if($user) { 
			$out .= RMLdisplaycomments( $id, false );
		}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaydocumentlocation( $print_on = true )
{
	global $id, $section, $document, $letter;

	$out = '';
	$result = RMLfiresql("SELECT subject_id,author_id,title,(SELECT name FROM author WHERE id=document.author_id) AS authorname,(SELECT sort_name FROM author WHERE id=document.author_id) AS sortname,(SELECT subject_name FROM subject WHERE id=document.subject_id) AS subjecttitle FROM document WHERE id=$id");
	if ( ! ( $thisrow = pg_Fetch_Object( $result, 0 ) ) ) {
		$out = 'ERROR: Document ID not valid in <code>displaydocumentlocation()</code>.';
	} else {
		$subid = $thisrow->subject_id;
		$authorid = $thisrow->author_id;
		$authorname = $thisrow->authorname;
		$thissortname = $thisrow->sortname;
		$myletter = $thissortname[0];
		$subtitle = $thisrow->subjecttitle;
		$doctitle =  $thisrow->title;

		if( $document == 'view' ) {
			$out .= "\n".'<a class="button next" href="?author=view&amp;letter='.$myletter.'">Authors</a>
	<a class="button next" href="?author=view&amp;id='.$authorid.'&amp;letter='.$myletter.'">'.$authorname.'</a>';
		}

		if($section) {
			$out .= "\n".'<a class="button next" href="?document=view&amp;id='.$id.'">'.$doctitle.'</a>';
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaydocumentupload( $id, $print_on = true )
{
	$out = "\n".'<form enctype="multipart/form-data" method="post" action="?function=import&amp;id='.$id.'">
ODT Content : 
<input type="file" size="49" name="content"><br/>
<input type="submit" value="Upload">
</form>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLimportdocument( $id, $print_on = true )
{
	$target_path = "./uploads/";
	$target_path = $target_path . basename( $_FILES['content']['tmp_name'] );

	$out = '';
	if( ! move_uploaded_file( $_FILES['content']['tmp_name'], $target_path ) ) {
		$out = "ERROR : Importing unknown file : $filename???";
	} else {
		$filename = basename( $_FILES['content']['name']);
		$out .= 'File uploaded : <b>'.$filename.'</b>"';

		$zip = zip_open( $target_path );

		do {
			$entry = zip_read( $zip );
		} while ( $entry && zip_entry_name( $entry ) != "content.xml" );

		zip_entry_open( $zip, $entry, "r" );
		$entry_content = zip_entry_read( $entry, zip_entry_filesize( $entry ) );

		zip_entry_close( $entry );
		zip_close( $zip );

		$xml = simplexml_load_string( $entry_content );
		$namespaces = $xml->getNameSpaces( true );
		$tablename = RMLgetactivetable( $id );

		$count = 0;
		$thisparent = 0;
		$thisid = 0;
		$footnoteid = 0;
		$mysection = 0;

		foreach( $xml->xpath( '//text:p' ) as $entry ) {
			foreach( $entry->attributes( $namespaces['text']) as $key=>$value ) {
				$texttype = RMLgettexttype( $value );
			}

			if( $texttype == 0 ) {
				$out .= "\n".'<br/><b>Unknown Element</b>: ".$value"<br/><b>Content</b>: '.$entry.'<br/>';
			}

			$myentry = RMLpreparexml( $entry->asXML() );
			$thisid++;

			if( $value == 'Picture' ) {
				$myentry = preg_replace( "@ @", "", $myentry );
				$myentry = preg_replace( "@<.*?>@", "", $myentry );
				RMLsavepicture( $id, $target_path, $myentry );
				$myentry = preg_replace( "@Pictures/@", "", $myentry );
				$myentry = '<img alt="Picture" src="./pictures/'.$id.'/'.$myentry.'">';
			}

			if( $value == 'Footnote' ) {
				$footnoteid++;
				$myentry = preg_replace( "@&lt;I&gt;@", "<i>", $myentry );
				$myentry = preg_replace( "@&lt;/I&gt;@", "</i>", $myentry);
				$myentry = preg_replace( "@&lt;SUB&gt;@", "<sub>", $myentry);
				$myentry = preg_replace( "@&lt;/SUB&gt;@", "</sub>", $myentry);
				$myentry = preg_replace( "@&lt;SUP&gt;@", "<sup>", $myentry);
				$myentry = preg_replace( "@&lt;/SUP&gt;@", "</sup>", $myentry);
				$myentry = preg_replace( "@&amp;ndash;@", "&ndash;", $myentry );
				RMLfiresql("INSERT INTO footnote (docid,id,section,body) VALUES($id,$footnoteid,$mysection,'$myentry')");
			}

			if( ( $texttype == 11 ) || ( $texttype == 12 ) || ( $texttype == 13 ) || ( $texttype == 14 ) || ( $texttype == 15 ) || ( $texttype == 16 ) ) {
				$thisparent = 0;
			}

			if( $texttype <> 19 ) { // exclude footnotes, handled above
				RMLfiresql( "INSERT INTO $tablename (doc_id,paragraphtype,body,id,parent_id) VALUES($id,$texttype,'$myentry',$thisid,$thisparent)" );
			}

			if( ( $texttype == 11 ) || ( $texttype == 12 ) || ( $texttype == 13 ) || ( $texttype == 14 ) || ( $texttype == 15 ) || ( $texttype == 16 ) ) {
				$thisparent = $thisid;
				$mysection++;
			}
		}

		RMLfiresql( "UPDATE document SET status=1 WHERE id=$id" );

		$sql = RMLfiresql( "SELECT sum(length(body)) as docsize FROM $tablename WHERE doc_id=$id" );
		$thisrow = pg_Fetch_Object( $sql, 0 );

		$sql = RMLfiresql( "SELECT sum(length(body)) as docsize FROM footnote WHERE docid=$id" );
		$notesize = pg_Fetch_Object( $sql, 0 );

		$out .= "\n".'<br/><br/>Added <b>' .getNumberFormatted( $thisid, 0 ) .'</b> elements to DataBase (<b>' .sizeFormat( $thisrow->docsize + $notesize->docsize ) .'</b>)<br/>
	<a class="button like" href="?document=view&amp;id='.$id.'">Lets see it</a>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLreaddocument( $id, $section, $print_on = true )
{
	$tablename = RMLgetactivetable( $id );

	$result = RMLfiresql( "SELECT id FROM $tablename WHERE doc_id=$id AND parent_id=0 AND paragraphtype <> 0 ORDER BY id" );
	if( pg_numrows( $result ) > 0 ) {
		$thisrow = pg_Fetch_Object( $result, $section - 1 );
		$thisparent = $thisrow->id;
	} else {
		$thisparent = 0;
	}

	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$id" );
	$thisowner = pg_Fetch_Object( $result, 0 );
	$owner = $thisowner->handle;

	$out = '';
	$result = RMLfiresql( "SELECT body,paragraphtype,id FROM $tablename WHERE doc_id=$id AND parent_id=$thisparent order by id" );
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisbody = $thisrow->body;
		$type = $thisrow->paragraphtype;
		$sequence = $thisrow->id;

		if( hasRights( 'editdocument', array( $owner ) ) ) {
			$out .= RMLdisplay( '<a id="s'.$sequence.'"/>'.$thisbody.' <small>[<a href="?function=edit&amp;id='.$id.'&amp;sequence=s'.$sequence.'&amp;section='.$section.'">Edit</a>]&nbsp;[<a href="?para=delete&amp;id='.$id.'&amp;section='.$section.'&amp;sequence=s'.$sequence.'">Delete</a>]</small>', $type, false );
		} else {
			$out .= RMLdisplay( '<a id="s'.$sequence.'"/>'.$thisbody, $type, false );
		}
	}
	$out .= "\n<br/>";
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLgetsectiontitle( $id, $section )
{
	$tablename = RMLgetactivetable( $id );
	$result = RMLfiresql( "SELECT body,id FROM $tablename WHERE doc_id=$id AND parent_id=0 AND paragraphtype<>0 ORDER BY id" );
	if( pg_numrows( $result ) > 0 ) {
		$thisrow = pg_Fetch_Object( $result, $section - 1 );
		$title = $thisrow->body;
		$sequence = $thisrow->id;
	} else {
		$title = "ERROR: (No sections)";
	}

	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$id" );
	$thisowner = pg_Fetch_Object( $result, 0 );
	$owner = $thisowner->handle;

	if( ( RMLgetcurrentuser() == 'admin' ) || ( RMLgetcurrentuser() == $owner ) ) {
		return $title.' <small>[<a href="?function=edit&amp;id='.$id.'&amp;section='.$section.'&amp;sequence=s'.$sequence.'">Edit</a>]</small>';
	} else {
		return $title;
	}
}

// ============================================================================

function RMLeditelement( $id, $print_on = true )
{
	global $sequence, $section;
	$out = '';
	$sequence = substr( $sequence, 1, strlen( $sequence ) - 1 );

	$table = RMLgetactivetable( $id );
	$result = RMLfiresql( "SELECT body,paragraphtype FROM $table WHERE doc_id=$id AND id=$sequence" );
	if ( ! ( $thisrow = pg_Fetch_Object( $result, 0 ) ) ) {
		$out = 'ERROR: No valid Document found in <code>editelement()</code>.';
	} else {
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$out .= RMLdisplay( $thisbody, $thistype, false );

		$out .= "\n".'<hr class="messageseperator">
<form method="post" action="?function=update&amp;id='.$id.'&amp;sequence=s'.$sequence.'&amp;section='.$section.'">
<table class="form">
<tr><td valign="top"><b>Texttype:</b></td>
<td><select class="norm" name="paragraphtype">';

		if( ( $thistype == 11 ) || ($thistype == 12) || ($thistype == 13) || ($thistype == 14) || ($thistype == 15) || ($thistype == 16) ) {
			if( $thistype <> 11 ) {
				$out .= "\n".'<option value="11">Part</option>';
			} else {
				$out .= "\n".'<option value="11" selected="yes">Part</option>';
			}
			if($thistype <> 12) {
				$out .= "\n<option value=\"12\">Book</option>";
			} else {
				$out .= "\n<option value=\"12\" selected=\"yes\">Book</option>";
			}
			if($thistype <> 13) {
				$out .= "\n<option value=\"13\">Chapter</option>";
			} else {
				$out .= "\n<option value=\"13\" selected=\"yes\">Chapter</option>";
			}
			if($thistype <> 14) {
				$out .= "\n<option value=\"14\">PartNoTOC</option>";
			} else {
				$out .= "\n<option value=\"14\" selected=\"yes\">PartNoTOC</option>";
			}
			if($thistype <> 15) {
				$out .= "\n<option value=\"15\">BookNoTOC</option>";
			} else {
				$out .= "\n<option value=\"15\" selected=\"yes\">BookNoTOC</option>";
			}
			if($thistype <> 16) {
				$out .= "\n<option value=\"16\">ChapterNoTOC</option>";
			} else {
				$out .= "\n<option value=\"16\" selected=\"yes\">ChapterNoTOC</option>";
			}
		} else {
			if($thistype <> 1) {
				$out .= "\n<option value=\"1\">Head1</option>";
			} else {
				$out .= "\n<option value=\"1\" selected=\"yes\">Head1</option>";
			}
			if($thistype <> 2) {
				$out .= "\n<option value=\"2\">Head2</option>";
			} else {
				$out .= "\n<option value=\"2\" selected=\"yes\">Head2</option>";
			}
			if($thistype <> 3) {
				$out .= "\n<option value=\"3\">Head3</option>";
			} else {
				$out .= "\n<option value=\"3\" selected=\"yes\">Head3</option>";
			}
			if($thistype <> 4) {
				$out .= "\n<option value=\"4\">ParaIndent</option>";
			} else {
				$out .= "\n<option value=\"4\" selected=\"yes\">ParaIndent</option>";
			}
			if($thistype <> 5) {
				$out .= "\n<option value=\"5\">ParaBlankOver</option>";
			} else {
				$out .= "\n<option value=\"5\" selected=\"yes\">ParaBlankOver</option>";
			}
			if($thistype <> 6) {
				$out .= "\n<option value=\"6\">QuoteIndent</option>";
			} else {
				$out .= "\n<option value=\"6\" selected=\"yes\">QuoteIndent</option>";
			}
			if($thistype <> 7) {
				$out .= "\n<option value=\"7\">QuoteBlankOver</option>";
			} else {
				$out .= "\n<option value=\"7\" selected=\"yes\">QuoteBlankOver</option>";
			}
			if($thistype <> 8) {
				$out .= "\n<option value=\"8\">ParaNoIndent</option>";
			} else {
				$out .= "\n<option value=\"8\" selected=\"yes\">ParaNoIndent</option>";
			}
			if($thistype <> 9) {
				$out .= "\n<option value=\"9\">QuoteNoIndent</option>";
			} else {
				$out .= "\n<option value=\"9\" selected=\"yes\">QuoteNoIndent</option>";
			}
			if($thistype <> 17) {
				$out .= "\n<option value=\"17\">PreBlankOver</option>";
			} else {
				$out .= "\n<option value=\"17\" selected=\"yes\">PreBlankOver</option>";
			}
			if($thistype <> 18) {
				$out .= "\n<option value=\"18\">PreNoIndent</option>";
			} else {
				$out .= "\n<option value=\"18\" selected=\"yes\">PreNoIndent</option>";
			}
			if($thistype <> 20) {
				$out .= "\n<option value=\"20\">Picture</option>";
			} else {
				$out .= "\n<option value=\"20\" selected=\"yes\">Picture</option>";
			}
			if($thistype <> 21) {
				$out .= "\n<option value=\"21\">TableStart</option>";
			} else {
				$out .= "\n<option value=\"21\" selected=\"yes\">TableStart</option>";
			}
			if($thistype <> 22) {
				$out .= "\n<option value=\"22\">TableCell</option>";
			} else {
				$out .= "\n<option value=\"22\" selected=\"yes\">TableCell</option>";
			}
			if($thistype <> 23) {
				$out .= "\n<option value=\"23\">TableRow</option>";
			} else {
				$out .= "\n<option value=\"23\" selected=\"yes\">TableRow</option>";
			}
			if($thistype <> 24) {
				$out .= "\n<option value=\"24\">TableEnd</option>";
			} else {
				$out .= "\n<option value=\"24\" selected=\"yes\">TableEnd</option>";
			}
			if($thistype <> 25) {
				$out .= "\n<option value=\"25\">ListStart</option>";
			} else {
				$out .= "\n<option value=\"25\" selected=\"yes\">ListStart</option>";
			}
			if($thistype <> 26) {
				$out .= "\n<option value=\"26\">ListItem</option>";
			} else {
				$out .= "\n<option value=\"26\" selected=\"yes\">ListItem</option>";
			}
			if($thistype <> 27) {
				$out .= "\n<option value=\"27\">ListEnd</option>";
			} else {
				$out .= "\n<option value=\"27\" selected=\"yes\">ListEnd</option>";
			}
			if($thistype <> 28) {
				$out .= "\n<option value=\"28\">OrderListStart</option>";
			} else {
				$out .= "\n<option value=\"28\" selected=\"yes\">OrderListStart</option>";
			}
			if($thistype <> 29) {
				$out .= "\n<option value=\"29\">OrderListItem</option>";
			} else {
				$out .= "\n<option value=\"29\" selected=\"yes\">OrderListItem</option>";
			}
			if($thistype <> 30) {
				$out .= "\n<option value=\"30\">OrderListEnd</option>";
			} else {
				$out .= "\n<option value=\"30\" selected=\"yes\">OrderListEnd</option>";
			}
			if($thistype <> 31) {
				$out .= "\n<option value=\"31\">HangingBlankOver</option>";
			} else {
				$out .= "\n<option value=\"31\" selected=\"yes\">HangingBlankOver</option>";
			}
			if($thistype <> 32) {
				$out .= "\n<option value=\"32\">HangingIndent</option>";
			} else {
				$out .= "\n<option value=\"32\" selected=\"yes\">HangingIndent</option>";
			}
			if($thistype <> 33) {
				$out .= "\n<option value=\"33\">ParaVignet</option>";
			} else {
				$out .= "\n<option value=\"33\" selected=\"yes\">ParaVignet</option>";
			}
			if($thistype <> 34) {
				$out .= "\n<option value=\"34\">BoxStart</option>";
			} else {
				$out .= "\n<option value=\"34\" selected=\"yes\">BoxStart</option>";
			}
			if($thistype <> 35) {
				$out .= "\n<option value=\"35\">BoxEnd</option>";
			} else {
				$out .= "\n<option value=\"35\" selected=\"yes\">BoxEnd</option>";
			}
			$out .= '<option value="36"' . (($thistype == 36)?' selected="yes"':'') .'>BoxHead</option>';

		}
		$out .= "\n".'</select>
</td></tr>
<tr><td valign="top"><b>Contents:</b></td>
<td><textarea class="norm" rows="12" cols="41" wrap="none" name="body">'.$thisbody.'</textarea>
</td></tr>
<tr><td></td><td><input type="submit" value="Update element"></td></tr></table>
</form>';
}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLupdateelement( $id )
{
	global $body, $sequence, $paratype;

	$sequence = substr( $sequence, 1, strlen( $sequence ) );
	$table = RMLgetactivetable( $id );
	RMLfiresql( "UPDATE $table SET body='$body', paragraphtype=$paratype WHERE doc_id=$id AND id=$sequence" );
}

// ============================================================================

function RMLflushdocument( $id )
{
	$owner = RMLgetdocumentowner( $id );

	if( RMLgetcurrentuser() == $owner ) {
		$table = RMLgetactivetable( $id );
		RMLfiresql( "DELETE FROM $table WHERE doc_id=$id" );
		RMLfiresql( "DELETE FROM footnote where docid=$id" );
		RMLfiresql( "UPDATE document SET status=0 WHERE id=$id" );
		del_dir( './pictures/'.$id );
	}
}

// ============================================================================

function RMLgetdocumentowner( $documentid )
{
	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$documentid" );
	$thisowner = pg_Fetch_Object( $result, 0 );
	$owner = $thisowner->handle;

	return $owner;
}

// ============================================================================

function RMLpublishdocument( $id )
{
	//todo: merge queries
	$result = RMLfiresql("SELECT handle,subject_id FROM document WHERE id=$id");
	$thisowner = pg_Fetch_Object($result,0);
	$owner = $thisowner->handle;
	$subject_id = $thisowner->subject_id;

	$result = RMLfiresql("SELECT owner FROM subject WHERE id=$subject_id");
	$thisowner = pg_Fetch_Object($result,0);
	$subject_owner = $thisowner->owner;

	if(RMLgetcurrentuser() == $owner) {
		$table = RMLgetactivetable($id);

		$result = RMLfiresql("SELECT author_id FROM document WHERE id=$id");
		$thisrow = pg_Fetch_Object($result,0);
		$target = $thisrow->author_id;

		$result = RMLfiresql("SELECT body,paragraphtype,id,parent_id FROM $table WHERE doc_id=$id");
		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisbody = RMLpreparestring($thisrow->body);
			$thistype = $thisrow->paragraphtype;
			$thisid = $thisrow->id;
			$thisparent = $thisrow->parent_id;

			RMLfiresql("INSERT INTO author$target VALUES($id,$thistype,'$thisbody',$thisid,$thisparent)");
		}

		RMLfiresql("DELETE FROM $table WHERE doc_id=$id");
		RMLfiresql("UPDATE \"document\" SET status=2, posted_on=NOW() WHERE id=$id");
		RMLsendconfirmmessage( $id, $owner, $subject_owner );
	}
}

// ============================================================================

function RMLsendconfirmmessage( $id, $owner, $subject_owner )
{
	if ( hasRights( 'selfpublish' ) || $subject_owner == $owner ) {
		RMLconfirmdocument( $id );
	} else {//just publish
		$title = RMLgetdocumenttitle( $id );
		$title = preg_replace( "@'@", "", $title );
		$message = "The document <a href=\"?document=view&amp;id=$id\"><i>$title</i></a> was submitted for review. As the maintainer for this subject, please review it and either confirm it as a working document, or send it back to <i>$owner</i>. If it takes longer than " .getMaxReviewDays() ." days, the submitter is allowed to publish wothout your consent.";
		RMLsendmessage( $subject_owner, $message, $owner, 'Review document', true );
		$message = "The document <a href=\"?document=view&amp;id=$id\"><i>$title</i></a> was submitted for review. The maintainer <i>$subject_owner</i> will review it and either confirm it as a working document, or send it back to you.";
		RMLsendmessage( $owner, $message, 'SYSTEM', 'Document in review', true );
	}
}

// ============================================================================

function RMLdeletedocument( $id )
{
	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$id" );
	if( ! $result ) {
		$out = 'ERROR: No such document: #'.$id;
	} else { 
		$thisrow = pg_Fetch_Object( $result, 0 );
		$owner = $thisrow->handle;
		if( ! hasRights( 'deldocument', array( $owner ) ) ) {
			$out = 'ERROR: Action not permitted: Delete document.';
		} else {
			RMLfiresql("DELETE FROM document WHERE id=$id");
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdownloaddocument( $id, $format = 'epub' )
{
	switch( $format ) {
		case 'txt':
			RMLexporttxt( $id );
		break;
		case 'html':
			RMLexporthtml( $id );
		break;
		case 'epub':
			RMLexportepub( $id );
		break;
		case 'markdown':
			RMLexportmd( $id );
		break;
		default:
			die( 'ERROR: wrong format' );
	}
}

// ============================================================================

function RMLconfirmdocument( $id )
{
	// TODO : Check validity of request.
	RMLfiresql( "UPDATE document SET status=3, posted_on=NOW() WHERE id=$id" );
}

// ============================================================================

function RMLdenydocument( $id )
{
	// TODO
	//RMLfiresql("INSERT INTO message (id,posted_on,handle,subject,body,sender_handle) VALUES (DEFAULT,NOW(),'$owner','Document rejected','$message','SYSTEM')");
	//RMLsendmessage( $username, $welcomemessage, 'SYSTEM', 'Welcome '.$username, false );
}

// ============================================================================

function RMLwithdrawdocument( $id, $print_on = true ) {
	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$id" );
	$thisowner = pg_Fetch_Object( $result, 0 );
	$owner = $thisowner->handle;

	if( hasRights( 'deldocument', array( $owner ) ) ) {
		$table = RMLgetactivetable($id);

		$result = RMLfiresql("SELECT body,paragraphtype,id,parent_id FROM $table WHERE doc_id=$id");
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisbody = RMLpreparestring( $thisrow->body );
			$thistype = $thisrow->paragraphtype;
			$thisid = $thisrow->id;
			$thisparent = $thisrow->parent_id;

			RMLfiresql("INSERT INTO sandbox VALUES($id,$thistype,'$thisbody',$thisid,$thisparent)");
		}

		RMLfiresql( "DELETE FROM $table WHERE doc_id=$id" );
		RMLfiresql( "UPDATE \"document\" SET status=1, posted_on=NOW() WHERE id=$id" );
	} else {
		$out = 'ERROR: Action not permitted.';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================
// by kittyhawk <waqar1445@GMAIL.COM>
function RMLdeleteelement( $id )
{
	global $sequence;

	$out = '';
	$table_name = RMLgetactivetable( $id );
	$sequence = substr( $sequence, 1, strlen( $sequence ) );

	$result = RMLfiresql( "SELECT handle FROM document WHERE id=$id" );
	$thisrow = pg_Fetch_Object( $result, 0 );
	$owner = $thisrow->handle;
	if( ! hasRights( 'editdocument', array( $owner ) ) ) {
		$out = "ERROR : Not your document ...";
	} else {

		$result = RMLfiresql("SELECT body,paragraphtype FROM $table_name WHERE doc_id=$id AND id=$sequence");
		$thisrow = pg_Fetch_Object($result,0);
		$paratype = $thisrow->paragraphtype;
		$body = $thisrow->body;

		if($paratype == 20) { // if the element is type "picture"
			$body = substr( $body, 24, strlen( $body ) );
			$body = substr( $body, 0, strlen( $body ) - 2 );
			unlink( $body ); // remove the file
		// this step is strictly speaking not required.
		// the picture will not be included in the epub unless it has a matching "picture" element
		}
		RMLfiresql("DELETE FROM $table_name WHERE doc_id=$id AND id=$sequence");

	// Should not be necessary and will clearly not work like this (Jotunbane)
	//	InfoCOMfiresql("UPDATE $table_name SET id=id-1 WHERE doc_id=$id AND id>$sequence AND (paragraphtype < 10 OR paragraphtype > 16)");

		//TODO: deal with pictures...
	}
	return processOutput( $out, $print_on );
}

// ============================================================================
// by kittyhawk <waqar1445@GMAIL.COM>
function RMLdisplayimageupload( $print_on = true )
{
	global $id, $sequence, $section;
	$out = "\n".'<form enctype="multipart/form-data" method="post" action="?function=upload_image&amp;id='.$id.'&amp;sequence='.$sequence.'&amp;section='.$section.'">
<label for="picture">Picture : </label><input type="file" size="49" name="picture"><br/>
<input type="submit" value="Upload">
</form>';
	return processOutput( $out, $print_on );
}

// ============================================================================
// by kittyhawk <waqar1445@GMAIL.COM>
function RMLsaveimage()
{
	global $id, $sequence;

	$table_name = RMLgetactivetable( $id );
	$sequence = substr( $sequence, 1, strlen( $sequence ) );

	$picname = '10000' .$id.$id.$id;
	$target_path = './pictures/' .$id .'/' .$picname;
	if( move_uploaded_file( $_FILES['picture']['tmp_name'], $target_path .'.jpg' ) ) {
		$myimage = new RMLimage();
		$myimage->load( $target_path .'.jpg' );
		$myimage->save( $target_path );
	}
	$dbentry = '<img alt="Picture" src="./pictures/'.$id.'/' . $picname . '">';
	RMLfiresql( "UPDATE $table_name SET body='$dbentry' WHERE doc_id=$id AND id=$sequence" );
}

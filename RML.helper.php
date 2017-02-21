<?php
/*============================================================================
// Helper functions for Radical Militant Library
// Copyright (C) 2009-2016 Jotunbane
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// ============================================================================*/

function RMLgetpagetitle()
{
	global $function, $subject, $static, $message, $document, $author,
		$id, $section, $sequence, $news, $footnote, $note, $style;

	$title = "Radical Militant Library";

	switch( $author ) {
	case 'view':
		$authorname = RMLgetauthorname( $id );
		$title = "$authorname";
	break;
	}

	switch( $document ) {
	case 'view':
		$docname = RMLgetdocumenttitle( $id );
		if($section) {
			$secname = RMLgetsectiontitle( $id, $section );
			$secname = strip_tags( $secname );
			$title = $docname.' : '.$secname;
		} else {
			$title = $docname;
		}
	break;
	}

	switch( $function ) {
	case 'upload':
		$docname = RMLgetdocumenttitle( $id );
		$title = "Upload '$docname'";
	break;
	case 'edit':
		$title = "Edit Element";
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
		$title = "$title";
	break;
	}

	switch( $news ) {
	case 'view':
		$title = "Radical Militant News";
	break;
	case 'add':
		$title = "Add Radical Militant news";
	break;
	case 'edit':
		$title = "Edit Radical Militant news";
	break;
	}

	switch( $footnote ) {
	case 'view':
		$title = RMLgetdocumenttitle( $id );
		$title = $title . " : Footnote $note";
	break;
	}

	switch( $style ) {
	case 'new':
		$title = "New Stylesheet";
	break;
	case 'edit':
		$title = "Edit Stylesheet";
	break;
	}

	return "$title";
}

// ============================================================================
/*
function RMLgetsubjecttitle( $id ) {
	if( $id <> 0 ) {
		$result = RMLfireSQL( "SELECT subject_name FROM subject WHERE id=$id" );
		$thisrow = pg_Fetch_Object( $result, 0 );
		$thissubject = $thisrow->subject_name;
		return "$thissubject";
*/
function RMLgetsubjecttitle( $id )
{
	if( is_numeric( $id ) && $id != 0 ) {
		if ( $result = RMLfireSQL( "SELECT subject_name FROM subject WHERE id=$id" ) ) {
			$thisrow = pg_Fetch_Object( $result, 0 );
			$thissubject = $thisrow->subject_name;
			return $thissubject;
		}
	} else {
		return 'Radical Militant Subjects';
	}
}

// ============================================================================

function RMLgetauthorname( $id )
{
	if( $id == 0 ) {
		return "Radical Militant Authors";
	}
	if ( $result = RMLfireSQL( "SELECT name FROM author WHERE id=$id" ) ) {
		$thisrow = pg_Fetch_Object( $result, 0 );
		return $thisrow->name;
	} else {
		return 'Invalid Author ID: '.$id;
	}
}

// ============================================================================

function RMLgetdocumenttitle( $id, $print_on = false )
{
	$result = RMLfiresql("SELECT title FROM document WHERE id=$id");
	if( !( $thisrow = pg_Fetch_Object( $result, 0 ) ) ){
		$out = 'ERROR: No Title for Document ID.';
	} else {
		$out = $thisrow->title;
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLfixdate( $date, $f = 'DD MMM YYYY' )
{
	setTimeZone();
	$date = strtotime( $date );
	switch ( $f ) {
		case 'DD MMM YYYY':
			return strftime( '%d %b %Y', $date );
		case 'U':// seconds since January 1st 1970
			return strftime( '%s', $date );			
		break;
		default:
			return false;
	}
}

// ============================================================================

function RMLgetsubjectid( $subject )
{
	$result = RMLfiresql( "SELECT id FROM subject WHERE subject_name='$subject'" );
	if( pg_numrows( $result ) > 0 ) {
		$tmp = pg_Fetch_Object( $result );
		$result = $tmp->id;
		return $result;
	} else {
		$result = RMLcreatesubject( $subject );
		return $result;
	}
}
// ============================================================================

function RMLcreatesubject( $subject )
{
	$user = RMLgetcurrentuser();
	RMLfiresql( "INSERT INTO subject (id,owner,subject_name) values(DEFAULT,'$user','$subject')" );
	return $subjectid;//ewa: looks like a bug, doubt it returns the id -> todo: check
}

// ============================================================================

function RMLgetauthorid($author)
{
	$result = RMLfiresql( "SELECT id FROM author WHERE name='$author'" );
	if( pg_numrows( $result ) > 0 ) {
		$tmp = pg_Fetch_Object( $result );
		$result = $tmp->id;
	} else {
		$result = RMLcreateauthor( $author );
	}
	return $result;
}

// ============================================================================

function RMLgetdocumentauthorname( $docid )
{
	$sql = RMLfiresql( "SELECT author_id FROM document WHERE id=$docid" );
	$thisrow = pg_Fetch_Object( $sql, 0 );
	$thisid = $thisrow->author_id;
	return RMLgetauthorname( $thisid );
}

// ============================================================================

function RMLcreateauthor( $author )
{
	$user = RMLgetcurrentuser();

	RMLfiresql( "INSERT INTO author (id,name,maintainer) values(DEFAULT,'$author','$user')" );
	return $authorid;//ewa: next bug; todo: check
}

// ============================================================================

function RMLpreparestring( $string )
{
	$search = array ( "/'/", '@&#(\d+);@e' );
	$replace = array ( "''", 'chr(\1)' );

	$result = preg_replace( $search, $replace, $string );
	// replace without regex
	//$result = str_replace(array('$','"','{','}'),'',$result);
	//$result = str_replace(array('/','%'),'-',$result);

	$result = strip_tags( $result, "<b><i><emph><a><br><img><sup><sub><ol><ul><li>" );
	return $result;
}

// ============================================================================

function RMLgetuniqueid()
{
	setTimeZone();
	$a = localtime();

	$dayofmonth = $a[3];
	$hour = $a[2];
	$minute = $a[1];
	$year = $a[5] + 1900;
	$month = $a[4] + 1;

	$secondtoday = $dayofmonth * 86400 + $hour * 3600 + $minute * 60 + $a[0];
	$secondtoday = strtoupper( base_convert( $secondtoday, 10, 16 ) );

	if(strlen($month) == 1) {
		$month = '0' . $month;
	}

	$user = RMLgetcurrentuser();

	return "$user.$year.$month.$secondtoday";;
}

// ============================================================================

function RMLpreparexml( $string )
{
	global $id;

	$search = array ('@<text:span text:style-name=".*?">@',
					 '@</text:span>@',
					 "@\\n@",
					 "@--@",
					 '@<text:bookmark.*?>@',
					 "@<text:line-break/>@",
					 '@<text:a xlink:type="simple" xlink:href="(.*?)">@',
					 "@</text:a>@",
					 '@<text:p.*?>@',
					 '@</text:p>@',
					 '@<text:tab/>@',
					 "@'@",
					 "@<text:note .*?>@",
					 "@<text:note-citation>(.*?)</text:note-citation>@",
					 "@<text:note-body>.*?</text:note>@",
					 "@<draw:frame.*?>@",
					 "@</draw:frame>@",
					 "@<draw:image xlink:href=\"(.*?)\".*?>@");

	$replace = array('<i>',
					 '</i>',
					 '',
					 '&ndash;',
					 '',
					 "<br />",
					 "<a href='\\1'>",
					 "</a>",
					 '',
					 '',
					 '',
					 "''",
					 "",
					 "[<a href=\"?footnote=view&amp;id=$id&amp;note=\\1\">\\1</a>]",
					 "",
					 "",
					 "",
					 "\\1");

	$result = preg_replace($search,$replace,$string);

	return $result;
}

// ============================================================================

function RMLpreparetxt($string)
{
	$search = array('@<i>@',
					 '@</i>@',
					 "@<br />@");

	$replace = array("_",
					 "_",
					 "\n");

	$result = preg_replace($search,$replace,$string);

	return $result;
}

// ============================================================================

function RMLpreparehtml( $string )
{
	$search = array('@ @',
					 '@<a href="\?footnote=view&id=.*?&note=(.*?)">@',
					 '@<a href="\?footnote=view&amp;id=.*?&amp;note=(.*?)">@',
					 '@<text:soft-page-break/>@',
					 '@­@', // Probably a hyphenation point (remove)
					 '@ "@',
					 '@^"@',
					 '@\("@',
					 '@"@',
					 // HTML accents begin (do not touch)
					 '@Á@',// 1
					 '@á@',
					 '@À@',
					 '@Â@',
					 '@à@',
					 '@Â@',
					 '@â@',
					 '@Ä@',
					 '@ä@',
					 '@Ã@',// 10
					 '@ã@',
					 '@Å@',
					 '@å@',
					 '@Æ@',
					 '@æ@',
					 '@Ç@',
					 '@ç@',
					 '@Ð@',
					 '@ð@',
					 '@É@',// 20
					 '@é@',
					 '@È@',
					 '@è@',
					 '@Ê@',
					 '@ê@',
					 '@Ë@',
					 '@ë@',
					 '@Í@',
					 '@í@',
					 '@Ì@',// 30
					 '@ì@',
					 '@Î@',
					 '@î@',
					 '@Ï@',
					 '@ï@',
					 '@Ñ@',
					 '@ñ@',
					 '@Ó@',
					 '@ó@',
					 '@Ò@',// 40
					 '@ò@',
					 '@Ô@',
					 '@ô@',
					 '@Ö@',
					 '@ö@',
					 '@Õ@',
					 '@Õ@',
					 '@Ø@',
					 '@ø@',
					 '@ß@',// 50
					 '@Þ@',
					 '@þ@',
					 '@Ú@',
					 '@ú@',
					 '@Ù@',
					 '@ù@',
					 '@Û@',
					 '@û@',
					 '@Ü@',
					 '@ü@',// 60
					 '@Ý@',
					 '@ý@',
					 '@ÿ@',
					 '@©@',
					 '@®@',
					 '@™@',
					 '@€@',
					 '@¢@',
					 '@£@',// 70
					 '@"@',
					 '@‘@',
					 '@’@',
					 '@“@',
					 '@”@',
					 '@«@',
					 '@»@',
					 '@—@',
					 '@–@',
					 '@°@',// 80
					 '@±@',
					 '@¼@',
					 '@½@',
					 '@¾@',
					 '@×@',
					 '@÷@',
					 '@α@',
					 '@β@',
					 '@∞@',
					 // HTML accents end
					 '@<a href=&#8221;(.*?)&#8221;>@'
					);

	$replace = array("&nbsp;",
					 "<a href=\"note\\1\">",
					 "<a href=\"note\\1\">",
					 "",
					 "", // Hypenation point. remove.
					 " &#8220;",
					 "&#8220;",
					 "(&#8220;",
					 "&#8221;",
					 // HTML accents begin (do not change)
					 "&Aacute;",// 1
					 "&aacute;",
					 "&Agrave;",
					 "&Acirc;",
					 "&agrave;",
					 "&Acirc;",
					 "&acirc;",
					 "&Auml;",
					 "&auml;",
					 "&Atilde;",// 10
					 "&atilde;",
					 "&Aring;",
					 "&aring;",
					 "&AElig;",
					 "&aelig;",
					 "&Ccedil;",
					 "&ccedil;",
					 "&Eth;",
					 "&eth;",
					 "&Eacute;",// 20
					 "&eacute;",
					 "&Egrave;",
					 "&egrave;",
					 "&Ecirc;",
					 "&ecirc;",
					 "&Euml;",
					 "&euml;",
					 "&Iacute;",
					 "&iacute;",
					 "&Igrave;", // 30
					 "&igrave;",
					 "&Icirc;",
					 "&icirc;",
					 "&Iuml;",
					 "&iuml;",
					 "&Ntilde;",
					 "&ntilde;",
					 "&Oacute;",
					 "&oacute;",
					 "&Ograve;", // 40
					 "&ograve;",
					 "&Ocirc;",
					 "&ocirc;",
					 "&Ouml;",
					 "&ouml;",
					 "&Otilde;",
					 "&otilde;",
					 "&Oslash;",
					 "&oslash;",
					 "&szlig;", // 50
					 "&Thorn;",
					 "&thorn;",
					 "&Uacute;",
					 "&uacute;",
					 "&Ugrave;",
					 "&ugrave;",
					 "&Ucirc;",
					 "&ucirc;",
					 "&Uuml;",
					 "&uuml;", // 60
					 "&Yacute;",
					 "&yacute;",
					 "&yuml;",
					 "&copy;",
					 "&reg;",
					 "&trade;",
					 "&euro;",
					 "&cent;",
					 "&pound;", // 70
					 "&quot;",
					 "&#8216;",
					 "&#8217;",
					 "&#8220;",// was &ldquo;
					 "&#8221;",// was &rdquo;
					 "&#8221;",
					 "&#8220;",
					 "&mdash;",
					 "&ndash;",
					 "&deg;", // 80
					 "&plusmn;",
					 "&frac14;",
					 "&frac12;",
					 "&frac34;",
					 "&times;",
					 "&divide;",
					 "&alpha;",
					 "&beta;",
					 "&infin;",
					 // HTML accents end
					 "<a id=\"\\1\" href=\"\\1.html\">"
					);

	$result = preg_replace($search,$replace,$string);

	return $result;
}

// ============================================================================

function RMLgetactivetable( $id, $print_on = false )
{
	$out = '';
	$result = RMLfiresql("SELECT handle,status,author_id FROM document WHERE id=$id");
	if( ! ( $thisrow = pg_Fetch_Object($result,0) ) ) {
		$out = 'ERROR: No valid Document in <code>RMLgetactivetable()</code>.';
	} else {
		$handle = $thisrow->handle;
		$status = $thisrow->status;
		$author_id = $thisrow->author_id;
	}
	if( $status > 1 ) {
		$out = 'author' . $author_id;
	} else {
		$out = 'sandbox';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLsavepicture($docid,$odtfile,$picturepath)
{
	$zip = zip_open($odtfile);

	while ($entry = zip_read($zip)) {
		$thisname = zip_entry_name($entry);
		if($thisname == $picturepath) {
			zip_entry_open($zip,$entry);
			$picture = zip_entry_read($entry, zip_entry_filesize($entry));
			zip_entry_close($entry);

			$filename = basename($picturepath);

			if(!is_dir("./pictures/$docid")) {
				mkdir("./pictures/$docid",0777);
			}

			$fp = fopen("./pictures/$docid/" . $filename, 'w');
			fwrite($fp,$picture);
			fclose($fp);
		}
	}
	zip_close($zip);
}

// ============================================================================

function RMLgettexttype( $typename, $reverse = false )
{
	//todo: enable lookup and reverse, then use this in e.g. RMLeditelement
	/* new style with array
	$a[1] = 'Head1';
	$a[2] = 'Head2';
	$a[3] = 'Head3';
	$a[4] = 'ParaIndent';
	$a[5] = 'ParaBlankOver';
	$a[6] = 'QuoteIndent';
	$a[7] = 'QuoteBlankOver';
	$a[8] = 'ParaNoIndent';
	$a[9] = 'QuoteNoIndent';
	//$a[10] = '';
	$a[11] = 'Part';
	$a[12] = 'Book';
	$a[13] = 'Chapter';
	$a[14] = 'PartNoTOC';
	$a[15] = 'BookNoTOC';
	$a[16] = 'ChapterNoTOC';
	$a[17] = 'ParaPreBlankOver';
	$a[18] = 'ParaPreNoIndent';
	$a[19] = 'Footnote';
	$a[20] = 'Picture';
	$a[21] = 'TableStart';
	$a[22] = 'TableCell';
	$a[23] = 'TableRow';
	$a[24] = 'TableEnd';
	$a[25] = 'ListStart';
	$a[26] = 'ListItem';
	$a[27] = 'ListEnd';
	$a[28] = 'OrderListStart';
	$a[29] = 'OrderListItem';
	$a[30] = 'OrderListEnd';
	$a[31] = 'HangingBlankOver';
	$a[32] = 'HangingIndent';
	$a[33] = 'ParaVignet';
	$a[34] = 'BoxStart';
	$a[35] = 'BoxEnd';
	$a[36] = 'BoxHead';/ ** /
	//$a[37] = '';
	//if ( !$reverse ) $a = array_flip( $a );
	//return $a[$typename];
	$p[11] = array('navpoint');
	$p[12] = array('navpoint');
	$p[13] = array('navpoint');
	/**/
	//old style
	switch( $typename ) {
	case 'Head1':		$result = 1;	break;
	case 'Head2':		$result = 2;	break;
	case 'Head3':		$result = 3;	break;
	case 'ParaIndent':	$result = 4;	break;
	case 'ParaBlankOver':	$result = 5;	break;
	case 'QuoteIndent':	$result = 6;	break;
	case 'QuoteBlankOver':	$result = 7;	break;
	case 'ParaNoIndent':	$result = 8;	break;
	case 'QuoteNoIndent':	$result = 9;	break;
			//10 missing, fill in new single entry here
	case 'Part':		$result = 11;	break;
	case 'Book':		$result = 12;	break;
	case 'Chapter':		$result = 13;	break;
	case 'PartNoTOC':	$result = 14;	break;
	case 'BookNoTOC':	$result = 15;	break;
	case 'ChapterNoTOC':	$result = 16;	break;
	case 'ParaPreBlankOver':$result = 17;	break;
	case 'ParaPreNoIndent':	$result = 18;	break;
	case 'Footnote':	$result = 19;	break;
	case 'Picture':		$result = 20;	break;
	case 'TableStart':	$result = 21;	break;
	case 'TableCell':	$result = 22;	break;
	case 'TableRow':	$result = 23;	break;
	case 'TableEnd':	$result = 24;	break;
	case 'ListStart':	$result = 25;	break;
	case 'ListItem':	$result = 26;	break;
	case 'ListEnd':		$result = 27;	break;
	case 'OrderListStart':	$result = 28;	break;
	case 'OrderListItem':	$result = 29;	break;
	case 'OrderListEnd':	$result = 30;	break;
	case 'HangingBlankOver':$result = 31;	break;
	case 'HangingIndent':	$result = 32;	break;
	case 'ParaVignet':	$result = 33;	break;
	case 'BoxStart':	$result = 34;	break;
	case 'BoxEnd':		$result = 35;	break;
	case 'BoxHead':		$result = 36;	break;
	default:		$result = 0;	break;
	}

	return $result;
}

// ============================================================================

function RMLgetprevlink()
{
	global $id, $document, $section, $page, $subject;

	switch($document) {
		case 'view':
			if($section > 0) {
				$prevsection = $section - 1;
				$result = "?document=view&amp;id=$id&amp;section=$prevsection";
			}
		break;
	}

	switch($subject) {
		case 'view':
			if($page > 1) {
				$tmp = $page - 1;
				$result = "?subject=view&amp;id=$id&amp;page=$tmp";
			}
		break;
	}
	return $result;
}

// ============================================================================

function RMLgetnextlink( $print_on = false )
{
	global $id, $document, $section, $page, $subject;

	if( $document == 'view' ) {
		$tablename = RMLgetactivetable( $id );
		$sql = RMLfiresql( "SELECT COUNT(id) as counter FROM $tablename WHERE parent_id=0 and doc_id=$id" );
		if( ! ( $thisrow = pg_Fetch_Object( $sql, 0 ) ) ) {
			$out = 'ERROR: No valid dacument in <code>getnextlink()</code>.';
		} else {
			$maxsection = $thisrow->counter;

			if($section > 0) {
				if($section < $maxsection) {
					$nextsection = $section + 1;
					$result = '?document=view&amp;id='.$id.'&amp;section='.$nextsection;
				}
			} else {
				$result = '?document=view&amp;id='.$id.'&amp;section=1';
			}
		}
	} elseif( $subject == 'view' ) {
		if( !isset($page) || $page === '' || $page === 0 ) {
			$page = 1;
		}
		if( $page < ( RMLgetmaxpage() - 1 ) ) {
			$result = '?subject=view&amp;id='.$id.'&amp;page='.( $page + 1 );
		}
	}

	$out = $result;
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLgetuplink()
{
	global $document, $section, $letter, $author, $id, $subject;

	if ( $document == 'view') {
		if( $section ) {
			$result = '?document=view&amp;id='.$id;
		} else {
			$sql = RMLfiresql( "SELECT author_id,(SELECT sort_name FROM author WHERE id=document.author_id) AS sort_name FROM document WHERE id=$id" );
			$thisrow = pg_Fetch_Object( $sql, 0 );
			$authorid = $thisrow->author_id;
			$thissortname = $thisrow->sort_name;
			$myletter = $thissortname[0];
			$result = '?author=view&amp;id='.$authorid.'&amp;letter='.$myletter;
		}
	}

	if( $subject == 'view' && $id > 0 ) {
		$result = '?subject=view&amp;id=0&amp;letter='.$letter;
	}

	if( $author == 'view' && $id > 0 ) {
		$result = '?author=view&amp;letter='.$letter;
	}
	return $result;
}

// ============================================================================

function RMLdisplaytoc( $id, $print_on = true )
{
	$out = '';
	$tablename = RMLgetactivetable( $id );

	$sql = RMLfiresql( "SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id AND parent_id=0 ORDER BY id" );
	if( pg_numrows( $sql ) > 0 ) {
		$out .= "\n".'<div class="box"><div class="boxheader"><b>Contents</b></div>';
	}

	$out .= "\n".'<div class="boxtext">';
	for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
		$thisrow = pg_Fetch_Object( $sql, $row );
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;
		if( ( $thistype == 11 ) || ( $thistype == 12 ) || ( $thistype == 13 ) ) {
			$target = $row + 1;

			if( $thistype == 11 ) {
				$out .= "\n<br/>&nbsp;&nbsp;<b>";
			}
			if( $thistype == 12 ) {
				$out .= "&nbsp;&nbsp;";
			}
			if( $thistype == 13 ) {
				$out .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			}

			$out .= "\n<a href=\"?document=view&amp;id=$id&amp;section=$target\">$thisbody</a><br/>";

			if( $thistype == 11 ) {
				$out .= "</b>";
			}
		}
	}
	$out .= "\n</div></div>";

	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLexporttxt( $id, $print_on = true )
{
	global $Version;

	$out = '';
	$tablename = RMLgetactivetable($id);
	$title = RMLgetdocumenttitle($id);

	$result = RMLfiresql("SELECT author_id,copyright FROM document WHERE id=$id");
	$thistmp = pg_Fetch_Object($result,0);
	$authorid = $thistmp->author_id;
	$copyright = $thistmp->copyright;
	$author = RMLgetauthorname($authorid);

	$thistitle = preg_replace("@ @","_",$title);

	if ( ! ( $file = gzopen("./output/$thistitle.txt.gz",'w') ) ) {
		$out = "ERROR : Cant open file for writing...";
	} else {
		gzwrite($file,strtoupper($title));
		gzwrite($file,"\n\nby \n$author");

		setTimeZone();
		$now = time();
		$now = strftime('%d %b %Y %H:%M',$now);

		$style = "\n\n  '$thistitle.html.gz' \n\n  Generated $now CET \n\n  $Version (https://c3jemx2ube5v5zpg.onion/)\n\n\n";
		$style = $style . "\n\n\n==========================================================\n" . $copyright . "\n==========================================================\n\n\n";

		gzwrite($file,"$style");

		$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id ORDER BY id");
		for($row=0;$row<pg_numrows($sql);$row++) {
			$thisrow = pg_Fetch_Object($sql,$row);
			$thisbody = $thisrow->body;
			$thistype = $thisrow->paragraphtype;
			$thisbody = RMLpreparetxt($thisbody);

			if( in_array( $histype, array( 11, 12, 13, 14, 15, 16) ) ) {
				$thisbody = "\n\n" . strtoupper($thisbody) . "\n";
			}

			if($thistype == 5) {
				$thisbody = "\n" . $thisbody;
			}

			if( -1 == gzwrite( $file, "\n" . $thisbody ) ) {
				die( "ERROR : Cant write to file...");
			}
		}
		gzclose($file);

		$filename = "./output/$thistitle.txt.gz";

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize( $filename ) );
		ob_clean();
		flush();
		if(
			readfile($filename)
			&& !connection_aborted()//try detecting aborted download
			&& $status > 2
		) {
			RMLcountdownload();
		}
		exit;
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLexporthtml( $id )
{
	global $SQLsize, $Version, $Version;

	$tablename = RMLgetactivetable($id);
	$result = RMLfiresql("SELECT title,subtitle,year,author_id,copyright FROM document WHERE id=$id");
	$thistmp = pg_Fetch_Object($result,0);
	$title = $thistmp->title;
	$subtitle = $thistmp->subtitle;
	$year = $thistmp->year;
	$authorid = $thistmp->author_id;
	$copyright = $thistmp->copyright;
	$author = RMLgetauthorname($authorid);

	$thistitle = preg_replace("@ @","_",$title);
	$file = gzopen("./output/$thistitle.html.gz",'w') or die("ERROR : Cant open file for writing...");

	$title = RMLpreparehtml($title);

	gzwrite($file,"<html>\n<head>\n<title>$title</title>");

	setTimeZone();
	$now = time();
	$now = strftime('%d %b %Y %H:%M',$now);

	$style = "\n\n<!-- \n\n  '$thistitle.html.gz' \n\n  Generated $now CET \n\n  $Version (https://c3jemx2ube5v5zpg.onion/)\n\n-->\n";
	$style = $style . "\n\n<!--\n" . $copyright . "\n-->\n";
	$style = $style . "\n<meta name=\"author\" content=\"$author\" />";
	$style = $style . "\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
	$style = $style . "\n<style type=\"text/css\">";
	$style = $style . "\nbody {font-size:12pt;margin-left:2em;margin-right:2em}";
	$style = $style . "\nh2.author {text-align:center;margin:4em}";
	$style = $style . "\nh1.title {text-align:center;margin:4em}";
	$style = $style . "\nh1.part {text-align:center;margin-top:3em}";
	$style = $style . "\nh2.book {text-align:center;margin-top:2em}";
	$style = $style . "\nh3.chapter {text-align:center;margin-top:2em}";
	$style = $style . "\nh4 {text-align:left;font-size:18pt;margin-top:2em}";
	$style = $style . "\nh5 {text-align:left;font-size:16pt;margin-top:2em}";
	$style = $style . "\nh6 {text-align:left;font-size:14pt;margin-top:2em}";
	$style = $style . "\np.indent {margin:0;line-height:16pt;text-indent:2em;text-align:justify}";
	$style = $style . "\np.blankover {margin:0;line-height:16pt;margin-top:16pt;text-align:justify}";
	$style = $style . "\np.noindent {margin:0;line-height:16pt;text-indent:0;text-align:justify}";
	$style = $style . "\np.quoteindent {font-family:sans-serif;margin:0;margin-left:4em;margin-right:4em;line-height:14pt;text-indent:2em;text-align:justify}";
	$style = $style . "\np.quotenoindent {font-family:sans-serif;margin:0;margin-left:4em;margin-right:4em;line-height:14pt;text-indent:0;text-align:justify}";
	$style = $style . "\np.quoteblankover {font-family:sans-serif;margin	:0;margin-left:4em;margin-right:4em;line-height:14pt;text-indent:0;margin-top:12pt;text-align:justify}";
	$style = $style . "\np.preblankover {font-family:monospace;font-size:12pt;margin:0;line-height:14pt;margin-top:12pt;text-align:left}";
	$style = $style . "\np.prenoindent {font-family:monospace;font-size:12pt;margin:0;line-height:12pt;text-indent:0;text-align: left}";
	$style = $style . "\n</style>\n</head>\n\n\n<body>\n\n";

	gzwrite($file,$style);

	$tmptitle = "<h1 class=\"title\"><br/><br/><br/>" . $title;

	if($subtitle) {
		$tmptitle = $tmptitle . "<small><br/>$subtitle</small></h1>";
	} else {
		$tmptitle = $tmptitle . "</h1>";
	}

	gzwrite($file,$tmptitle);

	$author = RMLpreparehtml($author);
	$author = "<h2 class=\"author\"><br/><br/>by <br/><br/>" . $author . "</h2>";
	gzwrite($file,"\n$author");

	$copyright = nl2br($copyright);
	$copyright = RMLpreparehtml($copyright);

	$InfoCOM = "<p><center><br/><br/><br/><br/><small>$Version<br/>http://c3jemx2ube5v5zpg.onion/</small></center></p><br><hr><p><small>$copyright</small></p><hr>";

	gzwrite($file,"\n$InfoCOM");

	$before = $SQLsize;
	$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id ORDER BY id");
	$totalsize = $SQLsize - $before;
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$thisbody = RMLpreparehtml($thisbody);

		switch($thistype) {
		case '1': $thisbody = "<h4><br><br>" . $thisbody . "</h4>"; break;
		case '2': $thisbody = "<h5><br>" . $thisbody . "</h5>"; break;
		case '3': $thisbody = "<h6><br>" . $thisbody . "</h6>"; break;
		case '4': $thisbody = "<p class=\"indent\">" . $thisbody . "</p>"; break;
		case '5': $thisbody = "<p class=\"blankover\"><br>" . $thisbody . "</p>"; break;
		case '6': $thisbody = "<blockquote><small>" . $thisbody . "</small></blockquote>"; break;
		case '7': $thisbody = "<blockquote><small><br>" . $thisbody . "</small></blockquote>"; break;
		case '8': $thisbody = "<p class=\"noindent\">" . $thisbody . "</p>"; break;
		case '9': $thisbody = "<blockquote><small>" . $thisbody . "</small></blockquote>"; break;
		case '11': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h1 class=\"part\">" . $thisbody . "</h1></center>"; break;
		case '12': $thisbody = "<center style=\"page-break-before: always;\"><br><h2 class=\"book\">" . $thisbody . "</h2></center>"; break;
		case '13': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h3 class=\"chapter\">" . $thisbody . "</h3></center>"; break;
		case '14': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h1 class=\"part\">" . $thisbody . "</h1></center>"; break;
		case '15': $thisbody = "<center style=\"page-break-before: always;\"><br><h2 class=\"book\">" . $thisbody . "</h2></center>"; break;
		case '16': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h3 class=\"chapter\">" . $thisbody . "</h3></center>"; break;
		case '17': $thisbody = "<p class=\"preblankover\"><small><br>" . $thisbody . "</small></p>"; break;
		case '18': 	$thisbody = "<p class=\"prenoindent\"><small>" . $thisbody . "</small></p>"; break;
		}
		if(-1 == gzwrite($file,"\n" . $thisbody)) { die("ERROR : Cant write to file..."); }
	}

	$counter = getNumberFormatted( pg_numrows($sql),0 );
	$pages = getNumberFormatted( $totalsize / 2200, 0 );
	gzwrite($file,"\n\n</body>\n\n</html>\n\n<!-- \n$counter elements\n$totalsize units ($pages pages) \n\nThis is InfoCOM, an experiment in publishing... signing off. \n-->\n<!-- END OF LINE -->");
	gzclose($file);

	$filename = "./output/$thistitle.html.gz";

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename( $filename ) );
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($filename));
	ob_clean();
	flush();
	if(
		readfile($filename)
		&& !connection_aborted()//try detecting aborted download
		&& $status > 2
	) {
		RMLcountdownload();
	}
	exit;
}

// ============================================================================

function RMLexportmd( $id )
{
	return false;// ewa: not ready yet
	global $SQLsize, $Version, $Version;

	$tablename = RMLgetactivetable($id);
	$result = RMLfiresql("SELECT title,subtitle,year,author_id,copyright FROM document WHERE id=$id");
	$thistmp = pg_Fetch_Object($result,0);
	$title = $thistmp->title;
	$subtitle = $thistmp->subtitle;
	$year = $thistmp->year;
	$authorid = $thistmp->author_id;
	$copyright = $thistmp->copyright;
	$author = RMLgetauthorname( $authorid );

	$thistitle = preg_replace( "@ @", "_", $title );
	$file = gzopen( "./output/$thistitle.md", 'w' ) or die( "ERROR : Cant open file for writing..." );

	setTimeZone();
	$now = time();
	$now = strftime( '%d %b %Y %H:%M', $now );

	$text
		="\n"
		."\n* Generated : $now CET"
		."\n* Version   : $Version (https://c3jemx2ube5v5zpg.onion/)"
		."\n* Title     : $title" .( ( $subtitle != '' ) ? ' – ' .$subtitle : '' )
		."\n* Author    : $author"
		."\n\n## Colophon"
		."\n\n" .$copyright
		."\n\n"
		."## " .$author
		."\n\n"
	;

	gzwrite( $file, $text );
// ewa: continue ...
	$copyright = RMLpreparemd( $copyright );

	$InfoCOM = "<p><center><br/><br/><br/><br/><small>$Version<br/>http://c3jemx2ube5v5zpg.onion/</small></center></p><br><hr><p><small>$copyright</small></p><hr>";

	gzwrite($file,"\n$InfoCOM");

	$before = $SQLsize;
	$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id ORDER BY id");
	$totalsize = $SQLsize - $before;
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$thisbody = RMLpreparehtml($thisbody);

		switch($thistype) {
		case '1': $thisbody = "<h4><br><br>" . $thisbody . "</h4>"; break;
		case '2': $thisbody = "<h5><br>" . $thisbody . "</h5>"; break;
		case '3': $thisbody = "<h6><br>" . $thisbody . "</h6>"; break;
		case '4': $thisbody = "<p class=\"indent\">" . $thisbody . "</p>"; break;
		case '5': $thisbody = "<p class=\"blankover\"><br>" . $thisbody . "</p>"; break;
		case '6': $thisbody = "<blockquote><small>" . $thisbody . "</small></blockquote>"; break;
		case '7': $thisbody = "<blockquote><small><br>" . $thisbody . "</small></blockquote>"; break;
		case '8': $thisbody = "<p class=\"noindent\">" . $thisbody . "</p>"; break;
		case '9': $thisbody = "<blockquote><small>" . $thisbody . "</small></blockquote>"; break;
		case '11': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h1 class=\"part\">" . $thisbody . "</h1></center>"; break;
		case '12': $thisbody = "<center style=\"page-break-before: always;\"><br><h2 class=\"book\">" . $thisbody . "</h2></center>"; break;
		case '13': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h3 class=\"chapter\">" . $thisbody . "</h3></center>"; break;
		case '14': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h1 class=\"part\">" . $thisbody . "</h1></center>"; break;
		case '15': $thisbody = "<center style=\"page-break-before: always;\"><br><h2 class=\"book\">" . $thisbody . "</h2></center>"; break;
		case '16': $thisbody = "<center style=\"page-break-before: always;\"><br><br><h3 class=\"chapter\">" . $thisbody . "</h3></center>"; break;
		case '17': $thisbody = "<p class=\"preblankover\"><small><br>" . $thisbody . "</small></p>"; break;
		case '18': 	$thisbody = "<p class=\"prenoindent\"><small>" . $thisbody . "</small></p>"; break;
		}
		if(-1 == gzwrite($file,"\n" . $thisbody)) { die("ERROR : Cant write to file..."); }
	}

	$counter = getNumberFormatted( pg_numrows($sql),0 );
	$pages = getNumberFormatted( $totalsize / 2200, 0 );
	gzwrite($file,"\n\n</body>\n\n</html>\n\n<!-- \n$counter elements\n$totalsize units ($pages pages) \n\nThis is InfoCOM, an experiment in publishing... signing off. \n-->\n<!-- END OF LINE -->");
	gzclose($file);

	$filename = "./output/$thistitle.html.gz";

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename( $filename ) );
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($filename));
	ob_clean();
	flush();
	if(
		readfile($filename)
		&& !connection_aborted()//try detecting aborted download
		&& $status > 2
	) {
		RMLcountdownload();
	}
	exit;
}

// ============================================================================

function RMLexportepub( $id ) {
	global $Version, $styleid;

	$tablename = RMLgetactivetable( $id );

	$pagestart = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n<head>\n<meta http-equiv=\"Content-Type\" content=\"application/xhtml; charset=utf-8\"/>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\"/>\n<title></title>\n</head>\n<body>";
	$pageend = "\n</body>\n</html>";

	$result = RMLfiresql("SELECT title,subtitle,year,author_id,copyright,teaser,subject_id,status FROM document WHERE id=$id");
	$thistmp = pg_Fetch_Object($result,0);
	$title = $thistmp->title;

	$subtitle = $thistmp->subtitle;
	$year = $thistmp->year;
	$authorid = $thistmp->author_id;
	$copyright = nl2br($thistmp->copyright);
	$copyright = preg_replace( "@ & @", " &amp; ", $copyright );
	$subjectid = $thistmp->subject_id;
	$teaser = nl2br($thistmp->teaser);
	$teaser = preg_replace( "@ & @", " &amp; ", $teaser );
	$status = $thistmp->status;

	$subject = RMLgetsubjecttitle( $subjectid );
	$author = RMLgetauthorname( $authorid );

	$result = RMLfiresql( "SELECT sort_name,dead FROM author WHERE id=$authorid" );
	$thistmp = pg_Fetch_Object( $result, 0 );
	$fileas = $thistmp->sort_name;
	$dead = $thistmp->dead;

	if( $subtitle <> "" ) {
		$tmptitle = $title . ", " . $subtitle;
	} else {
		$tmptitle = $title;
	}

	// todo: improve
	// also: http://stackoverflow.com/questions/19245205/replace-deprecated-preg-replace-e-with-preg-replace-callback 
	$thistitle = preg_replace("@ @","_",$tmptitle);
	$thistitle = preg_replace("@&@","",$thistitle);
	$thistitle = preg_replace("@&nbsp;@","_",$thistitle);
	$thistitle = preg_replace("@'@","",$thistitle);
	$thistitle = preg_replace("@,_@","_",$thistitle);
	$thistitle = preg_replace("@—@","-",$thistitle); // ndash
	$thistitle = preg_replace("@–@","-",$thistitle); // mdash
	$thistitle = preg_replace("@\?@","",$thistitle);
	// replace without regex
	$thistitle = str_replace(array('$','"','{','}'),'',$thistitle);
	$thistitle = str_replace(array('/','%'),'-',$thistitle);

	$filename = "./output/$thistitle.epub";

	// the source need to be write protected
	// todo: check if it was write-proteted still, halt if not
	//print_r( substr(sprintf('%o', fileperms($filename)), -4) );
	
	if (!copy('./template.epub', $filename)) {  // MIMETYPE HACK: solves problem of uncompressed mime file first
		echo( 'Error cannot copy epubtemplate to ' .$filename .'!' );
	}
	// todo: write mime file first (after create new zip) in a way it is not compressed without the need of a template

	$epub = new ZipArchive();
	if( $epub->open( $filename ) !== true ) {
		exit("FATAL : cannot open <$filename>\n");
	}

	// ************************************** CONTAINER
	$container = "<?xml version=\"1.0\"?>\n<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n<rootfiles>\n<rootfile full-path=\"content.opf\" media-type=\"application/oebps-package+xml\"/>\n</rootfiles>\n</container>";
	$epub->addFromString( 'META-INF/container.xml', $container );

	// ************************************** .OPF
	if( $subtitle <> "" ) {
		$thistitle = $title . " : " . $subtitle;
	} else {
		$thistitle = $title;
	}

	$thistitle = preg_replace( "@&@", "&amp;", $thistitle );

	$opf = "<?xml version=\"1.0\"?>
<package xmlns=\"http://www.idpf.org/2007/opf\" unique-identifier=\"uid\" version=\"2.0\">
<metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:dcterms=\"http://purl.org/dc/terms/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:opf=\"http://www.idpf.org/2007/opf\">
\t<dc:title>$thistitle</dc:title>
\t<dc:creator opf:role=\"aut\" opf:file-as=\"$fileas\">$author</dc:creator>
\t<dc:language xsi:type=\"dcterms:RFC3066\">en</dc:language>
\t<dc:identifier id=\"uid\" opf:scheme=\"URI\">
\t\thttps://c3jemx2ube5v5zpg.onion/?function=download&amp;id=$id
\t</dc:identifier>
\t<dc:subject>$subject</dc:subject>
\t<dc:publisher>https://c3jemx2ube5v5zpg.onion</dc:publisher>
\t<dc:date>$year</dc:date>
</metadata>";

	$manifest = "\n\n<manifest>
\t<item id=\"ncx\" href=\"toc.ncx\" media-type=\"application/x-dtbncx+xml\" />
\t<item id=\"stylesheet\" href=\"stylesheet.css\" media-type=\"text/css\" />
\t<item id=\"coverimage\" href=\"cover.jpg\" media-type=\"image/jpeg\" />
\t<item id=\"logo\" href=\"logo.png\" media-type=\"image/png\" />
\t<item id=\"vignet\" href=\"vignet.jpg\" media-type=\"image/jpeg\" />
\t<item id=\"cover\" href=\"cover.html\" media-type=\"application/xhtml+xml\" />
\t<item id=\"titlepage\" href=\"title.html\" media-type=\"application/xhtml+xml\" />
\t<item id=\"copyright\" href=\"copyright.html\" media-type=\"application/xhtml+xml\"/>
\t<item id=\"contents\" href=\"contents.html\" media-type=\"application/xhtml+xml\" />

\t<item id=\"headerfont\" href=\"Amazon-Ember-Bold.ttf\" media-type=\"font-truetype\" />
\t<item id=\"textfont\" href=\"Bookerly-Regular.ttf\" media-type=\"font-truetype\" />
\t<item id=\"textfont-it\" href=\"Bookerly-Italic.ttf\" media-type=\"font-truetype\" />
\t<item id=\"textfont-bd\" href=\"Bookerly-Bold.ttf\" media-type=\"font-truetype\" />
\t<item id=\"monofont\" href=\"DejaVuSansMono.ttf\" media-type=\"font-truetype\" />";

$epub->addFile("./fonts/Amazon-Ember-Bold.ttf", "Amazon-Ember-Bold.ttf");
$epub->addFile("./fonts/Bookerly-Regular.ttf", "Bookerly-Regular.ttf");
$epub->addFile("./fonts/Bookerly-Italic.ttf", "Bookerly-Italic.ttf");
$epub->addFile("./fonts/Bookerly-Bold.ttf", "Bookerly-Bold.ttf");
$epub->addFile("./fonts/DejaVuSansMono.ttf", "DejaVuSansMono.ttf");

	$sql = RMLfiresql("SELECT body,id,section FROM footnote WHERE docid=$id ORDER BY id");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thisid = $thisrow->id;
		$thissection = $thisrow->section;

		$footnote = "\n<div class=\"footnote\" id=\"note$thisid\">[<a href=\"section$thissection.html#note$thisid\"><b>$thisid</b></a>]<br/>" . $thisbody . "</div>";

		$manifest = $manifest . "\n\t<item id=\"footnote$thisid\" href=\"note$thisid.html\" media-type=\"application/xhtml+xml\" />";
		$epub->addFromString("note$thisid.html", $pagestart . $footnote . $pageend);
	}

	$pictureid = 0;

	$sql = RMLfiresql("SELECT body FROM $tablename WHERE paragraphtype=20 AND doc_id=$id");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$pictureid++;

		$thispicture = preg_replace("@<img.*? src=\"./pictures/.*?/(.*?)\">@","\\1",$thisbody);

		$theimage = new RMLimage();
		$theimage->load( "./pictures/$id/$thispicture" ); // todo: include in constructor
		$mediatype = $theimage->getMimeType();
		unset( $theimage ); // destroy object to free memory

		$manifest = $manifest . "\n\t<item id=\"picture$pictureid\" href=\"$thispicture\" media-type=\"$mediatype\" />";
		$epub->addFile("./pictures/$id/$thispicture", "$thispicture");
	}


	$spine = "\n\n<spine toc=\"ncx\">
\t<itemref idref=\"cover\" linear=\"yes\"/>
\t<itemref idref=\"titlepage\" linear=\"yes\"/>
\t<itemref idref=\"copyright\" linear=\"yes\"/>
\t<itemref idref=\"contents\" linear=\"yes\"/>";

	$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id AND parent_id=0 ORDER BY id");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$target = $row + 1;
		$manifest = $manifest . "\n\t<item id=\"section$target\" href=\"section$target.html\" media-type=\"application/xhtml+xml\" />";
		$spine = $spine . "\n\t<itemref idref=\"section$target\" linear=\"yes\"/>";
	}

	$sql = RMLfiresql("SELECT id FROM footnote WHERE docid=$id ORDER BY id");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisid = $thisrow->id;
		$spine = $spine . "\n\t<itemref idref=\"footnote$thisid\" linear=\"no\"/>";
	}

	$manifest = $manifest . "\n</manifest>";
	$spine = $spine . "\n</spine>";

	$opf = $opf . $manifest . $spine . "\n</package>";
	$epub->addFromString('content.opf', $opf);

	// ************************************** COVERPAGE
	$epub->addFile("./covers/cover$id.jpg", "cover.jpg");
	$epub->addFile("./img/logo.png", "logo.png");
	$epub->addFile("./img/vignet.jpg","vignet.jpg");

	$cover = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n<head>\n<meta http-equiv=\"Content-Type\" content=\"application/xhtml+xml; charset=utf-8\"/>\n<title>Cover</title>\n<style type=\"text/css\">\n@page { margin: 0pt; padding :0pt } body {margin : 0pt; padding : 0pt}\n</style>\n</head>\n<body>\n<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" width=\"100%\" height=\"100%\" viewBox=\"0 0 600 800\" preserveAspectRatio=\"xMidYMid meet\">\n<image width=\"600\" height=\"800\" xlink:href=\"cover.jpg\" />\n</svg>" . $pageend;

	$epub->addFromString('cover.html', $cover);

	$title = preg_replace("@&@","&amp;",$title);
	$subtitle = preg_replace("@&@","&amp;",$subtitle);

	$thistitle = $pagestart . "\n<div class=\"title\">$title</div><div class=\"subtitle\">$subtitle</div>\n<div class=\"author\"><small>by</small><br /><br /><b>$author</b></div><div class=\"author\"><small>$year</small></div><div class=\"publisher\"><img alt=\"Logo\" src=\"logo.png\"/><br/><b>~ All Your Books Are Belong to Us !!! ~</b><br/>http://c3jemx2ube5v5zpg.onion</div>" . $pageend;

	$epub->addFromString('title.html', $thistitle);
	// ************************************** COPYRIGHT
	if($subtitle <> "") { $subtitle = "<br/>" . $subtitle;}

	$copy = $pagestart . "\n<div class=\"copyright\"><big><b>$title</b></big>$subtitle <br/><br/>Copyright &copy; $year <b>$author</b></div>\n<div class=\"copyright\">$copyright</div>";

	$copy = $copy . "\n<div class=\"teaser\">&nbsp;<br/>$teaser</div>" . $pageend;
	$epub->addFromString('copyright.html', $copy);
	// ************************************** CONTENTS
	$contents = $pagestart . "<div class=\"chapter\">Contents</div>";

	$tablename = RMLgetactivetable($id);
	$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id AND parent_id=0 ORDER BY id");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$thisbody = preg_replace("@<text:soft-page-break/>@","",$thisbody);

		$target = $row + 1;

		switch($thistype) {
		case 11:
			$contents = $contents . "<div class=\"toc1\"><a href=\"section$target.html\">$thisbody</a></div>";
		break;
		case 12:
			$contents = $contents . "<div class=\"toc2\"><a href=\"section$target.html\">$thisbody</a></div>";
		break;
		case 13:
			$contents = $contents . "<div class=\"toc3\"><a href=\"section$target.html\">$thisbody</a></div>";
		break;
		}
	}
	$contents = $contents . $pageend;

	$epub->addFromString('contents.html', $contents);
	// ************************************** TOC.NCX
	$toc = "<?xml version=\"1.0\"?>
<!DOCTYPE ncx PUBLIC \"-//NISO//DTD ncx 2005-1//EN\" \"http://www.daisy.org/z3986/2005/ncx-2005-1.dtd\">
<ncx xmlns=\"http://www.daisy.org/z3986/2005/ncx/\" version=\"2005-1\">
<head>
\t<meta name=\"dtb:depth\" content=\"2\"/>
\t<meta name=\"dtb:uid\" content=\"https://c3jemx2ube5v5zpg.onion/?function=download&amp;id=$id\"/>
\t<meta name=\"dtb:totalPageCount\" content=\"0\"/>
\t<meta name=\"dtb:maxPageNumber\" content=\"0\"/>
</head>
<docTitle><text>$title</text></docTitle>
<docAuthor><text>$author</text></docAuthor>
<navMap>";

	$tablename = RMLgetactivetable($id);
	$sql = RMLfiresql("SELECT body,paragraphtype FROM $tablename WHERE doc_id=$id AND parent_id=0 ORDER BY id");
	$navpoint = 0;

	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisbody = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$thisbody = preg_replace("@<text:soft-page-break/>@","",$thisbody);
		$thisbody = strip_tags($thisbody);

		if(($thistype == 11) || ($thistype == 12) || ($thistype == 13)) {
			$target = $row + 1;
			$navpoint = $navpoint + 1;

			$toc = $toc . "\n<navPoint id=\"navPoint-$navpoint\" playOrder=\"$navpoint\">";
			$toc = $toc . "\n\t<navLabel><text>$thisbody</text></navLabel>";
			$toc = $toc . "\n\t<content src=\"section$target.html\"/>";
			$toc = $toc . "\n</navPoint>";

			// TODO : Nested NavPoints.
		}
	}

	$toc = $toc . "\n</navMap>\n</ncx>";
	$epub->addFromString('toc.ncx', $toc);

	// ************************************** MAIN
	$tablename = RMLgetactivetable($id);
	$sql = RMLfiresql("SELECT COUNT(id) as counter FROM $tablename WHERE parent_id=0 and doc_id=$id");
	$thisrow = pg_Fetch_Object($sql,0);
	$maxsection = $thisrow->counter + 1;
	for($section=1;$section<$maxsection;$section++) {
		$tablename = RMLgetactivetable($id);

		$result = RMLfiresql("SELECT id,body,paragraphtype FROM $tablename WHERE doc_id=$id AND parent_id=0 ORDER BY id");
		$thisrow = pg_Fetch_Object($result,$section-1);
		$thisparent = $thisrow->id;
		$thistitle = $thisrow->body;
		$thistype = $thisrow->paragraphtype;

		$thistitle = preg_replace("@<text:soft-page-break/>@","",$thistitle);

		switch($thistype) {
			case '11' : $thissection = $pagestart . "\n<div class=\"part\">$thistitle</div>"; break;
			case '12' : $thissection = $pagestart . "\n<div class=\"book\">$thistitle</div>"; break;
			case '13' : $thissection = $pagestart . "\n<div class=\"chapter\">$thistitle</div>"; break;
			case '14' : $thissection = $pagestart . "\n<div class=\"part\">$thistitle</div>"; break;
			case '15' : $thissection = $pagestart . "\n<div class=\"book\">$thistitle</div>"; break;
			case '16' : $thissection = $pagestart . "\n<div class=\"chapter\">$thistitle</div>"; break;
		}

		$result = RMLfiresql("SELECT body,paragraphtype,id FROM $tablename WHERE doc_id=$id AND parent_id=$thisparent order by id");

		for($row=0;$row<pg_numrows($result);$row++) {
			$thisrow = pg_Fetch_Object($result,$row);
			$thisbody = $thisrow->body;
			$thistype = $thisrow->paragraphtype;
			$sequence = $thisrow->id;
			$thisbody = RMLpreparehtml($thisbody);

			$thisbody = preg_replace("@&#8221;./pictures/.*?/(.*?)&#8221;@","\"\\1\"",$thisbody);
			$thisbody = preg_replace("@<img alt=&#8221;Picture&#8221; @","<img alt=\"Picture\" ",$thisbody);
			$thisbody = preg_replace("@<text:s/>@","",$thisbody);
			$thisbody = preg_replace("@<b>\[<a@","<span class=\"note\">[<a",$thisbody);
			$thisbody = preg_replace("@</a>\]</b>@","</a>]</span>",$thisbody);

			switch($thistype) {
				case '1': $thisbody = "\n<div class=\"head1\">" . $thisbody . "</div>"; break;
				case '2': $thisbody = "\n<div class=\"head2\">" . $thisbody . "</div>"; break;
				case '3': $thisbody = "\n<div class=\"head3\">" . $thisbody . "</div>"; break;
				case '4': $thisbody = "\n<div class=\"indent\">" . $thisbody . "</div>"; break;
				case '5': $thisbody = "\n<div class=\"blankover\">" . $thisbody . "</div>"; break;
				case '6': $thisbody = "\n<div class=\"quoteindent\">" . $thisbody . "</div>"; break;
				case '7': $thisbody = "\n<div class=\"quoteblankover\">" . $thisbody . "</div>"; break;
				case '8': $thisbody = "\n<div class=\"noindent\">" . $thisbody . "</div>"; break;
				case '9': $thisbody = "\n<div class=\"quotenoindent\">" . $thisbody . "</div>"; break;
				case '17': $thisbody = "\n<div class=\"preblankover\">" . $thisbody . "</div>"; break;
				case '18': $thisbody = "\n<div class=\"prenoindent\">" . $thisbody . "</div>"; break;
				case '20': $thisbody = "\n<div class=\"picture\">" . $thisbody . "</img></div>"; break;
				case '21': $thisbody = "\n<table><tr><td>" . $thisbody . "</td>"; break;
				case '22': $thisbody = "\n<td>" . $thisbody . "</td>"; break;
				case '23': $thisbody = "\n</tr><tr><td>" . $thisbody . "</td>"; break;
				case '24': $thisbody = "\n<td>" . $thisbody . "</td></tr></table>"; break;
				case '25': $thisbody = "\n<ul><li>" . $thisbody; break;
				case '26': $thisbody = "\n</li><li>" .$thisbody; break;
				case '27': $thisbody = "\n</li><li>" . $thisbody . "</li></ul>"; break;
				case '28': $thisbody = "\n<ol><li>" . $thisbody; break;
				case '29': $thisbody = "\n</li><li>" .$thisbody; break;
				case '30': $thisbody = "\n</li><li>" . $thisbody . "</li></ol>"; break;
				case '31': $thisbody = "\n<div class=\"hangingblankover\">" . $thisbody . "</div>"; break;
				case '32': $thisbody = "\n<div class=\"hangingindent\">" . $thisbody . "</div>"; break;
				case '33': $thisbody = "\n<div class=\"vignet\">&nbsp;<img src=\"vignet.jpg\"/>&nbsp;</div><div class=\"paravignet\">" . $thisbody . "</div>"; break;
				case '34': $thisbody = "\n<div class=\"boxstart\">"; break;
				case '35': $thisbody = "\n</div>"; break;
				case '36': $thisbody = "\n<div class=\"boxhead\">" . $thisbody . "</div>"; break; 
			}
			$thissection = $thissection . $thisbody;
		}
		$thissection = $thissection . $pageend;
		$epub->addFromString("section$section.html",$thissection);

	}
	// ************************************** STYLESHEET

	$style = RMLgetstylesheet($authorid,$subjectid,$id);
	$epub->addFromString('stylesheet.css', $style);

	$epub->close();

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' .basename( $filename ) );
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($filename));
	ob_clean();
	flush();

	if(
		readfile($filename)
		&& !connection_aborted()//try detecting aborted download
		&& $status > 2
	) {	// Only count download if document is live
		RMLcountdownload();
	}
	exit;
}

// ============================================================================

function RMLcountdownload()
{
	global $id;

	RMLfiresql( "UPDATE document SET downloads = downloads + 1 WHERE id=$id" );
}

// ============================================================================

function RMLgetstylesheet($authorid,$subjectid,$id)
{
	$owner = RMLgetcurrentuser();
	
	$result = RMLfiresql("SELECT style FROM stylesheet WHERE owner='$owner' AND name='document$id'");
	if(pg_numrows($result) > 0) {
		$thisrow = pg_Fetch_Object($result,0);
		$style = $thisrow->style;
		return $style;
	}

	$result = RMLfiresql("SELECT style FROM stylesheet WHERE owner='$owner' AND name='author$authorid'");
	if(pg_numrows($result) > 0) {
		$thisrow = pg_Fetch_Object($result,0);
		$style = $thisrow->style;
		return $style;
	}
	
	$result = RMLfiresql("SELECT style FROM stylesheet WHERE owner='$owner' AND name='subject$subjectid'");
	if(pg_numrows($result) > 0) {
		$thisrow = pg_Fetch_Object($result,0);
		$style = $thisrow->style;
		return $style;
	}

	$result = RMLfiresql("SELECT style FROM stylesheet WHERE owner='$owner' AND name='default'");
	if(pg_numrows($result) > 0) {
		$thisrow = pg_Fetch_Object($result,0);
		$style = $thisrow->style;
		return $style;
	}

	$result = RMLfiresql("SELECT style FROM stylesheet WHERE id=1");
	$thisrow = pg_Fetch_Object($result,0);
	$style = $thisrow->style;
	return $style;
}

// ============================================================================

function RMLgetmaxpage()
{
	global $id, $itemprpage;

	$sql = RMLfiresql( "SELECT COUNT(id) as doccount FROM document WHERE subject_id=$id AND status=3" );
	$thisrow = pg_Fetch_Object( $sql, 0 );
	return $thisrow->doccount / $itemprpage + 1;
}

// ============================================================================

function RMLdisplaynews( $print_on = true )
{
	$out = '';
	$sql = RMLfiresql("SELECT id,headline,body,author,posted FROM news ORDER BY posted DESC LIMIT 20");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisid = $thisrow->id;
		$thishead = $thisrow->headline;
		$thisbody = nl2br($thisrow->body);
		$thisauthor = $thisrow->author;
		$date = RMLfixdate( $thisrow->posted );

		$out .= "\n".'<div class="box">
<div class="boxheader"><b>'.$thishead.'</b></div><div style="text-align:right;padding-right:15px"><small><i>by</i> : <b>'.$thisauthor.'</b> (<i>'.$date.'</i>)</small>'
		.( ( hasRights( 'delnews', array( $thisauthor ) ) )
			? "\n".'<a href="?news=delete&amp;id='.$thisid.'"><img style="float : right;margin-top:-28px" alt="Delete" src="img/delete.png" /></a><br/>'
			//.' <a class="button edit" href="?news=edit">Edit News</a>'
			: ''
		)
		.'</div><div class="boxtext">'.$thisbody.'</div>
</div>'	;
	}
	if( hasRights( 'addnews' ) ) {
		$out .=
		"\n".'<a class="button add" href="?news=add">Add News</a>'
		;
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLaddnews( $print_on = true )
{
	if( ! hasRights( 'addnews' ) ) {
		$out = "ERROR: in Add News, you have no right to do this.";
		return false;
	} else {
		$author = RMLgetcurrentuser();
		$out = "\n".'<p class="ParaNoIndent">Hello '.$author.'<br/>
Please keep news to something that is actually news. Other than that, go nuts...<br/>
&nbsp;</p>
<form method="post" action="?news=save"><table class="form">
<tr><td valign="top">Headline : </td><td><input type="text" name="headline" size="60"></td></tr>
<tr><td valign="top">Body : </td><td><textarea class="norm" rows="10" cols="41" wrap="none" name="body"></textarea></td></tr>
<tr><td></td><td><input type="submit" value="Post news"></td></tr></table>
<input type="hidden" name="news" value="save"></form>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLsavenews( $print_on = true )
{
	global $body, $headline;

	$out = '';
	if( ! hasRights( 'addnews' ) ) {
		$out = "ERROR: News Save : Cookie baaaaaaaad...";
	} else {

		$author = RMLgetcurrentuser();

	// ewa: load of news should be ommitted on view not deleted 
	//	RMLfiresql("DELETE FROM news WHERE author='SYSTEM'");

		RMLfiresql("INSERT INTO news (id,headline,body,author,posted) VALUES(DEFAULT,'$headline','$body','$author',NOW())");
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdeletenews( $id, $print_on = true )
{
	$sql = RMLfiresql("SELECT author FROM news WHERE id=$id");
	$thisrow = pg_Fetch_Object($sql,0);

	$thisauthor = $thisrow->author;
	if( ! hasRights( 'delnews', array( $thisauthor ) ) ) {
		$out = 'ERROR: No Rights to delte news';
	} else {
		RMLfiresql("DELETE FROM news WHERE id=$id");
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLeditnews( $id, $print_on = true )
{
	$out = '';
	if( ! hasRights( 'editnews' ) ) {
		$out = "ERROR : No rights for you.";
	} else {
		if( ! hasRights( 'test' ) ) {
			$out = 'No code in function yet.';
		} else {
			//id,headline,body,author,posted
			$sql = RMLfiresql("SELECT * FROM news WHERE id=$id");
			$thisrow = pg_Fetch_Object($sql,0);
			$cu = RMLgetcurrentuser();
			$out = "\n".'<p class="ParaNoIndent">Hello ' .$cu .'<br/>
Please keep news to something that is actually news. Other than that, go nuts...<br/>
&nbsp;</p>
<form method="post" action="?news=update&id='.$id.'"><table class="form">
<tr><td valign="top">Headline : </td><td><input type="text" name="headline" size="60" value="'.$thisrow->headline.'"></td></tr>
<tr><td valign="top">Body : </td><td><textarea class="norm" rows="10" cols="41" wrap="none" name="body">'.$thisrow->body.'</textarea></td></tr>
<tr><td></td><td><input type="submit" value="Save news"></td></tr></table>
<input type="hidden" name="news" value="save"></form>'
			;
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLupdatenews( $print_on = true )
{
	$out = '';
	if( ! hasRights( 'editnews' ) ) {
		$out = "ERROR : No rights for you.";
	}
	if( ! hasRights( 'test' ) ) {
		$out = 'ERROR: No code in function yet.';
	}
	RMLfiresql("UPDATE news SET headline='".$headline."', body='".$body."' WHERE id='$id'");
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLgetrating( $number ) {
	if( $number > 1000 ) return "Elite";
	if( $number > 750 ) return "Jedi Master";
	if( $number > 500 ) return "Jedi";
	if( $number > 250 ) return "Zen Master";
	if( $number > 100 ) return "Master";
	if( $number > 75 ) return "Expert";
	if( $number > 50 ) return "Adept";
	if( $number > 25 ) return "Apprentice";
	if( $number > 10 ) return "Novice";
	if( $number > 5 ) return "Amateur";
	if( $number > 1 ) return "Trainee";
	return "dyslexic";
}

/* reimplemented
 *
 * suppressing warning for errno 13: 'Permission denied'
 * suppressing warning for filetype: 'Lstat failed'
 * counting filetype 'dir' and 'file' only
 */
function getDirectorySize( $path, $delim='/' )
{
	$r = array('size' => 0, 'count' => 0, 'dircount' => 0);
	if( !file_exists( $path )
		|| false === ( $dirlist = @scandir( $path ) )
	) {
		return false;
	}
	foreach( array_diff( $dirlist, array('.', '..') ) as $file ) {
		$nextpath = $path . $delim . $file;
		switch( @filetype( $nextpath ) ) {
			case 'dir':
				$result = getDirectorySize( $nextpath );
				$r['size'] += $result['size'];
				$r['count'] += $result['count'];
				$r['dircount'] += $result['dircount']+1;
				break;
			case 'file':
				$r['size'] += filesize( $nextpath );
				$r['count']++;
				break;
			default:
			/* not counting other file types as file here
			 * possible are:
			 *		'link', 'char', 'block', 'socket', 'fifo', ''
			 */
		}
	}
	return $r;
}

/* reimplemented
 *
 * used to show file size with unit
 *
 * n - number, feasible is (platform dependant) PHP_INT_SIZE
 * s - space character, e.g. for non breaking html space
 * b - base for correct numbers and unit names
 * u - units as array[base][exponent]
 * e - calculated exponent
 * i - index/exponent of biggest unit
 *
 * IEC prefix, properly explained:
 *   https://en.wikipedia.org/wiki/Binary_prefix
 * remember to use
 *   + decimal -  is data transfer (base 1000: in kB, MB, GB, ...)
 *   + binary -  is data storage (base 1024: in KiB, MiB, GiB, ... )
 */
function sizeFormat( $n, $d = array( 'decnum' => -1 ), $s='&nbsp;', $b=1024, $u = array(
		// YiB is 2^80, x64 has PHP_INT_SIZE
		// => 2^60 is max feasible for now
		'1024' => array("bytes", "KiB", "MiB", "GiB",
			"TiB", "PiB", "EiB", "ZiB", "YiB"/**/ ),
		'1000' => array("bytes", "kB", "MB", "GB",
			"TB", "PB", "EB", "ZB", "YB"/**/ ),
	) )
{

	// Jotunbane -------------------
		if($n == 0) { return 0; }
	// ----------------------------

	$e =	(int)log( $n, $b ) ;
	$n /=	( ( $b == 0 && $e != 0 ) ? pow( $b, $e ) : 1 );
	$i =	sizeof( $u[$b] ) - 1;
	if( $e > $i ) {
		$e -=	$e - $i ;
	}
	$n /=	pow( $b, $e );
	return getNumberFormatted( $n, $d['decnum'] ) . $s . $u[$b][$e];
}

/* reimplemented
 *
 * dirname - directory to delete recursively
 *
 * suppressing warning for errno 13: 'Permission denied'
 * no extended validation for dirname herein yet
 * list of more files or directories that shall not be deleted might be handy to add
*/
function del_dir( $dirname, $delim='/' )
{
	if( ! is_dir( $dirname ) ) {
		//return unlink($dirname); //in case that is what you want
		return false;
	}
	if( false === ( $dirlist = @scandir( $path ) ) ) {
		return false;
	}
	foreach( array_diff( $dirlist, array( '.', '..' ) ) as $file ) {
		if( is_dir( $dirname . $delim . $file ) ) {
			del_dir( $dirname . $delim . $file );
		} else {
			unlink( $dirname . $delim . $file );
		}
	}
	return del_dir( $dirname );
}

// ============================================================================

/* ewa: optimization, displaying level/rating might be changed here centrally
 * formatting/alignment should be done in style best as a class or a container calling this */
function getRatingDisplay( $score, $styleclass='rating-elm', $max = 10, $round = 0 )
{
	$score = round( $score, 0 );
	return str_repeat ( '<img class="'.$styleclass.'" alt="On" src="./img/on.png"/>', $score )
	. str_repeat ( '<img class="'.$styleclass.'" alt="Off" src="./img/off.png"/>', ( $max - $score ) );
}

/* put timezone in a central point, could be configured in a setting via DB or config file as well
 * */
function setTimeZone( $z = 'Europe/Copenhagen' )
{
	// idea: e.g. if $z == '' load config file
	return date_default_timezone_set( $z );
}

/* ewa:		centralize output, check for marked error and return value
 * usage:	return processOutput( $out, $print_on );
 * */
function processOutput( $output, $printit )
{
	if ( $printit ) {
		print( $output );
		if ( strtoupper( substr( $output, 0, 6 ) ) == 'ERROR:' ) {
			return false;
		} else {
			return true;
		}
	} else {
		return $output;
	}
}

function getBibTeX( $docID ) {
	//request data from db
	$result = RMLfiresql( "
SELECT title,
	subtitle,
	author_id, (SELECT name FROM author WHERE id=document.author_id) AS author,
	subject_id,
	(SELECT subject_name FROM subject WHERE id=document.subject_id) AS subject,
	year,
	\"unique\",
	keywords,
	copyright AS colophon,
	teaser,
	downloads,
	(SELECT owner FROM subject WHERE id=document.subject_id) AS owner
FROM document WHERE id=" .$docID
	);
	//if( !$result ){ return ''; }

	$book = pg_Fetch_Object( $result, 0 );
	$colophon = explode("\n", $s );
	foreach( $colophon as $line ) {

		if ( strpos( $line, ':' ) ) {
			$elm = explode( ":", $line );

			switch( strtolower( $elm[0] ) ) {
				case 'translator':
				case 'translation':
					//translator
					$book->translator = $elm[1];
					//orig language
					$start = strpos( $elm[1] , ' (' );
					if ( $start > -1 ) {
						$book->translator = substr( $elm[1], 0, $start );
						$start += 2;
						$length = strlen( $elm[1] ) - $start - 1;
						//last elm in space-separated list (e.g. "from English", "from the English")
						$book->origlanguage = array_slice( explode( ' ', substr( $elm[1], $start, $length ) ), -1 )[0];
					}
				break;
				case 'language':
					$book->language = $elm[1];
				break;
				case 'isbn':
					$book->isbn = $elm[1];
				break;
				case 'first published':
				case 'published':
					//publisher
					$start = strpos( $elm[1] , ' by ' );
					if ( $start > -1 ) {
						$start += 4;
						$length = strlen( $elm[1] ) - $start;
						$book->publisher = substr( $elm[1], $start, $length );
					} else {
						$start = 0;
					}
					//date
					//todo
				break;
				case 'publisher':
					$book->publisher = $elm[1];
				break;
				case 'edition':
					$book->edition = $elm[1];
				break;
				case 'editor':
					$book->editor = $elm[1];
				break;
				case 'volume':
					$book->volume = $elm[1];
				break;
				case 'series':
					$book->series = $elm[1];
				break;
				case 'number':
					$book->number = $elm[1];
				break;
			}

		}

	}

	if ( isset( $book->date ) ) {
		unset( $book->year );
		unset( $book->month );
	}
	if ( isset( $book->editor ) ) {
		unset( $book->author );
	}
	if ( strlen( $book->subtitle ) <= 0 ) {
		unset( $book->subtitle );
	}

	return '@book{rml:' .$docid .','
.'	title = {' .$book->title .'},'
//.'	indextitle = {' .$book->title .'},' //
//.'	shorttitle = {' .$book->shorttitle .'},' //
.( !isset( $book->subtitle ) )?'':'	subtitle = {' .$book->subtitle .'},'
.'	author = {' .$book->author .'},'
.( !isset( $book->editor ) )?'':'	editor = {' .$book->editor .'},'
.( !isset( $book->translator ) )?'':'	translator = {' .$book->translator .'},'
.( !isset( $book->origlanguage ) )?'':'	origlanguage = {' .$book->origlanguage .'},'
.( !isset( $book->language ) )?'':'	language = {' .$book->language .'},'
.( !isset( $book->publisher ) )?'':'	publisher = {' .$book->publisher .'},'
.( !isset( $book->isbn ) )?'':'	isbn =      {' .$book->isbn .'},'
.( !isset( $book->date ) )?'':'	date = {' .$book->date .'},'
.( !isset( $book->year ) )?'':'	year =      {' .$book->year .'},'
.( !isset( $book->month ) )?'':'	month = {' .$book->month .'},'
.( !isset( $book->series ) )?'':'	series =    {' .$book->series .'},'
.( !isset( $book->number ) )?'':'	number =    {' .$book->number .'},'
.( !isset( $book->edition ) )?'':'	edition =   {' .$book->edition .'},'
.( !isset( $book->keywords ) )?'':'	keywords = {' .$book->keywords .'},'
.( !isset( $book->volume ) )?'':'	volume =    {' .$book->volume .'},'
.'	url =       {http://c3jemx2ube5v5zpg.onion/?document=view&id='.$docID .'},'
//.'	note = {' $book->colophon .'}'//for now just colophon
.'}';
}

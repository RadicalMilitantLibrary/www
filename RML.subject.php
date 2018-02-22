<?php
// ============================================================================
//  Subjects for Radical Militant Library
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

function RMLdisplaysubject( $id, $print_on = true )
{
	global $page, $letter, $itemprpage;

	$out = '';
	if( !isset( $id ) || $id < 1 ) {
		$out .= RMLdisplaysubjectorder( false );

		if( hasRights( 'addsubject' ) ) {
			$out .= "\n".'<a class="button add" href="?subject=add">Add Subject</a>';
		}

		$result = RMLfiresql( "SELECT id,subject_name,subject_description,(SELECT count(id) FROM document WHERE subject_id=subject.id AND status>3) AS doccount FROM subject ORDER BY subject_name" );
		if ( ! $result ) {
			$out = 'ERROR: No elements to list';
		} else {

			for( $row=0; $row < pg_numrows( $result ); $row++ ) {
				$thisrow = pg_Fetch_Object( $result, $row );
				$thisid = $thisrow->id;
				$thisname = $thisrow->subject_name;
				$doccount = $thisrow->doccount;
				$description = nl2br( $thisrow->subject_description );

				if( strlen( $description ) == 0 ) {
					$description = "No description...";
				} elseif ( strlen( $description ) > 300 ) {
					$description = substr( $description, 0, 300 ) .'...';
				}
				$out .= "\n".'<div class="box">
<div class="boxheader"><a href="?subject=view&amp;id='.$thisid.'"><b>'.$thisname.'</a></b></div>
<div class="boxtext">'.$description.'</div>
<div class="inlineclear"></div>
</div>';
			}
		}
	} else {
		$sql = RMLfiresql( "SELECT id,subject_name,subject_description AS subjdesc,(SELECT COUNT(id) FROM document WHERE subject_id=subject.id AND subject.id=$id AND status=3) AS doccount FROM subject ORDER BY subject_name" );
		if( ! $sql ) {
			$out = 'ERROR: No elements to list';
		} else {
			$thisrow = pg_Fetch_Object( $sql, 0 );
			$out .= '';
			// PAGINATION START
			$doccount = $thisrow->doccount;
			$maxpage = $doccount / $itemprpage + 1;

			if( ( $page == '' ) || ( $page == 0 ) ) {
				$page = 1;
			}

			$firstrow = $page * $itemprpage - $itemprpage;
			$lastrow = $firstrow + $itemprpage;

			if( $lastrow > $doccount ) {
				$lastrow = $doccount;
			}

			$pagination = "\n".'<div class="order">';
			if( $doccount > $itemprpage ) {//show page links when too many items
				for( $row=1; $row < $maxpage; $row++ ) {
					if( $row == $page ) {
						$pagination .= "\n".'<a class="activebutton" href="?subject=view&amp;id='.$id.'&amp;page='.$row.'">'.$row.'</a>';
					} else {
						$pagination .= "\n".'<a class="button" href="?subject=view&amp;id='.$id.'&amp;page='.$row.'">'.$row.'</a>';
					}
				}
			}
			$pagination .= '</div>';
			$tmprow = $firstrow + 1;
			if( $doccount > $itemprpage ) {
				$pagination .= '<div style="float:right;margin-top: -30px;">&nbsp;<b>' .getNumberFormatted( $tmprow, 0 ) .'</b> to <b>'. getNumberFormatted( $lastrow, 0 ) .'</b> of <b>'. getNumberFormatted( $doccount, 0 ) .'</b></div>';
			}
			// PAGINATION END.
			$out .= $pagination;
// todo: needs fix for correct subject image (its asked for when creating a new one), could default on maintainer image if not available

			$sql = RMLfiresql( "SELECT id,title,status,author_id,year,keywords,teaser,(SELECT name FROM author WHERE id=document.author_id) AS autname,(SELECT AVG(level) FROM forum WHERE thread_id=document.id) AS score FROM document WHERE subject_id=$id AND status=3 ORDER BY title LIMIT $itemprpage OFFSET $firstrow" );
			if( ! $sql ) {
				$out = 'ERROR: No Subject Information.';
			} else {
				for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
					$thisrow = pg_Fetch_Object( $sql, $row );
					$thisid = $thisrow->id;

					$thisstatus = $thisrow->status;
					$thisposted = $thisrow->posted_on;
					$thisauthorid = $thisrow->author_id;
					$thistitle = $thisrow->title;
					$thisyear = $thisrow->year;
					$thiskeywords = $thisrow->keywords;
					$thisteaser = $thisrow->teaser;
					$authorname = $thisrow->autname;
					$avgscore = $thisrow->score;

					$out .= "\n".'<div class="box">
<p class="boxheader"><a href="?document=view&amp;id='.$thisid.'"><img class="Cover" alt="Cover" src="./covers/cover'.$thisid.'"/><b>'.$thistitle.'</b></a></p>';

					if( $thisyear == '0' ) {
						$thisyear = 'Unknown';
					}

					$out .= "\n".'<p class="boxtext"><small>by <a href="?author=view&amp;id='.$thisauthorid.'">'.$authorname.'</a>, <b>'.$thisyear.'</b></small>';

					//$out .= '<span class="right-float">' .getRatingDisplay( $avgscore ) .'</span>';

					if( strlen( $thisteaser ) > 300 ) {
						$thisteaser = substr( $thisteaser, 0, 296 ) . ' ...';
						$thisteaser = strip_tags( $thisteaser );
					}
					$out .= '</p><p class="boxtext">'.$thisteaser.'</p><div class="inlineclear"></div></div>';
				}
				$out .= $pagination;
			}
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaysubjectorder( $print_on = true ) {
	global $letter, $id;

	$sortletters = '';
	$oldletter = '';

	$result = RMLfiresql("SELECT subject_name FROM subject ORDER BY subject_name");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisname = $thisrow->subject_name;
		$sortletters = $sortletters . strtoupper($thisname[0]);
	}

	$out = "\n<div class=\"order\">";

	if($letter == 'All') {
		$out .= "\n<a class=\"activebutton\" href=\"?subject=view&amp;id=0&amp;letter=All\"><span>All</span></a>";
	} else {
		$out .= "\n<a class=\"button\" href=\"?subject=view&amp;id=0&amp;letter=All\"><span>All</span></a>";
	}

	for($i = 0; $i < strlen($sortletters); $i++) {
		if($oldletter <> $sortletters[$i]) {
			if($letter == $sortletters[$i]) {
				$out .= "\n<a class=\"activebutton\" href=\"?subject=view&amp;id=0&amp;letter=$sortletters[$i]\"><span>$sortletters[$i]</span></a>";
			} else {
				$out .= "\n<a class=\"button\" href=\"?subject=view&amp;id=0&amp;letter=$sortletters[$i]\"><span>$sortletters[$i]</span></a>";
			}
		}
		$oldletter = $sortletters[$i];
	}

	$out .= "</div>";
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLaddsubject( $print_on = true ) {
	$out = "\n<table><form enctype=\"multipart/form-data\" method=\"post\" action=\"?subject=new\">
<tr><td align=right>Subject </td><td><input class=norm type=\"text\" name=\"headline\"></td></tr>
<tr><td align=right valign=top>Description </td><td><textarea class=norm rows=\"12\" name=\"bodytext\"></textarea></td></tr>
<tr><td align=right>Picture </td><td><input class=norm type=\"file\" name=\"picture\"></td></tr>
<tr><td></td><td><input type=\"submit\" value=\"Hit It\"></td></form></table>";
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLnewsubject( $print_on = true )
{
	global $bodytext, $headline;

	if( ! hasRights( 'addsubject' ) ) {
		$out = 'Sorry, you lack the right to add a new subject. Only known and experienced Librarians will be granted rights. Get started reading <a href="?function=manual">the manual</a>.';
	} else {

		$target_path = "./subjects/";
		$target_path = $target_path . "subject_" . basename( $_FILES['picture']['name']);

		if( move_uploaded_file( $_FILES['picture']['tmp_name'], $target_path ) ) {
			$filename = "subject_" . basename( $_FILES['picture']['name']);
		} else{
			$filename = "Default.png";
		}

		$thisuser = RMLgetcurrentuser();
		RMLfiresql( "INSERT INTO subject (id,owner,subject_name,subject_description,picture) VALUES (DEFAULT,'$thisuser','$headline','$bodytext','$filename')" );
		RMLfiresql( "INSERT INTO news (id,headline,body,author,posted) VALUES(DEFAULT,'New subject ($headline).','<b>$thisuser</b>, just added the subject <b>$headline</b>.','SYSTEM',NOW())" );
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLeditsubject( $id, $print_on = true ) {
	$result = RMLfiresql("SELECT owner,subject_name,subject_description FROM subject WHERE id=$id");
	$thisrow = pg_Fetch_Object($result,0);
	$maintainer = $thisrow->owner;
	$thisname = $thisrow->subject_name;
	$thisdesc = $thisrow->subject_description;

	$out = '';
	if( hasRights( 'editsubject', array( $maintainer ) ) ) {
		$out = ("ERROR: Edit Subject, bad user...");
	} else {
	$out .= "\n".'<table>
<form enctype="multipart/form-data" method="post" action="?subject=update&amp;id='.$id.'">
<tr><td>Subject</td><td><input class=norm type="text" name="headline" value="'.$thisname.'"></td></tr>
<tr><td valign=top>Descriptipn</td><td><textarea class=norm rows="20" name="bodytext">'.$thisdesc.'</textarea></td></tr>
<tr><td>Picture</td><td><input type="file" size=49 name="picture"></td></tr>
<tr><td></td><td><input type="submit" value="Edit Subject"></td>
</form></table>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLupdatesubject( $id, $headline, $bodytext, $print_on = true ) {
	$result = RMLfiresql("SELECT owner FROM subject WHERE id=$id");
	$thisrow = pg_Fetch_Object($result,0);
	$maintainer = $thisrow->owner;

	if( hasRights( 'editsubject', array( $maintainer ) ) ) {
		$out = "ERROR: Update Subject, bad user...";
	} elseif($headline == '') {
		$out = "ERROR: Update subject, bad subject name.";
	} else {

		$target_path = "./subjects/";
		$target_path = $target_path . "subject" . basename( $_FILES['picture']['name']);

		if(move_uploaded_file($_FILES['picture']['tmp_name'], $target_path)) {
			$filename = "subject" . basename( $_FILES['picture']['name']);
			InfoCOMfiresql("UPDATE subject SET picture='$filename' WHERE id=$id");
		}

		RMLfiresql("UPDATE subject SET subject_name='$headline' WHERE id=$id");
		RMLfiresql("UPDATE subject SET subject_description='$bodytext' WHERE id=$id");
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplaysubjectlocation( $print_on = true ) {
	global $id, $subject;

	$out = '';
	if( ( $id > 0 ) || ( $subject == 'new' ) ) {
		$out .= "\n".'<div class="order">[<a href="?subject=view&amp;id=0&amp;letter=All">Subjects</a>]</div>';
	}
	return processOutput( $out, $print_on );
}



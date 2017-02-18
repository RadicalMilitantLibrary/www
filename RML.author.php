<?php

// ============================================================================
//  Author functions for Radical Militant Library
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

function RMLdisplayauthor( $id, $print_on = true )
{
	global $letter;

	$out = '';
	if( !isset( $id ) || $id == 0 ) {
		$out .= RMLdisplayauthororder( false );

		$result = RMLfiresql("SELECT id,name,sort_name,born,dead,bio,(SELECT COUNT(author_id) FROM document WHERE author_id = author.id AND status=3) AS counter FROM author WHERE letter='$letter' GROUP BY counter,sort_name,author.id,author.name,author.born,author.dead,author.bio ORDER BY sort_name");
		$displaycount = 0;
		setTimeZone();
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisid = $thisrow->id;
			$thisname = $thisrow->name;
			$thissort = $thisrow->sort_name;
			$born = $thisrow->born;
			$dead = $thisrow->dead;
			$counter = $thisrow->counter;
			//$maintainer = $thisrow->maintainer;//not in query! maybe add doc-maintainer
			$bio = $thisrow->bio;

			$age = getAge( $born, $dead );
			if ( $born == 0 ) {
				$born = 'Unknown'; $age = '';
			} else {
				$born = substr( $born, 0, 4 );
			}
			$bio = strip_tags( $bio );
			if( strlen( $bio ) > 400 ) {
				$bio = substr( $bio, 0, 400 ) . ' …';
			}


			if( isset( $letter ) && strlen( $letter ) > 0 ) {
				if ( ( $thissort[0] == strtoupper( $letter ) ) && ( $counter > 0) ) {

					$out .= "\n".'<div class="box"><div class="inlineclear">
<a href="?author=view&amp;id='.$thisid.'&amp;letter='.$letter.'"><img alt="Author #'.$thisid.'" class="Author" src="./authors/author'.$thisid.'"/></a>
<div class="boxheader"><a href="?author=view&amp;id='.$thisid.'&amp;letter='.$letter.'"><b>'.$thisname.'</b></a></div>
<div style="margin:0;margin-top:5px">Born <b>' .$born . '</b>';

					if( is_numeric( $dead ) && $dead != 0 ) {
						$out .= ', Died <b>'.substr( $dead, 0, 4 ).'</b>';
					}
					if( $age != '' ) {
						$out .= ' (age <b>'.$age.'</b>)';
					}

					$out .= '<br/>Books online <b>' . getNumberFormatted( $counter, 0 ) .'</b></div>';

					$out .= "\n".'<div class="boxtext">'.$bio.'</div></div></div>';
				}
			} else {	// ewa: was defunct?! (by if obove)
				if( $displaycount < 20 ) {	//todo: implement pages to turn over -> limit in query already
					$thisletter = $thissort[0];

					$out .= "\n".'<div class="box">
<a href="?author=view&amp;id='.$thisid.'&amp;letter='.$thisletter.'"><img class="Author" alt="Author #'.$thisid.'" src="./authors/author'.$thisid.'"></a>
<p class="boxheader"><b>'.$thisname.'</b></p>
<p class="boxtext"><small>Born <b>'.$born.'</b>';
					if( $dead != 0 ) {
						$out .= ", Died <b>$dead</b>";
					}
					$out .= ' (age <b>'.$age.'</b>)<br>
Books online <b>'. getNumberFormatted( $counter, 0 ) .'</b></small></p>';

					$out .=  "\n".'<p class="boxtext"><small>'.$bio.'</small></p></div>' ;
					$displaycount++;
				}
			}
		}

		if( hasRights( 'addauthor'/** /, array( $maintainer )/**/ ) ) {//maintainer not in query!
			$out .= "\n".'<a href="?author=new" class="button add">Add</a>';
		}
	} else {
		$out = RMLdisplaydocumentsbyauthor( $id, false );
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayauthororder( $print_on = true )
{
	global $letter;

	$sortletters = "";
	$oldletter = "";

	$result = RMLfiresql("SELECT sort_name FROM author ORDER BY sort_name");
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisname = $thisrow->sort_name;
		$sortletters = $sortletters . strtoupper( $thisname[0] );
	}

	$out = '';
	$out .= "\n".'<div class="order">';

	for( $i = 0; $i < strlen($sortletters); $i++ ) {
		if( $oldletter <> $sortletters[$i] ) {
			$out .= "\n".'<a class="';
			if( $letter == $sortletters[$i] ) {
				$out .= 'active';
			}
			$out .= 'button" href="?author=view&amp;letter='.$sortletters[$i].'">'.$sortletters[$i].'</a>';
		}
		$oldletter = $sortletters[$i];
	}

	$out .= '</div>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLnewauthor( $print_on = true )
{
	if( RMLgetcurrentuser() == '' ) {
		$out = 'ERROR: New Author. Bad User...';
	} else {
		$out = "\n".'<form enctype="multipart/form-data" method="post" action="?author=add">
<table>
<tr><td>Name&nbsp;</td><td><input class="norm" type="text" name="authorname"></td></tr>
<tr><td>Sort as&nbsp;</td><td><input class="norm" type="text" name="sortname"></td></tr>
<tr><td>*Born&nbsp;&nbsp;</td><td><input class="norm" type="text" name="born"></td></tr>
<tr><td>*Died&nbsp;(YYYYMMDD*)&nbsp;</td><td><input class="norm" type="text" value=0 name="dead"></td></tr>
<tr><td valign="top">Bio</td><td><textarea class="norm" rows="20" name="bodytext"></textarea></td></tr>
<tr><td>Biosource&nbsp;</td><td><input class="norm" type="text" name="source"></td></tr>
<tr><td>Picture</td><td><input type="file" size="49" name="picture"></td></tr>
<tr><td></td><td><input type="submit" value="Add author"></td>
</table><span>* Dates as far as known, so year only (e.g. '.date('Y').') is valid as well as year and month only (e.g. '.date('Ym').') or full date (e.g. today: '.date('Ymd').')</span>
</form>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLaddauthor( $print_on = true )
{
	global $authorname, $sortname, $born, $dead, $bodytext, $source;

	$maintainer = RMLgetcurrentuser();
	if(
		$maintainer == null	// user logged in
		|| ! hasRights( 'addauthor' )	// allowed to add authors
	) {
		/* vv01f: or add but inactive to be edited my a moderator */
		$out = 'ERROR: Action not permitted: Add author.';
	} else {
		if( $authorname == '' ) {
			$out = "ERROR: Add author, bad authorname.";
		} elseif( $sortname == '' ) {
			$out = "ERROR: Add author, bad sortname.";
		} elseif( $born == '' ) {
			$out = "ERROR: Add author, bad birthyear.";
		} else {
		/* vv01f: more checks needed! */

			$letter = strtoupper( substr( $sortname, 0, 1 ) );

			RMLfiresql( "INSERT INTO author (id,name,sort_name,bio,maintainer,born,dead,source,letter) VALUES (DEFAULT,'$authorname','$sortname','$bodytext','$maintainer',$born,$dead,'$source','$letter')" );

			$sql = RMLfiresql( "SELECT id FROM author where sort_name='$sortname'" );
			if ( ! $sql ) {
				$out = 'ERROR: Author could not be selected: '.$sortname ;
			} else {
				$thisrow = pg_Fetch_Object( $sql, 0 );
				$thisid = $thisrow->id;
				$filename = 'author' .$thisid;
				$target_path = './authors/' .$filename;

				// use default.jpg if no picture given
				// todo: check if default image is write protected before
				if( !move_uploaded_file( $_FILES['picture']['tmp_name'], $target_path ) ) {
					exec( 'cp ./authors/default.jpg '.$target_path );// todo: user copy('./authors/default.jpg', $target_path) http://de2.php.net/manual/de/function.copy.php
				} else {
					// limit author image to width of 300
					$myimage = new RMLimage();
					$myimage->load( $target_path );
					if ( $myimage->getWidth() >  300 ) {
						$myimage->resizeToWidth( 300 );
					}
					$myimage->save( $target_path );
				}

				RMLfiresql( "CREATE TABLE $filename (doc_id integer NOT NULL,paragraphtype integer NOT NULL,body text,id integer NOT NULL,parent_id integer NOT NULL) WITH (OIDS=FALSE)" );
				RMLfiresql( "ALTER TABLE $filename OWNER TO webuser" );
				RMLfiresql( "INSERT INTO news (id,headline,body,author,posted) VALUES(DEFAULT,'New author.','<i>$maintainer</i> added the author <b><a href=\"?author=view&id=$thisid\">$authorname</a></b>.','SYSTEM',NOW())" );
			}
		}
	}
	/* message in news */
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLeditauthor( $id, $print_on = true ) {
	$result = RMLfiresql( "SELECT name,sort_name,bio,maintainer,born,dead,picture,source FROM author WHERE id=$id" );
	if( ! hasRights( 'editauthor', array( $thismaintainer ) ) ) {
		$out = 'ERROR: Edit Author not allowed, bad user...';
	} elseif( ! $result ) {
		$out = 'ERROR: Missing Author ID #'.$id ;
	} else {
		$thisrow = pg_Fetch_Object( $result, 0 );
		$thisname = $thisrow->name;
		$thissortname = $thisrow->sort_name;
		$thisbio = $thisrow->bio;
		$thismaintainer = $thisrow->maintainer;
		$thisborn = $thisrow->born;
		$thisdead = $thisrow->dead;
		$thissource = $thisrow->source;

		$out = "\n".'<table>
<form enctype="multipart/form-data" method="post" action="?author=update&amp;id='.$id.'">
<tr><td>Name&nbsp;</td>
<td><input class="norm" type="text" name="authorname" value="'.$thisname.'"></td></tr>
<tr><td>Sort as&nbsp;</td>
<td><input class="norm" type="text" name="sortname" value="'.$thissortname.'"></td></tr>
<tr><td>Born&nbsp;</td>
<td><input class="norm" type="text" name="born" value="'.$thisborn.'"></td></tr>
<tr><td>Died&nbsp;</td>
<td><input class="norm" type="text" name="dead" value="'.$thisdead.'"></td></tr>
<tr><td valign="top">Bio </td>
<td><textarea class="norm" rows="20" name="bodytext">'.$thisbio.'</textarea></td></tr>
<tr><td>Biosource&nbsp;</td>
<td><input class="norm" type="text" name="source" value="'.$thissource.'"></td></tr>
<tr><td>Picture </td>
<td><input type="file" size="49" name="picture"></td></tr>
<tr><td></td><td>
<input type="submit" value="Save"></td>
</form></table>';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLupdateauthor( $id, $print_on = true ) {
	global $authorname, $sortname, $born, $dead, $bodytext, $source;

	$result = RMLfiresql("SELECT maintainer FROM author WHERE id=$id");
	$thisrow = pg_Fetch_Object($result,0);
	$maintainer = $thisrow->maintainer;

	if( !hasRights( 'editauthor', array( $maintainer ) ) ) {
		$out = "ERROR: Update Author not allowed, bad user...";
	} elseif( $authorname == '' ) {
		$out = "ERROR: Update author, bad authorname.";
	} elseif( $sortname == '' ) {
		$out = "ERROR: Update author, bad sortname.";
	} elseif( $born == '' ) {
		$out = "ERROR: Update author, bad birthyear.";
	} else {

		$target_path = "./authors/";
		$target_path = $target_path . "author" .$id;

		if( move_uploaded_file($_FILES['picture']['tmp_name'], $target_path) ) {
			$filename = "author" . "$id";
			RMLfiresql("UPDATE author SET picture='$filename' WHERE id=$id");
		}

		RMLfiresql("UPDATE author SET name='$authorname', sort_name='$sortname', born=$born, dead=$dead, bio='$bodytext', source='$source' WHERE id=$id");
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function RMLdisplayauthorlocation( $id, $print_on = true ) {
	global $letter;

	$out = '';
	if( $id ) {
		$out = "\n".'<a class="button next" href="?author=view&amp;letter='.$letter.'">Authors</a>';
	}
	return processOutput( $out, $print_on );
}

function getAge( $born, $dead )
{
	if ( !is_numeric( $born ) || $born == 0 ) {
		return '';
	}
// ewa:	now more exact, todo: make it shorter ö.O somehow, afaik needs php >= 5.3
//		e.g. ... $age = date_diff( date_create( ), date_create(), true );
	if( !is_numeric( $dead ) || (int)$dead == 0 ) {
		switch( strlen( $born ) ) {
			case 4:
				$age = '~' . ( date('Y') - $born );
			break;
			case 6:
				$age = '~'
					. (
						date('Y') - substr( $born, 0, 4 )
						- ( ( substr( $born, 4, 2 ) > date( 'm' ) ) ? 1 : 0 )
					);
			break;
			case 8:
				$age = date('Y')
					- substr( $born, 0, 4 )
					- ( ( substr( $born, 4, 2 ) >= date( 'm' ) && substr( $born, 6, 2 ) > date( 'd' ) ) ? 1 : 0 );
			break;
			default:
		}
	} else {
		$len = min( strlen( $born ), strlen( $dead ) );
		$age = substr( $dead, 0, 4 ) - substr( $born, 0, 4 );
		if ( $len == 6 ) {
			$age = '~' .( $age - ( ( substr( $born, 4, 2 ) > substr( $dead, 4, 2 ) ) ? 1 : 0 ) );
		} elseif ( $len == 8 ) {
			$age = $age - ( ( substr( $born, 4, 2 ) >= substr( $dead, 4, 2 ) && substr( $born, 6, 2 ) > substr( $dead, 6, 2 ) ) ? 1 : 0 );
		} else {
			$age = '~' .$age;
		}
	}
	return $age;
}

// ewa: eats date like 'YYYYMMDD' or 'YYYY-MM-DD'
function getDateFormatted( $d, $f = 'DD MMM YYYY', $s = array( 'space' => '&nbsp;', 'datsep' => '.' ) )
{
	$d = str_replace( '-', '', $d );
	$arrMonths = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'Oktober', 'November', 'December' );
	switch ( $f ) {
		case 'DD MMM YYYY':
			return ( ( strlen( $d ) >= 8 ) ? substr( $d, 6, 2 ).$s['space'] : '' )
					.( ( strlen( $d ) >= 6 && ( substr( $d, 4, 2 ) <= 12 ) && ( substr( $d, 4, 2 ) >= 1 ) ) ? substr( $arrMonths[ ( substr( $d, 4, 2 ) - 1 ) ], 0, 3 ) .$s['space'] : '' )
					.substr( $d, 0, 4 );
		break;
		case 'DD MMMM YYYY':
			return ( ( strlen( $d ) >= 8 ) ? substr( $d, 6, 2 ).$s['space'] : '' )
					.( ( strlen( $d ) >= 6 && ( substr( $d, 4, 2 ) <= 12 ) && ( substr( $d, 4, 2 ) >= 1 ) ) ? $arrMonths[ ( substr( $d, 4, 2 ) - 1 ) ] .$s['space'] : '' )
					.substr( $d, 0, 4 );
		break;
		case 'DD.MM.YYYY':
		default:
			return ( ( strlen( $d ) >= 8 ) ? substr( $d, 6, 2 ).'.' : '' )
					.( ( strlen( $d ) >= 6 ) ? substr( $d, 4, 2 ).'.' : '' )
					.substr( $d, 0, 4 );
	}
}

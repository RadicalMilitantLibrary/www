<?php
// ============================================================================

function RMLdisplaysearch( $print_on = true) {
	global $search, $query,$lang;
	$language = getLanguage($lang);
	$out = "\n".'<div class="box"><div class="boxheader"><b>Search</b></div>'
	.'<div class="boxtext">
	<div style="margin-top: 1ex; margin-bottom: 1ex;"><small>Search is a case insensitive substring search</small></div>
	<table>
		<form method="get"><input type="hidden" name="function" value="search">
			<fieldset>
				<tr><td>Search</td><td>: <input type="text" size="40" name="query" value="'.preg_replace( ["/''/"], ["'"], $query).'"/></td><td> in </td><td>
					<select name="search" autofocus="'.$search.'">
						<option value="title">Title</option>
						<option value="isbn">ISBN</option>
						<option value="author">Author</option>
					</select>
				</td></tr>';
				if($language!=""){ $out .= '<tr><td></td><td><small>Only looking for <b>'.$language.'</b> books. <a href="?function=search">disable</a></small></td></tr>
											 <input type="hidden" name="lang" value="'.$lang.'">';
				}
				$out .= '<tr><td></td><td><input class="formbutton" type="submit" value="Search"/></td></tr>
			</fieldset>
		</form>
	</table><div class=\"inlineclear\"> &nbsp; </div></div></div>';
	
	if($query) {
		$querystripped = preg_replace(["/(%| )/"],[""], $query);
		$queryminlength = 3;
		$maxresultsshown = 50;
		$languagecondition = "";
		if($language!="") {
			$languagecondition = "AND language='$language'";
		}
		if(strlen($querystripped) < $queryminlength) {
			$out .= "Query must be at least ".$queryminlength." characters!";
		}
		else if($search=="title" || $search=="isbn") {	
			if($search == "title") {
				$result = RMLfiresql("SELECT title, subtitle, id, author_id, year, teaser,(SELECT AVG(level) FROM forum WHERE thread_id=document.id AND level > 0) AS score, (SELECT name FROM author WHERE id=document.author_id) AS authorname FROM \"document\" WHERE (title ILIKE '%$query%' OR subtitle ILIKE '%$query%') AND status=3 $languagecondition;");
			} else if ($search == "isbn") {
				$result = RMLfiresql("SELECT title, subtitle, id, author_id, year, teaser,(SELECT AVG(level) FROM forum WHERE thread_id=document.id AND level > 0) AS score, (SELECT name FROM author WHERE id=document.author_id) AS authorname FROM \"document\" WHERE (copyright ILIKE '%ISBN%$query%') AND status=3 $languagecondition;");
			}
			
			$numberofresults = pg_numrows($result);
			$out .= "<p>Query returned ".$numberofresults." results, showing ".min([$numberofresults,$maxresultsshown]).".</p>";
			
			for($row=0;$row<$numberofresults && $row<$maxresultsshown;$row++) {
				$thisrow = pg_Fetch_Object($result,$row);
				$thistitle = $thisrow->title;
				$thissubtitle = $thisrow->subtitle;
				$thisid = $thisrow->id;
				$thisyear = $thisrow->year;
				$thisauthorid = $thisrow->author_id;
				$thisauthor = $thisrow->authorname;
				$thisteaser = $thisrow->teaser;
				$avgscore = $thisrow->score;

				if( strlen( $thisteaser ) > 400 ) {
					$thisteaser = substr( $thisteaser, 0, 400 ) .' ...';
					$thisteaser = strip_tags( $thisteaser );
				}
			
				$out .= "\n"
				.'<div class="box">
						<p class="boxheader"><a href="?document=view&amp;id='.$thisid.'">
							<img class="Cover" alt="Cover" src="./covers/cover'.$thisid.'"/><b>'.$thistitle.'</b>
						</a>';
						if($thissubtitle) { $out .= '― <small>'.$thissubtitle.'</small>'; }
						$out .= 
						'</p><p class="boxtext">
							<small><a href="?author=view&amp;id='.$thisauthorid.'">'.$thisauthor.'</a>,
							<b>' .$thisyear .'</b></small>
							<span class="right-float">' .getRatingDisplay( $avgscore ) .'</span>
						</p>
						<p class="boxtext">'.$thisteaser.'</p>
						<div class="inlineclear"></div>
					</div>';
			}
		} else if($search=="author"){
			$result = RMLfiresql("SELECT sort_name,bio,picture,born,dead,id FROM author WHERE (name ILIKE '%$query%');");
			
			$numberofresults = pg_numrows($result);
			$out .= "<p>Query returned ".$numberofresults." results, showing ".min([$numberofresults,$maxresultsshown]).".</p>";
			for($row=0;$row<$numberofresults && $row<$maxresultsshown;$row++) {
				$thisrow = pg_Fetch_Object($result,$row);
				$thisname = $thisrow->sort_name;
				$bio = $thisrow->bio;
				$thisid = $thisrow->id;
				$born = $thisrow->born;
				$dead = $thisrow->dead;
				$thispicture = $thisrow->picture;

				$age = getAge( $born, $dead ); //do something with age
				if ( $born == 0 ) {
					$born = 'Unknown'; $age = '';
				} else {
					$born = substr( $born, 0, 4 );
				}
				if ( $dead == 0 ) {
					$dead = '';
				} else {
					$dead = substr( $dead, 0, 4 );
				}
				$bio = strip_tags( $bio );
				if( strlen( $bio ) > 400 ) {
					$bio = substr( $bio, 0, 400 ) . ' …';
				}
			
				$out .= "\n"
				.'<div class="box">
						<p class="boxheader"><a href="?author=view&amp;id='.$thisid.'">
							<img class="Author" alt="Author" src="./authors/author'.$thisid.'"/><b>'.$thisname.'</b>
						</a>';
						$out .= 
						'</p><p class="boxtext">
							<small>'.$born.' - '.$dead.'</small>
						</p>
						<p class="boxtext">'.$bio.'</p>
						<div class="inlineclear"></div>
					</div>';
			}
		}
	}
	
	
	return processOutput( $out, $print_on);
}

?>

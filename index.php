<?php
error_reporting(E_ALL ^E_NOTICE ^E_DEPRECATED);
ini_set('display_errors', '1');
// ============================================================================
//  "Frontpage" for Radical Militant Library
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

$starttime = microtime();
$Version = "0.5.7";
$itemprpage = 20;
//define('SETTINGS_FILENAME', './settings.php');

//require SETTINGS_FILENAME;
//checkSettings( SETTINGS_FILENAME );
require 'settings.php';
require 'RML.common.php';
require 'RML.helper.php';
require 'RML.database.php';
require 'RML.user.php';
require 'RML.document.php';
require 'RML.forum.php';
require 'RML.author.php';
require 'RML.image.php';
require 'RML.lists.php';
require 'RML.subject.php';

$id = ( is_numeric($_REQUEST['id']) && $_REQUEST['id'] > 0 ) ? $_REQUEST['id'] : 0 ;
if(is_numeric($_REQUEST['parent'])) { $parent = $_REQUEST['parent']; }
if(is_numeric($_REQUEST['styleid'])) { $styleid = $_REQUEST['styleid']; }
if(is_numeric($_REQUEST['section'])) { $section = $_REQUEST['section']; }
if(is_numeric($_REQUEST['paragraphtype'])) { $paratype = $_REQUEST['paragraphtype']; }

$para = RMLpreparestring($_REQUEST['para']);
$function = RMLpreparestring($_REQUEST['function']);
$message = RMLpreparestring($_REQUEST['message']);
$blog = RMLpreparestring($_REQUEST['blog']);
$document = RMLpreparestring($_REQUEST['document']);
$subject = RMLpreparestring($_REQUEST['subject']);
$body = RMLpreparestring($_REQUEST['body']);
$username = RMLpreparestring($_REQUEST['username']);
$password = RMLpreparestring($_REQUEST['password']);
$password2 = RMLpreparestring($_REQUEST['password2']);
$mail = RMLpreparestring($_REQUEST['mail']);
$author = RMLpreparestring($_REQUEST['author']);
$cookie = RMLpreparestring($_COOKIE['RML']);
$title = RMLpreparestring($_REQUEST['title']);
$subtitle = RMLpreparestring($_REQUEST['subtitle']);
$year = RMLpreparestring($_REQUEST['year']);
$ISBN = RMLpreparestring($_REQUEST['ISBN']);
$keywords = RMLpreparestring($_REQUEST['keywords']);
$copyright = RMLpreparestring($_REQUEST['copyright']);
$teaser = RMLpreparestring($_REQUEST['teaser']);
$letter = RMLpreparestring($_REQUEST['letter']);
$bodytext = RMLpreparestring($_REQUEST['bodytext']);
$source = RMLpreparestring($_REQUEST['source']);
$headline = RMLpreparestring($_REQUEST['headline']);
$authorname = RMLpreparestring($_REQUEST['authorname']);
$sortname = RMLpreparestring($_REQUEST['sortname']);
$born = RMLpreparestring($_REQUEST['born']);
$dead = RMLpreparestring($_REQUEST['dead']);
$sequence = RMLpreparestring($_REQUEST['sequence']);
$format = RMLpreparestring($_REQUEST['format']);
$page = RMLpreparestring($_REQUEST['page']);
$comment = RMLpreparestring($_REQUEST['comment']);
$score = RMLpreparestring($_REQUEST['score']);
$news = RMLpreparestring($_REQUEST['news']);
$footnote = RMLpreparestring($_REQUEST['footnote']);
$note = RMLpreparestring($_REQUEST['note']);
$messageto = RMLpreparestring($_REQUEST['messageto']);
$messagesubject = RMLpreparestring($_REQUEST['messagesubject']);
$style = RMLpreparestring($_REQUEST['style']);
$lists = RMLpreparestring($_REQUEST['lists']);
$docid = RMLpreparestring($_REQUEST['docid']);

switch( $para ) {
	case 'delete':
		RMLdeleteelement( $id );
		$sequence = $sequence - 1;
		header( 'Location: ?document=view&id='.$id.'&section='.$section.'#s'.$sequence );
	break;
}

switch( $subject ) {
	case 'new':
		RMLnewsubject( false );
		header( 'Location: ?subject=view&id=0' );
	break;
	case 'update':
		RMLupdatesubject( $id, $headline, $bodytext );
		header( 'Location: ?subject=view&id='.$id );
	break;
}

switch($lists) {
	case 'new':
		RMLnewlist(false);
		header('Location: ?lists=view&id=0');
	break;
	case 'add':
		RMLaddtolist($id, false);
		header("Location: ?lists=view&id=$id");
	break;
}

switch( $author ) {
	case 'add':
		RMLaddauthor();
		header( 'Location: ?author=view&letter=A' );
	break;
	case 'update':
		RMLupdateauthor( $id );
		header( 'Location: ?author=view&id='.$id );
	break;
}

switch( $function ) {
	case 'login':
		if( $password <> "" ) {
			RMLlogin();
			break;
		}
	break;
	case 'logout':
		RMLlogout();
		header("Location: .");
	break;
	case 'newuser':
		RMLcreatenewuser();
	break;
	case 'update':
		RMLupdateelement( $id );
		header( 'Location: ?document=view&id='.$id.'&section='.$section.( ( isset( $sequence ) && $sequence != '' && is_numeric( substr( $sequence, 1, strlen( $sequence ) - 1 ) ) )?'#s'.$sequence:'') );
	break;
	case 'flush':
		RMLflushdocument( $id );
		header("Location: ?document=view&id=$id");
	break;
	case 'publish':
		RMLpublishdocument( $id );
	break;
	case 'delete':
		RMLdeletedocument( $id );
		$user = RMLgetcurrentuser();
		header( 'Location: ?function=user&user='.$user );
	break;
	case 'download':
		if ( !in_array( $format, array( 'epub','html','markdown','text' ) ) ) {
			$format = 'epub';
		}
		RMLdownloaddocument( $id, $format );
		header( 'Location: ?document=view&id='.$id );
	break;
	case 'confirm':
		RMLconfirmdocument( $id );
	break;
	case 'deny':
		RMLdenydocument( $id );
	break;
	case 'withdraw':
		RMLwithdrawdocument( $id, false );
		$user = RMLgetcurrentuser();
		header( 'Location: ?function=user' );
	break;
}

switch( $message ) {
	case 'delete':
		RMLdeletemessage( $id );
	break;
	case 'send':
		$out .= RMLsendmessage( $messageto, $body, RMLgetcurrentuser(), $messagesubject, false );
		if( $out == '' ) {
			header( 'Location: ?function=user' );
		} else {
			
		}
	break;
}

switch ( $document ) {
	case 'create':
		RMLcreatedocument();
		header( 'Location: ?function=user' );
	break;
	case 'update':
		RMLgetcurrentuser();
		RMLupdatedocument( $id );
		header( 'Location: ?document=view&id='.$id );
	break;
	case 'avatar':
		RMLuploadavatar();
		header( 'Location: ?function=user' );
	break;
}

switch ($comment) {
	case 'save':
		RMLsaveforum( $id, false );
		header( 'Location: ?document=view&id='.$id );
	break;
} 

switch ( $news ) {
	case 'delete':
		RMLdeletenews( $id, false );
		header( 'Location: ?news=view' );
	break;
	case 'save':
		RMLsavenews( false );
		header( 'Location: ?news=view' );
	break;
}

switch ( $style ) {
	case 'save':
		RMLsavestylesheet();
		header( 'Location: ?function=user' );
	break;
	case 'update':
		RMLupdatestylesheet($id, false );
		header( 'Location: ?function=user' );
	break;
}

$out .= RMLdisplayhead( false )
	.RMLdisplaytop( false )
/*	.RMLdisplayleft( false ) */
	.RMLdisplaymain( $id, false )
	.RMLdisplaybottom( false )
	.RMLdisplayend( false );
return processOutput( $out, true );

<?php
// ============================================================================
//  Database functions for Radical Militant Library
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

$conn;
$SQLcounter = 0;
$SQLtime = 0;
$SQLsize = 0;
// vv01f: those should be attributes to a static object, so no globals are needed; not sure though how to do it in php yet
//   to be called e.g. pgDB::query() and holding its properties by itself
/* idea * /
class pgDB	// https://secure.php.net/manual/en/language.oop5.static.php
{
	private static $conn;
	private $connection_string = '';
	private $counter = 0;
	private $time = 0;
	private $size = 0;

	public function __construct( $params ) {	//array( 'user' => $dbuser, 'dbname' => $dbname, 'host' => $dbhost, 'password' => $dbpass )
		foreach( $param as $k => $v ) {
			$this->connection_string .= $k .'=' .$v .' ';
		}
		$this::connect();//unsure
	}

	public static function connect()	// method handling static var
	{
		$this::conn = pg_pconnect( $this->connection_string )
			or die("<h1>Opendatabase ERROR : Fuck Me...</h1>");
	}

	// ...
}
/* * */

// ============================================================================

function RMLopendatabase()
{
	global $dbname, $dbuser, $dbhost, $dbpass, $conn;

	if ( isset( $dbpass ) && $dbpass !== '' ) {
		$conn = pg_pconnect( "user=$dbuser dbname=$dbname host=$dbhost password=$dbpass" )
			or die( "<h1>Opendatabase ERROR : Fuck Me...</h1>" );

		return $conn;
	} else {
		die( "<h1>Opendatabase ERROR : No DB password set...</h1>" );
	}
}

// ============================================================================

function RMLgrabconnection()
{
	global $conn;

	if( !$conn ) {
		$conn = RMLopendatabase();
	}

	return $conn;
}

// ============================================================================

function RMLfireSQL( $SQL )
{
	global $conn;
	global $SQLcounter;
	global $SQLsize;

	$conn = RMLgrabconnection();

	$result = pg_exec( $conn, $SQL );

	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$i = pg_num_fields( $result );
		for( $j = 0; $j < $i; $j++ ) {
			$SQLsize += pg_field_prtlen( $result, $j );
		}
	}

	$SQLcounter++;
	return $result;
}

function RMLclosedb() // vv01f: later to be called by a deconstructor, now just for clarity
{
	global $conn;

	if( !$conn ) {
		return false;
	} else {
		return pg_close( $conn );
	}
}

?>

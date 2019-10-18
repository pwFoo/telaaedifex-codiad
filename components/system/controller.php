<?php
require_once('../../common.php');

if ( ! isset( $_POST['action'] ) ) {
	
	die( formatJSEND( "error", "Missing parameter" ) );
}

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

if ( $_POST['action'] == 'create_default_tables' ) {
	
	if( is_admin() ) {
		
		global $sql;
		$result = $sql->create_default_tables();
		
		//echo var_dump( $result );
		
		if( $result === true ) {
			
			exit( formatJSEND( "success", "Created tables." ) );
		} else {
			
			exit( formatJSEND( "error", array( "message" => "Could not create tables.", "result" => $result ) ) );
		}
	} else {
		
		exit( formatJSEND( "error", "Only admins can use this method." ) );
	}
}
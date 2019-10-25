<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');
require_once('./class.project.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

$Project = new Project();
$Project->projects = $Project->get_projects();

if( ! is_array( $Project->projects ) ) {
	
	$Project->projects = array();
}


if( $_GET['action'] == 'add_user' ) {
	
	$invalid_users = array(
		"",
		"null",
		"undefined"
	);
	
	if( ! isset( $_GET['access'] ) || in_array( $_GET['access'], $invalid_users ) || ! in_array( $_GET['access'], array_keys( Permissions::LEVELS ) ) ) {
		
		exit( formatJSEND( "error", "No access set." ) );
	} else {
		
		$access = Permissions::LEVELS[$_GET['access']];
	}
	
	if( isset( $_GET['user_id'] ) && ! in_array( $_GET['user_id'], $invalid_users ) ) {
		
		$user = $_GET['user_id'];
	} else {
		
		exit(  formatJSEND( "error", "No user id set." ) );
	}
	
	if( isset( $_GET['project_path'] ) && $_GET['project_path'] != '' ) {
		
		$project = $_GET['project_path'];
	} else {
		
		exit( formatJSEND( "error", "No project path set." ) );
	}
	
	if( $Project->check_owner( $_GET['project_path'], true ) ) {
		
		return $Project->add_user( $project, $user, $access );
	} else {
		
		exit( formatJSEND( "error", "You can not manage this project." ) );
	}
}


//////////////////////////////////////////////////////////////////
// Create Project
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'create' ) {
	
	$name = $_GET['project_name'];
	$public = ( $_GET['public_project'] != 'true' ) ? false : true;
	$path = ( $_GET['project_path'] != '' ) ? $_GET['project_path'] : $_GET['project_name'];
	$return = $Project->Create( $path, $name, $public );
	exit( json_encode( $return ) );
}

//////////////////////////////////////////////////////////////////
// Return Current
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'current' ) {
	
	if( isset( $_SESSION['project'] ) ) {
		
		echo formatJSEND( "success", $_SESSION['project'] );
	} else {
		
		echo formatJSEND( "error", "No Project Returned" );
	}
}

//////////////////////////////////////////////////////////////////
// Delete Project
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'delete' ) {
	
	if( isset( $_GET['project_path'] ) ) {
		
		$Project->path = $_GET['project_path'];
		$Project->Delete();
	}
}

//////////////////////////////////////////////////////////////////
// Get Project Access
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'get_access' ) {
	
	$access = $Project->get_access( $_GET['project_id'] );
	echo formatJSEND( "success", $access );
}

//////////////////////////////////////////////////////////////////
// Get Current Project
//////////////////////////////////////////////////////////////////

$no_return = false;
if( isset( $_GET['no_return'] ) ) {
	
	$no_return = true;
}

if( $_GET['action'] == 'get_current' ) {

	if( ! isset( $_SESSION['project'] ) ) {
		
		// Load default/first project
		if( $no_return ) {
			
			$Project->no_return = true;
		}
		$project = $Project->GetFirst();
		
		if( $project == null ) {
			
			exit( formatJSEND( "error", "Error, Could not load a projet." ) );
		} else {
			
			exit( formatJSEND( "success", $project ) );
		}
	} else {
		
		// Load current
		$Project->path = $_SESSION['project'];
		$project_name = $Project->GetName();
		if( ! $no_return ) {
			
			exit( formatJSEND( "success", array( "name" => $project_name, "path" => $_SESSION['project'] ) ) );
		}
	}
}

//////////////////////////////////////////////////////////////////
// Check Project Owner
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'get_owner' ) {
	
	$Project->path = $_GET['project_path'];
	$owner = $Project->get_owner();
	try {
		
		$return = json_decode( $owner );
		exit( formatJSEND( "error", null ) );
	} catch( exception $e ) {
		
		exit( formatJSEND( "success", array( "owner" => $owner ) ) );
	}
}

//////////////////////////////////////////////////////////////////
// Open Project
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'open' ) {
	
	if( ! isset( $_GET['path'] ) || ! Permissions::has_read( $_GET['path'] ) ) {
		
		die( formatJSEND( "error", "No Access to path " . $_GET['path'] ) );
	}
	$Project->path = $_GET['path'];
	$Project->Open();
}

if( $_GET['action'] == 'remove_user' ) {
	
	$invalid = array(
		"",
		"null",
		"undefined"
	);
	
	if( ! in_array( $_GET['username'], $invalid ) ) {
		
		$Project->user = $_GET['username'];
	} else {
		
		exit( formatJSEND( "error", "No username set." ) );
	}
	
	if(	! in_array( $_GET['project_path'], $invalid ) ) {
		
		$Project->path = $_GET['project_path'];
	} else {
		
		exit( formatJSEND( "error", "No project path set." ) );
	}
	
	if(	! in_array( $_GET['project_id'], $invalid ) ) {
		
		$Project->project_id = $_GET['project_id'];
	} else {
		
		exit( formatJSEND( "error", "No project id set." ) );
	}
	
	if( $Project->check_owner( $_GET["project_path"], true ) ) {
		
		$Project->remove_user();
	} else {
		
		exit( formatJSEND( "error", "You can not manage this project." ) );
	}
}

//////////////////////////////////////////////////////////////////
// Rename Project
//////////////////////////////////////////////////////////////////

if( $_GET['action'] == 'rename' ) {
	
	if( ! isset( $_GET['project_path'] ) || ! Permissions::has_owner( $_GET['project_path'] ) ) {
		
		die( formatJSEND( "error", "No Access" ) );
	}
	$Project->path = $_GET['project_path'];
	$Project->Rename();
}


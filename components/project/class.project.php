<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once( '../../common.php' );

class Project extends Common {
	
	//////////////////////////////////////////////////////////////////
	// PROPERTIES
	//////////////////////////////////////////////////////////////////
	
	public $name         = '';
	public $path         = '';
	public $gitrepo      = false;
	public $gitbranch    = '';
	public $projects     = '';
	public $no_return    = false;
	public $assigned     = false;
	public $command_exec = '';
	
	//////////////////////////////////////////////////////////////////
	// METHODS
	//////////////////////////////////////////////////////////////////
	
	// -----------------------------||----------------------------- //
	
	//////////////////////////////////////////////////////////////////
	// Construct
	//////////////////////////////////////////////////////////////////
	
	public function __construct() {
		
		$this->projects = $this->get_projects();
	}
	
	//////////////////////////////////////////////////////////////////
	// NEW METHODS
	//////////////////////////////////////////////////////////////////
	
	public function add_project( $project_name, $project_path, $owner = null ) {
		
		if( $owner == null ) {
			
			$owner = $_SESSION["user"];
		} else {
			
			$owner = 'nobody';
		}
		
		$sql = "INSERT INTO `projects`( `name`, `path`, `owner` ) VALUES ( ?, ?, ? );";
		$bind = "sss";
		$bind_variables = array( $project_name, $project_path, $owner );
		$return = sql::sql( $sql, $bind, $bind_variables, formatJSEND( "error", "Error creating project $project_name." ) );
		
		return( $return );
	}
	
	public function delete_project( $project_name, $project_path, $owner = null ) {
		
		if( $owner == null ) {
			
			$owner = $_SESSION["user"];
		} else {
			
			$owner = 'nobody';
		}
		
		$owner = $_SESSION["user"];
		$sql = "DELETE FROM `projects` WHERE `name`=? AND `path`=? AND ( `owner`=? OR `owner`='nobody' );";
		$bind = "sss";
		$bind_variables = array( $project_name, $project_path, $owner );
		$return = sql::sql( $sql, $bind, $bind_variables, formatJSEND( "error", "Error deleting project $project_name." ) );
		
		try {
			
			$json = json_decode( $return, true );
			exit( $return );
		} catch( exception $e ) {
			
			exit( formatJSEND( "success", "Successfully deleted project $project_name." ) );
		}
	}
	
	public function get_projects() {
		
		$sql = "SELECT * FROM `projects` WHERE `owner`=? OR `owner`='nobody' ORDER BY `name`;";
		$bind = "s";
		$bind_variables = array( $_SESSION["user"] );
		$return = sql::sql( $sql, $bind, $bind_variables, formatJSEND( "error", "Error fetching projects." ) );
		
		if( mysqli_num_rows( $return ) > 0 ) {
			
			$return = mysqli_fetch_all( $return, MYSQLI_ASSOC );
		} else {
			
			$return = formatJSEND( "error", "Error fetching projects." );
		}
		
		return( $return );
	}
	
	public function rename_project( $old_name, $new_name, $path ) {
		
		$sql = "SELECT * FROM `projects` WHERE `name`=? AND `path`=? AND ( `owner`=? OR `owner`='nobody' );";
		$bind = "sss";
		$bind_variables = array( $old_name, $path, $_SESSION["user"] );
		$return = sql::sql( $sql, $bind, $bind_variables, formatJSEND( "error", "Error fetching projects." ) );
		
		if( mysqli_num_rows( $return ) > 0 ) {
			
			$sql = "UPDATE `projects` SET `name`=? WHERE `name`=? AND `path`=? AND ( `owner`=? OR `owner`='nobody' );";
			$bind = "ssss";
			$bind_variables = array( $new_name, $old_name, $path, $_SESSION["user"] );
			$return = sql::sql( $sql, $bind, $bind_variables, formatJSEND( "error", "Error renaming project." ) );
		} else {
			
			exit( formatJSEND( "error", "Error renaming project, could not find specified project." ) );
		}
	}
	
	//////////////////////////////////////////////////////////////////
	// OLD METHODS
	//////////////////////////////////////////////////////////////////
	
	// -----------------------------||----------------------------- //
	
	//////////////////////////////////////////////////////////////////
	// Get First (Default, none selected)
	//////////////////////////////////////////////////////////////////
	
	public function GetFirst() {
		
		$this->name = $this->projects[0]['name'];
		$this->path = $this->projects[0]['path'];
		
		// Set Sessions
		$_SESSION['project'] = $this->path;
		
		if ( ! $this->no_return ) {
			
			echo formatJSEND( "success", $this->projects[0] );
		}
	}
	
	//////////////////////////////////////////////////////////////////
	// Get Name From Path
	//////////////////////////////////////////////////////////////////
	
	public function GetName() {
		
		foreach ( $this->projects as $project => $data ) {
			
			if ( $data['path'] == $this->path ) {
				
				$this->name = $data['name'];
			}
		}
		return $this->name;
	}
	
	//////////////////////////////////////////////////////////////////
	// Open Project
	//////////////////////////////////////////////////////////////////
	
	public function Open() {
		
		$pass = false;
		foreach ( $this->projects as $project => $data ) {
			
			if ( $data['path'] == $this->path ) {
				
				$pass = true;
				$this->name = $data['name'];
				$_SESSION['project'] = $data['path'];
			}
		}
		if ( $pass ) {
			
			echo formatJSEND( "success", array( "name" => $this->name, "path" => $this->path ) );
		} else {
			
			echo formatJSEND( "error", "Error Opening Project" );
		}
	}
	
	//////////////////////////////////////////////////////////////////
	// Create
	//////////////////////////////////////////////////////////////////
	
	public function Create() {
			
		if ( $this->name != '' && $this->path != '' ) {
			
			$this->path = $this->cleanPath();
			$this->name = htmlspecialchars( $this->name );
			if ( ! $this->isAbsPath( $this->path ) ) {
				
				$this->path = $this->SanitizePath();
			}
			if ( $this->path != '' ) {
				
				$pass = $this->checkDuplicate();
				if ( $pass ) {
					
					if ( ! $this->isAbsPath( $this->path ) ) {
						
						mkdir( WORKSPACE . '/' . $this->path );
					} else {
						
						if ( defined( 'WHITEPATHS' ) ) {
							
							$allowed = false;
							foreach ( explode( ",", WHITEPATHS ) as $whitepath ) {
								
								if ( strpos( $this->path, $whitepath ) === 0 ) {
									
									$allowed = true;
								}
							}
							if ( ! $allowed) {
								
								die( formatJSEND( "error", "Absolute Path Only Allowed for " . WHITEPATHS ) );
							}
						}
						if ( ! file_exists( $this->path ) ) {
							
							if ( ! mkdir( $this->path . '/', 0755, true ) ) {
								
								die( formatJSEND( "error", "Unable to create Absolute Path" ) );
							}
						} else {
							
							if ( ! is_writable( $this->path ) || ! is_readable( $this->path ) ) {
								
								die( formatJSEND( "error", "No Read/Write Permission" ) );
							}
						}
					}
					$this->projects[] = array( "name" => $this->name, "path" => $this->path );
					$this->add_project( $this->name, $this->path );
					
					// Pull from Git Repo?
					if ( $this->gitrepo && filter_var( $this->gitrepo, FILTER_VALIDATE_URL ) !== false ) {
						
						$this->gitbranch = $this->SanitizeGitBranch();
						if ( ! $this->isAbsPath( $this->path ) ) {
							
							$this->command_exec = "cd " . escapeshellarg( WORKSPACE . '/' . $this->path ) . " && git init && git remote add origin " . escapeshellarg( $this->gitrepo ) . " && git pull origin " . escapeshellarg( $this->gitbranch );
						} else {
							
							$this->command_exec = "cd " . escapeshellarg( $this->path ) . " && git init && git remote add origin " . escapeshellarg( $this->gitrepo ) . " && git pull origin " . escapeshellarg( $this->gitbranch );
						}
						$this->ExecuteCMD();
					}
					
					echo formatJSEND( "success", array( "name" => $this->name, "path" => $this->path ) );
				} else {
					
					echo formatJSEND( "error", "A Project With the Same Name or Path Exists" );
				}
			} else {
				
				echo formatJSEND( "error", "Project Name/Folder not allowed" );
			}
		} else {
			
			echo formatJSEND( "error", "Project Name/Folder is empty" );
		}
	}
	
	//////////////////////////////////////////////////////////////////
	// Sanitize GitBranch
	//////////////////////////////////////////////////////////////////
	
	public function SanitizeGitBranch() {
		
		$sanitized = str_replace( array( "..", chr(40), chr(177), "~", "^", ":", "?", "*", "[", "@{", "\\" ), array( "" ), $this->gitbranch );
		return $sanitized;
	}
	
	//////////////////////////////////////////////////////////////////
	// Rename
	//////////////////////////////////////////////////////////////////
	
	public function Rename() {
		
		$revised_array = array();
		foreach ( $this->projects as $project => $data ) {
			
			if ( $data['path'] != $this->path ) {
				
				$revised_array[] = array( "name" => $data['name'], "path" => $data['path'] );
			} else {
				
				$this->rename_project( $data['name'], $_GET['project_name'], $data['path'] );
			}
		}
		
		$revised_array[] = $this->projects[] = array( "name" => $_GET['project_name'], "path" => $this->path );
		// Response
		echo formatJSEND("success", null);
	}
	
	//////////////////////////////////////////////////////////////////
	// Delete Project
	//////////////////////////////////////////////////////////////////
	
	public function Delete() {
		
		$revised_array = array();
		foreach ( $this->projects as $project => $data ) {
			
			if ( $data['path'] != $this->path ) {
				
				$revised_array[] = array( "name" => $data['name'], "path" => $data['path'] );
			} else {
				
				$this->delete_project( $data['name'], $data['path'] );
			}
		}
	}
	
	//////////////////////////////////////////////////////////////////
	// Check Duplicate
	//////////////////////////////////////////////////////////////////
	
	public function CheckDuplicate() {
		
		$pass = true;
		foreach ( $this->projects as $project => $data ) {
			
			if ( $data['name'] == $this->name || $data['path'] == $this->path ) {
				
				$pass = false;
			}
		}
		return $pass;
	}
	
	//////////////////////////////////////////////////////////////////
	// Sanitize Path
	//////////////////////////////////////////////////////////////////
	
	public function SanitizePath() {
		
		$sanitized = str_replace( " ", "_", $this->path );
		return preg_replace( '/[^\w-]/', '', strtolower( $sanitized ) );
	}
	
	//////////////////////////////////////////////////////////////////
	// Clean Path
	//////////////////////////////////////////////////////////////////
	
	public function cleanPath() {
		
		// prevent Poison Null Byte injections
		$path = str_replace( chr( 0 ), '', $this->path );
		
		// prevent go out of the workspace
		while( strpos( $path, '../' ) !== false ) {
			
			$path = str_replace( '../', '', $path );
		}
		
		return $path;
	}
	
	//////////////////////////////////////////////////////////////////
	// Execute Command
	//////////////////////////////////////////////////////////////////
	
	public function ExecuteCMD() {
		
		if ( function_exists( 'system' ) ) {
			
			ob_start();
			system( $this->command_exec );
			ob_end_clean();
		}  elseif( function_exists( 'passthru' ) ) {
			
			//passthru
			ob_start();
			passthru($this->command_exec);
			ob_end_clean();
		}  elseif ( function_exists( 'exec' ) ) {
			
			//exec
			exec( $this->command_exec, $this->output );
		} elseif ( function_exists( 'shell_exec' ) ) {
			
			//shell_exec
			shell_exec( $this->command_exec );
		}
	}
}
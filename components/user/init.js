/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */
(function(global, $) {

	var codiad = global.codiad;

	$(function() {
		codiad.user.init();
	});

	codiad.user = {
		
		loginForm: $('#login'),
		controller: 'components/user/controller.php',
		dialog: 'components/user/dialog.php',
		
		//////////////////////////////////////////////////////////////////
		// Initilization
		//////////////////////////////////////////////////////////////////
		
		init: async function() {
			var _this = this;
			this.loginForm.on('submit', function(e) {
				e.preventDefault();
				_this.authenticate();
			});
			
			// Get Theme
			if(codiad.settings !== undefined) {
			
				var theme = await codiad.settings.get_option('codiad.theme');
			} else {
			
				var theme = 'default';
			}
			$("#theme option").each(function() {
				if($(this).val() == theme) {
					$(this).attr("selected", "selected");
				}
			});
			
			// Get Language
			if(codiad.settings !== undefined) {
			
				var language = await codiad.settings.get_option('codiad.language');
			} else {
			
				var language = 'en';
			}
			
			$("#language option").each(function() {
				if($(this).val() == language) {
					$(this).attr("selected", "selected");
				}
			});
			
			// More Selector
			$('.show-language-selector').click(function() {
				$(this).hide();
				$('.language-selector').animate({
					height: 'toggle'
				}, "fast");
			});
		},
		
		//////////////////////////////////////////////////////////////////
		// Authenticate User
		//////////////////////////////////////////////////////////////////
		
		authenticate: function() {
			
			$.ajax({
				type: "POST",
				url: this.controller + '?action=authenticate',
				data: this.loginForm.serialize(),
				success: function( data ) {
					
					let parsed = codiad.jsend.parse( data );
					if( parsed != 'error' ) {
						// Session set, reload
						window.location.reload();
					}
				},
				error: function( XMLHttpRequest, textStatus, errorThrown ) {
					
					console.log( XMLHttpRequest, textStatus, errorThrown );
				}
			});
		},
		
		//////////////////////////////////////////////////////////////////
		// Logout
		//////////////////////////////////////////////////////////////////
		
		logout: function() {
			var forcelogout = true;
			if($('#list-active-files li.changed').length > 0) {
				forcelogout = confirm(i18n('You have unsaved files.'));
			}
			if(forcelogout) {
				$('#list-active-files li.changed').each(function() {
					$(this).removeClass('changed')
				});
				amplify.publish('user.logout', {});
				codiad.settings.save();
				$.get(this.controller + '?action=logout', function() {
					window.location.reload();
				});
			}
		},
		
		//////////////////////////////////////////////////////////////////
		// Open the user manager dialog
		//////////////////////////////////////////////////////////////////
		
		list: function() {
			$('#modal-content form')
				.die('submit'); // Prevent form bubbling
			codiad.modal.load(600, this.dialog + '?action=list');
		},
		
		//////////////////////////////////////////////////////////////////
		// Create User
		//////////////////////////////////////////////////////////////////
		
		createNew: function() {
			var _this = this;
			codiad.modal.load(400, this.dialog + '?action=create');
			$('#modal-content form')
			.live('submit', function(e) {
				e.preventDefault();
				var pass = true;
				var username = $('#modal-content form input[name="username"]')
					.val();
				var password1 = $('#modal-content form input[name="password1"]')
					.val();
				var password2 = $('#modal-content form input[name="password2"]')
					.val();
				
				// Check matching passwords
				if(password1 != password2) {
					
					codiad.message.error(i18n('Passwords Do Not Match'));
					pass = false;
				}
				
				if( pass ) {
					
					$.post( _this.controller + '?action=create', {
						'username': username,
						'password': password1,
						'password2': password2,
					}, function(data) {
						var createResponse = codiad.jsend.parse( data );
						if( createResponse != 'error' ) {
							
							codiad.message.success( i18n( 'User Account Created' ) )
							_this.list();
						} else {
							
							console.log( createResponse, data );
						}
					});
				}
			});
		},
		
		//////////////////////////////////////////////////////////////////
		// Delete User
		//////////////////////////////////////////////////////////////////
		
		delete: function(username) {
			var _this = this;
			codiad.modal.load(400, this.dialog + '?action=delete&username=' + username);
			$('#modal-content form')
				.live('submit', function(e) {
					e.preventDefault();
					var username = $('#modal-content form input[name="username"]')
						.val();
					codiad.modal.show_loading();
					$.get(_this.controller + '?action=delete&username=' + username, function(data) {
						var deleteResponse = codiad.jsend.parse(data);
						if(deleteResponse != 'error') {
							codiad.message.success(i18n('Account Deleted'))
							_this.list();
						} else {
							
							console.log( deleteResponse, data );
						}
					});
				});
		},
		
		//////////////////////////////////////////////////////////////////
		// Set Project Access
		//////////////////////////////////////////////////////////////////
		
		projects: function(username) {
			codiad.modal.load(400, this.dialog + '?action=projects&username=' + username);
			var _this = this;
			$('#modal-content form')
				.live('submit', function(e) {
					e.preventDefault();
					var username = $('#modal-content form input[name="username"]')
						.val();
					var accessLevel = $('#modal-content form select[name="access_level"]')
						.val();
					var projects = new Array();
					$('input:checkbox[name="project"]:checked').each(function() {
						projects.push($(this).val());
					});
					if(accessLevel == 0) {
						projects = 0;
					}
					// Check and make sure if access level not full that at least on project is selected
					if(accessLevel == 1 && !projects) {
						codiad.message.error(i18n('At Least One Project Must Be Selected'));
					} else {
						$.post(_this.controller + '?action=project_access&username=' + username, {
							projects: projects
						}, function(data) {
							var projectsResponse = codiad.jsend.parse(data);
							if(projectsResponse != 'error') {
								codiad.message.success(i18n('Account Modified'));
							}
						});
					}
				});
		},
		
		//////////////////////////////////////////////////////////////////
		// Change Password
		//////////////////////////////////////////////////////////////////
		
		password: function(username) {
			var _this = this;
			codiad.modal.load(400, this.dialog + '?action=password&username=' + username);
			$('#modal-content form')
				.live('submit', function(e) {
					e.preventDefault();
					var username = $('#modal-content form input[name="username"]')
						.val();
					var password1 = $('#modal-content form input[name="password1"]')
						.val();
					var password2 = $('#modal-content form input[name="password2"]')
						.val();
					if(password1 != password2) {
						codiad.message.error(i18n('Passwords Do Not Match'));
					} else {
						$.post(_this.controller + '?action=password', {
							'username': username,
							'password': password1
						}, function(data) {
							var passwordResponse = codiad.jsend.parse(data);
							if(passwordResponse != 'error') {
								codiad.message.success(i18n('Password Changed'));
								codiad.modal.unload();
							} else {
								
								console.log( passwordResponse, data );
							}
						});
					}
				});
		},
		
		//////////////////////////////////////////////////////////////////
		// Change Current Project
		//////////////////////////////////////////////////////////////////
		
		project: function(project) {
			$.get(this.controller + '?action=project&project=' + project);
		},
		
		update_access: function( e, username ) {
			
			let access = "";
			
			if( ( typeof e ) == "string" ) {
				
				access = e;
			} else {
				
				access = $( e.target ).val();
			}
			
			$.post( this.controller + `?action=update_access`, {
				user: username,
				access: access,
			}, function( data ) {
				
				let response = codiad.jsend.parse( data );
				if( response != 'error' ) {
					
					codiad.message.success( i18n( 'Access Updated' ) );
				}
			});
		},
	};
})(this, jQuery);

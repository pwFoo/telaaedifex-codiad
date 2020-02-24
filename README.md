Codiad

This is the Telaaedifex team's custom version of Codiad.  Codiad is a web-based IDE framework with a small footprint and minimal requirements.

Codiad was built with simplicity in mind, allowing for fast, interactive development without the massive overhead of some of the larger desktop editors. That being said even users of IDE's such as Eclipse, NetBeans and Aptana are finding Codiad's simplicity to be a huge benefit. While simplicity was key, we didn't skimp on features and have a team of dedicated developers actively adding more.
For more information on the project please check out the check out the Wiki.
Distributed under the MIT-Style License. See LICENSE.txt file for more information.

Repositories:

[GitLab](https://gitlab.com/xevidos/codiad)

[GitHub](https://github.com/xevidos/codiad)

Issues:

[GitLab](https://gitlab.com/xevidos/codiad/issues)

[GitHub](https://github.com/xevidos/codiad/issues)

Features:

* 100+ Native programming languages supported.
* Auto Complete ( Trigger by CTRL + Space or Turn on Live Autocomplete ).
* Auto Save.
* Built in updater.
* Collaborative Editing ( [Via plugin](https://gitlab.com/xevidos/codiad-collaborative) ).
* Multi Cursor.
* Overscroll ( Ability to center bottom of code ).
* PHP 7.2 Compatibility.
* Split editor mode.
* Themes.

When you see a - in front of a task that means it is a possibility but we aren't sure we will add it yet.

Current Tasks:

Task List:
  
* Add ability to create shortlinks with certain permissions for users to share.
* Add ability to login with LDAP
* Add archive management abilities
* Add bookmark files
* Add custom market
* \- Add in new admin interface ( Check admin-portal branch for progress )
	- Group Management
	- Permissions Management
	- Plugin Management
	- Project Management
	- System Settings
	- User Management
* Add different code linters
* Add Drag and Drop natively to filemanager
* Add folder / filestructure upload ability
* Add if file could not be saved 5 times close the open file
* Add multi level users. ( Projects for only certain groups, Permission levels )
* Add mobile compatibility
* Add move files
* Add permissions module ( more in depth permissions such as read/write, delete, etc )
* Add print code
* Add support for more archive types ( Add commands add more accepted PHP extension types )
* Add support for more database systems ( MSSQL, Oracle, SQLite, Filesystem storage, etc )
* Add terminal support ( optional per permission level )
* Add in auto save timer that saves after the user stops typing instead of after every change
* Clean up update script
* Fix broken themes
* Re Add the custom language recognition system after recode
* Remove all old and unneeded dependencies
* Seperate Upload filemanager instance from main filemanager instance
* Update all current components to use more current standards ( async await and .then in favor over callbacks )


Completed:

* Add Auto Save
* Add ability to center bottom of code
* Add updating script
* Add site renaming
* Database Update
	- Added ( MySQL, PostgreSQL ) Support.
	- Project Updated to use PDO so future support for more database systems can be added.
	- Updated to store program data ( Not project data ) in databases.
* Fix JS errors already showing
* Remove Codiad autocomplete in favor of Ace's
* Updated Cursor tracking
* Updated for PHP 7.2


Known Bugs:

* Auto save does not save the most recent changes every once in a while requiring more information to be typed ( E.G. A couple spaces ) in order to show up in saved file
* Cursor is set to the wrong position if in split view
* In certain enviroments the update script pulls the old version of the sql class causing the update to fail
* The Server has new version of file alert causes auto save to stop when the user presses okay
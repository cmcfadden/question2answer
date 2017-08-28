<?php
/*
 * Shibboleth Auth Configuration
 */

// User's role mapping
// Roles : QA_USER_LEVEL_BASIC, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER
$SHIB_USERS_ROLE=array(
	'mcfa0086@umn.edu' => QA_USER_LEVEL_SUPER,
	'qauser2' => QA_USER_LEVEL_EDITOR,
);

// Shibboleth user default role
define('SHIB_DEFAULT_USER_ROLE',QA_USER_LEVEL_BASIC);

/*
 * ACLs based on Shibboleth attributes values
 *
 * Format :
 *
 * $SHIB_ACLS=array(
 * 	'[attr1]' => array (
 *		'[regex1]',
 *		'[regex2]',
 *		[...]
 *	),
 *	'[attr2]' => array (
 *		[...]
 *	),
 *	[...]
 * );
 *
 * Access is granted if :
 *  - $SHIB_ACLS is not defined
 *  - $SHIB_ACLS in not an array
 *  - if at least one regex match with at least one value of corresponding attribute
 */
/*
$SHIB_ACLS=array(
	'eduPersonPrimaryAffiliation' => array (
		'/^staff$/',
		'/^faculty$/',
	)
);
*/

// Message to display on access denied
define('SHIB_ACCESS_DENIED_MESSAGE','You are not authorized to access to this application.');

/*
 * Shibboleth attributes mapping
 */

// Shibboleth login attribute
define('SHIB_USERID_ATTR','eppn');

// Shibboleth login alternative attribute
define('SHIB_ALTERNATE_USERID_ATTR','mail');

// Shibboleth mail attribute
define('SHIB_MAIL_ATTR','mail');

// Shibboleth mail alternative attribute
define('SHIB_ALTERNATE_MAIL_ATTR','supannMailPerso');

// Shibboleth public name attribute
define('SHIB_PUBLIC_NAME_ATTR','displayName');

// Shibboleth public name alternative attribute
define('SHIB_ALTERNATE_PUBLIC_NAME_ATTR','cn');

/*
 * Shibboleth Logout URL
 */
define('SHIB_LOGOUT_URL','/Shibboleth.sso/Logout');

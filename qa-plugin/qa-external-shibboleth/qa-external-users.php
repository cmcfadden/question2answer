<?php

/*
	=========================================================================
	THIS FILE ALLOWS YOU TO INTEGRATE WITH AN EXISTING USER MANAGEMENT SYSTEM
	=========================================================================

	It is used if QA_EXTERNAL_USERS is set to true in qa-config.php.
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	// Loading configuration
	require_once 'config.php';

/*
	 Return shibboleth attribute value
	 
	 Parameters :
		$attr = The shibboleth attribute to read
		$alt_attr = (optionnal) An alternative shibboleth attribute
			to read if $attr is not defined
	 
	 Return : Shibboleth attribute value or null if attribute not defined, empty or null
*/

	function get_shib_attr($attr,$alt_attr=false,$default=null) {
		if (isset($_SERVER[$attr]) && $_SERVER[$attr]) return $_SERVER[$attr];

		if ($alt_attr && isset($_SERVER[$alt_attr]) && $_SERVER[$alt_attr]) return $_SERVER[$alt_attr];

		return $default;
	}

/*
	 Return user informations from Shibboleth attributes
	 
	 Return : Array of user informations in QA format
*/

	function get_shib_user_infos() {
		$user=get_shib_attr(SHIB_USERID_ATTR,SHIB_ALTERNATE_USERID_ATTR);
		if (!$user) {
			error_log("Shib external auth : fail to found userid from shib attributes : ".SHIB_USERID_ATTR."/".SHIB_ALTERNATE_USERID_ATTR);
			return null;
		}

		$infos=array(
			'userid'		=> $user,
			'publicusername'	=> get_shib_attr(SHIB_PUBLIC_NAME_ATTR,SHIB_ALTERNATE_PUBLIC_NAME_ATTR,$user),
			'email'			=> get_shib_attr(SHIB_MAIL_ATTR,SHIB_ALTERNATE_MAIL_ATTR),
		);
		global $SHIB_USERS_ROLE;
		if (isset($SHIB_USERS_ROLE[$user])) {
			$infos['level'] = $SHIB_USERS_ROLE[$user];
		}
		else {
			$infos['level'] = SHIB_DEFAULT_USER_ROLE;
		}
	
		error_log("User $user infos : ".print_r($infos,true));
		return $infos;
	}

	function check_user_access() {
		global $SHIB_ACLS;

		if (!isset($SHIB_ACLS) || !is_array($SHIB_ACLS)) return true;

		foreach($SHIB_ACLS as $attr => $regs) {
			// Get attribute value
			$attr_val=get_shib_attr($attr);

			// Check attribute value
			if(!$attr_val) continue;

			// Check regex on value
			if (!is_array($regs)) $regs=array($regs);
			foreach ($regs as $reg) {
				if (preg_match($reg,$attr_val))
					return true;
			}
		}
		error_log("User access denied by ACLs : ".print_r($_SERVER,1));
		return false;
	}

	function qa_get_mysql_user_column_type() {
		return 'VARCHAR(32)';
	}


	function qa_get_login_links($relative_url_prefix, $redirect_back_to_url)
/*
	===========================================================================
	YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
	===========================================================================

	You should return an array containing URLs for the login, register and logout pages on
	your site. These URLs will be used as appropriate within the Q2A site.
	
	You may return absolute or relative URLs for each page. If you do not want one of the links
	to show, omit it from the array, or use null or an empty string.
	
	If you use absolute URLs, then return an array with the URLs in full (see example 1 below).

	If you use relative URLs, the URLs should start with $relative_url_prefix, followed by the
	relative path from the root of the Q2A site to your login page. Like in example 2 below, if
	the Q2A site is in a subdirectory, $relative_url_prefix.'../' refers to your site root.
	
	Now, about $redirect_back_to_url. Let's say a user is viewing a page on the Q2A site, and
	clicks a link to the login URL that you returned from this function. After they log in using
	the form on your main site, they want to automatically go back to the page on the Q2A site
	where they came from. This can be done with an HTTP redirect, but how does your login page
	know where to redirect the user to? The solution is $redirect_back_to_url, which is the URL
	of the page on the Q2A site where you should send the user once they've successfully logged
	in. To implement this, you can add $redirect_back_to_url as a parameter to the login URL
	that you return from this function. Your login page can then read it in from this parameter,
	and redirect the user back to the page after they've logged in. The same applies for your
	register and logout pages. Note that the URL you are given in $redirect_back_to_url is
	relative to the root of the Q2A site, so you may need to add something.
*/
	{

		return array(
			'login' => null,
			'register' => null,
			'logout' => null,
		);

	}
	

	function qa_get_logged_in_user()
/*
	Check if user is currently logged in : If not, return null. If so, return an array 
        with the following elements:

	* userid: a user id appropriate for your response to qa_get_mysql_user_column_type()
	* publicusername: a user description you are willing to show publicly, e.g. the username
	* email: the logged in user's email address
	* level: one of the QA_USER_LEVEL_* values below to denote the user's privileges:
	
	QA_USER_LEVEL_BASIC, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER
	
	To indicate that the user is blocked you can also add an element 'blocked' with the value true.
	Blocked users are not allowed to perform any write actions such as voting or posting.
	
	The result of this function will be passed to your other function qa_get_logged_in_user_html()
	so you may add any other elements to the returned array if they will be useful to you.

	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries.
	
	In order to access the admin interface of your Q2A site, ensure that the array element 'level'
	contains QA_USER_LEVEL_ADMIN or QA_USER_LEVEL_SUPER when you are logged in.
*/
	{
		if (isset($_REQUEST['logout']) && constant('SHIB_LOGOUT_URL')) {
			header('Location: '.SHIB_LOGOUT_URL);
			exit();
		}

		if (!check_user_access()) {
			header('HTTP/1.0 403 Forbidden');
			die(SHIB_ACCESS_DENIED_MESSAGE);
		}

		return get_shib_user_infos();

	}

	
	function qa_get_user_email($userid)
/*
	Return the email address for user $userid, or null if you don't know it.
*/
	{

		$infos=get_shib_user_infos();
		if ($infos['userid']==$userid) return $infos['email'];

		return null;

	}
	

	function qa_get_userids_from_public($publicusernames)
/*
	You should take the array of public usernames in $publicusernames, and return an array which
	maps valid usernames to internal user ids. For each element of this array, the username should be
	in the key, with the corresponding user id in the value. If your usernames are case- or accent-
	insensitive, keys should contain the usernames as stored, not necessarily as in $publicusernames.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
*/
	{

		$publictouserid=array();
		
		foreach ($publicusernames as $publicusername)
			$publictouserid[$publicusername]=$publicusername;
		
		return $publictouserid;

	}


	function qa_get_public_from_userids($userids)
/*
	This is exactly like qa_get_userids_from_public(), but works in the other direction.
	
	You should take the array of user identifiers in $userids, and return an array which maps valid
	userids to public usernames. For each element of this array, the userid you were given should
	be in the key, with the corresponding username in the value.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
*/
	{

		$useridtopublic=array();
		
		foreach ($userids as $userid) {
			$useridtopublic[$userid]=$userid;
		}
		
		return $useridtopublic;

	}


	function qa_get_logged_in_user_html($logged_in_user, $relative_url_prefix)
/*
	You should return HTML code which identifies the logged in user, to be displayed next to the
	logout link on the Q2A pages. This HTML will only be shown to the logged in user themselves.

	$logged_in_user is the array that you returned from qa_get_logged_in_user(). Hopefully this
	contains enough information to generate the HTML without another database query, but if not,
	call qa_db_connection() to get the connection to the Q2A database.

	$relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
	you want to include a link that uses relative URLs. If the Q2A site is in a subdirectory of
	your site, $relative_url_prefix.'../' refers to your site root (see example 1).

	If you don't know what to display for a user, you can leave the default below. This will
	show the public username, linked to the Q2A profile page for the user.
*/
	{
	
	//	By default, show the public username linked to the Q2A profile page for the user

		$publicusername=$logged_in_user['publicusername'];
		
		return '<A HREF="'.htmlspecialchars($relative_url_prefix.'user/'.urlencode($publicusername)).
			'" CLASS="qa-user-link">'.htmlspecialchars($publicusername).'</A>';

	}


	function qa_get_users_html($userids, $should_include_link, $relative_url_prefix)
/*

	You should return an array of HTML to display for each user in $userids. For each element of
	this array, the userid should be in the key, with the corresponding HTML in the value.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries. If you
	access this database or any other, try to use a single query instead of one per user.
	
	If $should_include_link is true, the HTML may include links to user profile pages.
	If $should_include_link is false, links should not be included in the HTML.
	
	$relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
	you want to include links that uses relative URLs. If the Q2A site is in a subdirectory of
	your site, $relative_url_prefix.'../' refers to your site root (see example 1).
	
	If you don't know what to display for a user, you can leave the default below. This will
	show the public username, linked to the Q2A profile page for each user.
*/
	{

	//	By default, show the public username linked to the Q2A profile page for each user

		$useridtopublic=qa_get_public_from_userids($userids);
		
		$usershtml=array();

		foreach ($userids as $userid) {
			$publicusername=$useridtopublic[$userid];
			
			$usershtml[$userid]=htmlspecialchars($publicusername);
			
			if ($should_include_link)
				$usershtml[$userid]='<A HREF="'.htmlspecialchars($relative_url_prefix.'user/'.urlencode($userid)).
					'" CLASS="qa-user-link">'.$usershtml[$userid].'</A>';
		}
			
		return $usershtml;

	}


	function qa_avatar_html_from_userid($userid, $size, $padding)
/*
	
	You should return some HTML for displaying the avatar of $userid on the page.
	If you do not wish to show an avatar for this user, return null.
	
	$size contains the maximum width and height of the avatar to be displayed, in pixels.

	If $padding is true, the HTML you return should render to a square of $size x $size pixels,
	even if the avatar is not square. This can be achieved using CSS padding - see function
	qa_get_avatar_blob_html(...) in qa-app-format.php for an example. If $padding is false,
	the HTML can render to anything which would fit inside a square of $size x $size pixels.
	
	Note that this function may be called many times to render an individual page, so it is not
	a good idea to perform a database query each time it is called. Instead, you can use the fact
	that before qa_avatar_html_from_userid(...) is called, qa_get_users_html(...) will have been
	called with all the relevant users in the array $userids. So you can pull out the information
	you need in qa_get_users_html(...) and cache it in a global variable, for use in this function.
*/
	{
		return null; // show no avatars by default

	}
	
	
	function qa_user_report_action($userid, $action)
/*
	Informs you about an action by user $userid that modified the database, such as posting,
	voting, etc... If you wish, you may use this to log user activity or monitor for abuse.
	
	Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
	Q2A, you can use this with PHP's MySQL functions such as mysql_query() to run queries.
	
	$action will be a string (such as 'q_edit') describing the action. These strings will match the
	first $event parameter passed to the process_event(...) function in event modules. In fact, you might
	be better off just using a plugin with an event module instead, since you'll get more information.
	
	FYI, you can get the IP address of the user from qa_remote_ip_address().
*/
	{
		// do nothing by default
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/

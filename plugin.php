<?php

class BRHPasswordProtection extends KokenPlugin {
	
	private $separator = '|';
	private $separator_eol = ',';
	private $cookie_name = "brh-koken-protected";
	private $auth_url;
	private $url;
	private $email = '';
	
	function __construct()
	{
		$this->register_filter('site.output', 'logins');		
		$this->url = self::get_current_url();
	}
		
	function logins( $html )
	{
		// Skip admin pages
		if( strpos($_SERVER["PHP_SELF"], 'preview.php') )
		{
			return $html;
		}
		
		// Make sure we have some login values set from the plugin settings
		if( isset($this->data->logins) )
		{
			self::build_auth($this->data->logins);
			
			if( isset($this->data->email) )
			{
				$this->email = $this->data->email;
			}
			
			// Check if page is password protected
			if( self::is_protected_page() )
			{
				// Check credentials & cookies against URL
				if( self::is_valid_cookie() )
				{
					return $html;
				}
				else
				{
					// Here on a $_POST ?
					if( isset($_POST['password']) )
					{
						if( $this->auth_url[$this->url] == $_POST['password'] )
						{
							// Login successful, set cookie for two weeks
							setcookie($this->cookie_name, $_POST['password'], time()+1209600);
							return $html;
						}
						else
						{
							// Invalid, show login form again
							return self::display_login_form($html, true);
						}
					}
				}
				// Show login form
				return self::display_login_form($html);
			}
		}
		// Made it all the way here, show the full page
		return $html;
	}
		
	function display_login_form($html, $failed=false)
	{
		
		include('simple_html_dom.php');
		$_html = str_get_html($html);
		
		// Check for PJAX call
		if( $_SERVER['HTTP_X_PJAX'] )
		{
			$_html->find('#main', 0)->innertext = self::login_form($failed);
		}
		// Write over the whole body
		else
		{
			$_html->find('body', 0)->innertext = self::login_form($failed);		
		}
		
		return $_html;
	}
	
	function is_protected_page()
	{
		// More flexibility on matching for the URL here?
		return (array_key_exists('*', $this->auth_url) || array_key_exists($this->url, $this->auth_url));
	}
	
	function is_valid_cookie()
	{	
		// Make sure cookie exists and contains the contents allow access to the current URL
		// Cookie should be set to a password, make sure that password is allowed for the current URL
		// TODO: make this work with multiple URL
		return (isset($_COOKIE[$this->cookie_name]) && 
				in_array($_COOKIE[$this->cookie_name], $this->auth_url) &&
				$this->auth_url[$this->url] == $_COOKIE[$this->cookie_name]);
	}
		
	function login_form($failed=false)
	{
		$output = '<div id="" class="container" style="padding-top:60px; margin: 0 20px;">';
		
		if( $failed )
		{
			$output .=	'<h2>Password Incorrect, Login Required</h2>';
		}
		else
		{
			$output .=	'<h2>Login Required</h2>';
		}

		$output .=
				'<form method="POST" action="'.$_SERVER["REQUEST_URI"].'">'.
				'<input type="text" name="password" placeholder="Password" />'.
				'<input type="Submit" value="Login" />'.
				'</form>';

		if( !empty($this->email) )
		{
			$output .= '<br/><p>Please email '.$this->email.' for access.</p>';
		}

		$output .= '</div>';
		
		return $output;
	}
	
	// Build array of login data with url access
	// User => Pass
	function build_auth( $data )
	{
		$ret = array();
		
		$data = explode($this->separator_eol, $data);
		foreach( $data as $d )
		{
			$entry = explode($this->separator, $d);

			list($url, $pass) = $entry;
			
			// Force leading slash for URL matching
			if( $url[0] != '/' )
			{
				$url = '/'.$url;
			}
			
			$ret[$url] = $pass; 
		}
		
		$this->auth_url = $ret;
	} 
	
	// Lifted from app/site/site.php
	function get_current_url()
	{
		// If this isn't set, they have enabled URL rewriting for purty links and arrived here directly
		// (not through /index.php/this/that)
		if (!isset($rewrite))
		{
			$rewrite = true;
			$raw_url = $_GET['url'];
		}
		else
		{
			if (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '/') === 0)
			{
				$raw_url = $_SERVER['QUERY_STRING'];
			}
			else if (isset($_SERVER['PATH_INFO']))
			{
				$raw_url = $_SERVER['PATH_INFO'];
			}
			else if (isset($_SERVER['REQUEST_URI']))
			{
				$raw_url = $_SERVER['REQUEST_URI'];
			}
			else if (isset($_SERVER['ORIG_PATH_INFO']))
			{
				$raw_url = $_SERVER['ORIG_PATH_INFO'];
			}
			else
			{
				$raw_url = '/';
			}
		}
		return $raw_url;
	}
}

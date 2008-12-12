<?php

require_once 'MathSocAction.inc';
//require_once 'Zend/Auth.php';
require_once 'KiwiId.php';

class UserController extends MathSoc_Controller_Action
{
	private $loginUrl = "https://strobe.uwaterloo.ca/cpadev/kiwi/user/login/";

	public function init()
	{	parent::init();
		$referer = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
			
		if( $this->_request->getParam('referer') )
		{	$referer = $this->_request->getParam('referer');
		}

		// If user not authenticated and query not set
		if( !$this->_request->isGet() && !$this->_request->getQuery('__kiwi_id__') )
		{	// Forward to kiwi login URL
			$this->_redirect( $loginUrl . "?__kiwi_referer__={$referer}" );
		}
	}

    public function indexAction()
	{	// Display user information

		// Access to email, clubs, sections for positions held
    }

	public function loginAction()
	{	$status = "";
		$auth = Zend_Auth::getInstance();

		// If authentication information is set
		if( $this->_request->getQuery('__kiwi_id__') )
		{	// Attempt to use it to authenticate
			$result = $auth->authenticate(
				new Zend_Auth_Adapter_KiwiId($this->_request->getQuery('__kiwi_id__')) );

			if( $result->isValid() )
			{	// If the authentication is valid
				$status = "You are logged-in as " . $auth->getIdentity() . "<br>\n";
				// Do database lookup for groups the user returned by kiwi check is a part of
				$this->_redirect('/user');
			}else
			{	// If authentication is invalid, remove user
				$auth->clearIdentity();
				foreach( $result->getMessages() as $message )
				{	$status .= "$message<br>\n";
				}
		    }
		}elseif( $auth->hasIdentity() )
		{	$status = "You are logged-in as " . $auth->getIdentity() . "<br>\n";
		}else
		{	if( $this->_getParam( "referer" ) )
			{	$this->_redirect($this->loginUrl . "?__kiwi_referer__=" . $this->_getParam("referer"));
			}else
			{	$this->_redirect($this->loginUrl . "?__kiwi_referer__=http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
			}
		}
	}

	public function logoutAction()
	{	// Remove the authentication session information
		Zend_Auth::getInstance()->clearIdentity();
		// TODO: forward to kiwi logout url
		$this->_redirect('/');
	}

	public function profileAction()
	{
		require_once( "userDB.inc" );
		$db = new UserDB();
		$user = $db->getProfile( $this->_getParam('username') );

		$this->view->user = $user;
	}
}

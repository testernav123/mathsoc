<?php

require_once 'MathSocAction.inc';
require_once 'noveltiesDB.inc';

class NoveltiesController extends MathSoc_Controller_Action
{
	private $db;

	public function init()
	{	parent::init();

		$this->db = new NoveltiesDB();
	}

	/** /novelties - Give details about novelties.
	 */
	public function indexAction()
	{
	}

	/** /novelties/contest - Give active contest details, currently hardcoded
	 */
	public function contestAction()
	{
	}

	/** /novelties/display - Display an existing novelty from the database
	 */
	public function displayAction()
	{
		if( $this->_getParam( 'id' ) )
		{	// Grab the novelty from the database
			$novelty = $this->db->getNovelty( $this->_getParam( 'id' ) );

			if( $this->_getParam( 'image' ) )
			{	$images = $novelty['images'];
				foreach( $images as $image )
				{	if( $image['name'] == $this->_getParam( 'image' ) )
					{	Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
						header("Content-type: {$image['type']}");
						print( $image['image'] );
					}
				}
			}

			$this->view->novelty = $novelty;
		}

		$this->view->novelties = $this->db->getNovelties();

	}

	/** /novelties/submit - Used to submit new novelties to the system.
	 */
	public function submitAction()
	{	
		$this->secure();
		require_once( "../application/default/views/helpers/form.inc" );
		$this->view->email = Zend_Auth::getInstance()->getIdentity() . '@uwaterloo.ca';

		$_POST['notes'] = 'MathSoc T-Shirt Design Contest (Winter 2009)';

		$novelty = array(
				'submitter'	=> Zend_Auth::getInstance()->getIdentity(),
				'name'		=> $_POST['name'],
				'description' => $_POST['description'],
				'notes'		=> $_POST['notes'],
				'images'	=> array(),
			);

		$this->view->assign( $novelty );
		$this->view->email = Zend_Auth::getInstance()->getIdentity() . '@uwaterloo.ca';

		if( !$_POST['name'] )
			$message .= "\nYou must name your creation.\n";
		if( !$_POST['description'] )
			$message .= "\nPlease add a short description of your novelty\n";

		if( isset( $_POST['submit_tshirt'] ) )
		{	$novelty['style'] = 'T-Shirt';
			
			if( $_FILES['tshirt_front']['error'] == UPLOAD_ERR_OK )
			{	$front = array(
					'name'	=> 'Front',
					'type'	=> $_FILES['tshirt_front']['type'],
					'image'	=> file_get_contents( $_FILES['tshirt_front']['tmp_name'] )
				);
				array_push( $novelty['images'], $front );
			}else
			{	$this->view->message = "There was a problem uploading your front image.  This image is required, please try again.\n";
			}

			if( $_FILES['tshirt_back']['error'] == UPLOAD_ERR_OK )
			{	$back = array(
					'name'	=> 'Back',
					'type'	=> $_FILES['tshirt_back']['type'],
					'image'	=> file_get_contents( $_FILES['tshirt_back']['tmp_name'] )
				);
				array_push( $novelty['images'], $back );
			}else
			{	$this->view->message = "There was a problem uploading your back image.  This image is required, please try again.\n";
			}

			if( !isset( $message ) )
			{	$this->db->submitNovelty( $novelty );
			}else
			{	$this->view->message = $message;
				$this->view->assign( $_POST );
			}
		}
	}
}


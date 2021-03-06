<?php

require_once 'MathSocAction.inc';

require_once 'officeDB.inc';

class Admin_OfficeController extends MathSoc_Controller_Action
{
	private $db;

	public function init()
	{	parent::init();

		$this->db = new OfficeDB();
	}

	/** /admin/office
	 *
	 */
	public function indexAction()
	{
	}

	/** /admin/office/workers - Retrieve a list of the email address of the office workers for the term
	 * TODO: in the long run this should be a mail list
	 */
	public function workersAction()
	{	if( $this->_getParam("hour") )
		{	// If this user has requested a specific hour, return the holders
			// TODO
		}else
		{	$workers = $this->db->getEmails();
			$this->view->workers = $workers;
		}
	}

	/** /admin/office/missing
	 */
	public function missingAction()
	{	$missing = $this->db->missingHours();
		$this->view->missing = $missing;
	}
}

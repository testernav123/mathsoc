<?php

require_once 'Zend/Controller/Action.php';

// Load the database model for the exam storage
require_once '../application/exambank/models/examDB.inc';

class Exambank_IndexController extends Zend_Controller_Action
{
	private $db;

	public function init()
	{	parent::init();

		// User must be authenticated to see any of these pages
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		//$this->view->user = Zend_Auth::getInstance()->getIdentity();

		$this->db = new ExamDB();
	}
/*
    function preDispatch()
	{
		$auth = Zend_Auth::getInstance();

		if (!$auth->hasIdentity())
		{	$this->_redirect('auth/login');
		}
	}
*/
	// Browsing Functions
	public function indexAction()
	{	// List the existing exams

		// Add the javascripts to layout for DHTML interface
		$this->view->javascripts = array(
			$this->getRequest()->getBaseUrl() . '/js/prototype.js',
			$this->getRequest()->getBaseUrl() . '/js/course_selector.js');
	}

	// Javascript Callback Functions
	public function coursesAction()
	{	// Used by ajax to list the courses with exams
		if( $this->_getParam('number') )
		{	// Display course title information
			$this->view->values = $this->db->getCourses($this->_getParam('prefix'), $this->_getParam('number'));
		}elseif( $this->_getParam('prefix') )
		{	// Display course numbers for prefix
			$this->view->values = $this->db->getCourses($this->_getParam('prefix'));
		}else
		{	// Display all the prefixes available
			$this->view->values = $this->db->getCourses();
		}
	}

	/** /exambank/exams/:prefix/:number or /exambank/exams/:prefix/:number/:term/:id/:type/
	 *
	 */
	public function examsAction()
	{	// Used to retrieve exams search results in iframe
		if( $this->_getParam('type') )
		{	// Disable view renderer so output can be created manually
			Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

			// Lookup exam in system
			$exam = $this->db->getExam( $this->_getParam('id') );

			// Ensure the type of exam we're looking for exists
			if( !$filename = $exam[$this->_getParam('type') . "_path"] )
			{	print( "The exam you're looking for doesn't exist" );
				exit;
			}
			$filename = Zend_Registry::getInstance()->get('config')->examDir . $filename;

			// Display the exam to the user
			if( $buffer = file_get_contents( $filename ) )
			{
				header("Content-type: {$exam[$this->_getParam('type') . '_type']}");

				// Grab the file extension from the mime type
				$ext = split('/',$exam[$this->_getParam('type') . '_type']);
				$ext = $ext[1];

				header("Content-Length: ".strlen($buffer));
				header("Content-Disposition: inline; filename={$exam['course']}-{$exam['term']}-{$exam['type']}{$exam['number']}_{$this->_getParam('type')}.{$ext}");

				print( $buffer );
				exit;
			}
		}elseif( $this->_getParam('prefix') && $this->_getParam('number') )
		{	// Load the "exams" array to the view
			// Must set exam.id, exam.course, exam.term, exam.file_path, exam.sol_path
			$this->view->exams = $this->db->getExams( $this->_getParam('prefix'), $this->_getParam('number') );
		}
	}

	public function submitAction()
	{
		require_once( "../application/default/views/helpers/form.inc" );
		require_once( "smarty/SmartyValidate.class.php" );
		require_once( "mathsoc.inc" );

		// Generate HTML elements for the form
		$this->view->prefix_field = makeSelectElement( "prefix_element", "course_prefix",
								$this->db->getCourses(null, null, false), $this->_getParam("course_prefix") );

		// Include form validation
		$smarty = $this->view->getEngine();
		if(empty($_SESSION) || !isset($_SESSION['SmartyValidate'])) {
			SmartyValidate::connect($smarty, true);
			SmartyValidate::register_validator('prefix_element','FullName','notEmpty');
		} else {
			SmartyValidate::connect($smarty);
			// validate after a POST
			if(SmartyValidate::is_valid($_POST)) {
				// no errors, done with SmartyValidate
				SmartyValidate::disconnect();

				// TODO: insert entry to database

				// TODO: set success message
			}
			$smarty->assign($_POST);
		}
	}
}

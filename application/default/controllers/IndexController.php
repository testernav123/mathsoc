<?php

require_once 'MathSocAction.inc';
require_once 'announceDB.inc';

class IndexController extends MathSoc_Controller_Action
{
  private $db;
  
  public function init()
  {
    parent::init();
    $this->db = new AnnounceDB();
  }

  public function indexAction()
  {
    $posts = $this->db->getAnnouncements( mktime( 0, 0, 0, 9, 1, 2009 ) );
    $this->view->posts = $posts;
  }

}


<?php

// Load database module

/* This is a system to be coupled with a uwdir authentication scheme

*/

class UserDB
{
	private $_db;

	function __construct()
	{	$this->_db = Zend_Db::factory(Zend_Registry::getInstance()->get('config')->db);
	}

	function getPosition( $alias )
	{	$query = "SELECT positions.alias, positions.name, positions.description, positions.desired AS number, positions.email, positions.default_email 
				  FROM positions
				  WHERE positions.unitId = 1
					AND positions.alias = ?";

		$position = $this->_db->fetchRow( $query, $alias );

		if( empty( $position ) )
		{	return $position;
		}

		// This works because we restrict it to unit 1 (mathsoc)
		if( $position['email'] )
		{	$position['email'] = $position['alias'] . '@mathsoc.uwaterloo.ca';
		}else
		{	unset( $position['email'] );
		}

		$query = "SELECT holders.term,
						GROUP_CONCAT(volunteers.name SEPARATOR ',') AS name,
						GROUP_CONCAT(holders.email SEPARATOR ',') AS include_email,
						GROUP_CONCAT(user_emails.email SEPARATOR ',') AS email,
						terms.current_term
				  FROM holders, user_emails, volunteers, terms
				  WHERE holders.unitId = 1
					AND holders.position = ?
					AND holders.userId = user_emails.userId
					AND holders.userId = volunteers.userId
					AND holders.term > (terms.last_term - 30)
				  GROUP BY holders.term
				  ORDER BY holders.term DESC";
		$results = $this->_db->fetchAll($query, $alias);

		$terms = array();

		foreach( $results as $result )
		{	$current = array();
			$users = array();

			$names = split(',', $result['name']);
			$include_emails = split(',', $result['include_email']);
			$emails = split(',', $result['email']);

			for( $i = 0; $i < count( $names ); $i++ )
			{	if( $include_emails == 1 )
				{	array_push( $current, array("name" => $names[$i], "email" => $emails[$i]) );
				}else
				{	array_push( $current, array("name" => $names[$i]) );
				}
				array_push( $users, $names[$i] );
			}

			$terms[$result['term']] = $users;

			if( $result['term'] == $result['current_term'] )
			{	$position['current'] = $current;
				$position['current_count'] = count( $users );
			}
		}

		if( !isset( $position['number'] ) )
		{	$position['number'] = 1;
		}
		if( !isset( $position['current_count'] ) )
		{	$position['current_count'] = 0;
		}

		$position['holders'] = $terms;

		return $position;
	}

	function getPositions( $userId = "", $unit = "mathsoc" )
	{
		if( $userId != "" )
		{	$query = "SELECT positions.alias, positions.name, positions.description
					  FROM holders, terms, positions
					  WHERE positions.alias = holders.position
						AND holders.term = terms.current_term
						AND holders.unitId = ?
						AND holders.userid = ?";
			return $this->_db->fetchAll($query, array($unit, $userid));
		}else
		{	$query = "SELECT positions.alias, positions.name, positions.description
					  FROM positions, units
					  WHERE units.id = positions.unitId
						AND units.alias = ?
					  ORDER BY positions.name";
			return $this->_db->fetchAll($query, $unit);
		}
	}

	function getElectionPositions()
	{	$query = "SELECT positions.alias, positions.name, positions.description
				  FROM positions, units
				  WHERE units.id = positions.unitId
					AND units.alias = 'mathsoc'
					AND (positions.category = 'EXC' OR positions.category = 'REP')
					AND positions.alias != ''
				  ORDER BY positions.name";
		return $this->_db->fetchAll($query);
	}

	function getPositionsByCategory()
	{	$query = "SELECT p.category,
						p.alias,
						p.name,
						p.email,
						(SELECT GROUP_CONCAT(CONCAT_WS(':',volunteers.userId,volunteers.name) SEPARATOR ';')
						 FROM volunteers, holders, terms
						 WHERE terms.current_term = holders.term
							AND holders.userId = volunteers.userId
							AND p.alias = holders.position
						 GROUP BY holders.position) AS holders
				  FROM positions p
				  WHERE p.category != 'LIST'
				  ORDER BY p.name";
		$result = $this->_db->fetchAll($query);

		$return = array();

		foreach( $result as $row )
		{	if( !isset($return[$row['category']]) )
				$return[$row['category']] = array();

			$holders = array();
			if( $row['holders'] )
			{	$temp = explode(";", $row['holders']);
				foreach( $temp as $entry )
				{	$temp2 = explode(":", $entry);
					$holders[$temp2[0]] = $temp2[1];
				}
			}

			$row['holders'] = $holders;
			array_push( $return[$row['category']], $row );
		}

		return $return;
	}

	public function getAvailablePositions( $term = null )
	{	if( $term )
		{	$query = "SELECT positions.category,
							positions.alias,
							positions.name,
							positions.description,
	 						positions.desired,
							( SELECT COUNT(holders.userId)
							  FROM holders
							  WHERE holders.unitId = 1
								AND holders.term = ?
								AND holders.position = positions.alias
							  GROUP BY holders.position
							) AS holders,
							positions.chosen
					  FROM positions
					  GROUP BY positions.alias
					  ORDER BY positions.name";
			$result = $this->_db->fetchAll($query, $term);
		}else
		{	$query = "SELECT positions.category,
							positions.alias,
							positions.name,
	 						positions.desired,
							( SELECT COUNT(holders.userId)
							  FROM holders, terms
							  WHERE holders.unitId = 1
								AND holders.term = terms.current_term
								AND holders.position = positions.alias
							  GROUP BY holders.position
							) AS holders,
							positions.chosen
					  FROM positions
					  GROUP BY positions.alias
					  ORDER BY positions.name";
			$result = $this->_db->fetchAll($query);
		}

		$return = array();
		foreach( $result as $row )
		{	if( !isset($return[$row['category']]) )
				$return[$row['category']] = array();

			$holders = isset( $row['holders'] ) ? $row['holders'] : 0;
			$desired = isset( $row['desired'] ) ? $row['desired'] : 1;

			if( $holders < $desired )
				array_push( $return[$row['category']], $row );
		}

		return $return;
	}

	public function getUsers()
	{
		$query = "SELECT *
				  FROM volunteers_current
				  WHERE positions IS NOT NULL OR office_worker IS NOT NULL
				  ORDER BY volunteers_current.name";
		$result = $this->_db->fetchAll($query);

		$users = array();
		foreach( $result as $row )
		{
			$user = array(
				"name"	=> $row['name'],
				"positions" => array());

			if( $row['positions'] )
			{	$positions = explode( ";", $row['positions'] );
				foreach( $positions as $position )
				{	$position = explode( ":", $position );
					$user['positions'][$position[0]] = $position[1];
				}
			}

			if( $row['office_worker'] )
				$user['positions']['office worker'] = "Office Worker";
			$users[$row['userId']] = $user;
		}

		return $users;
	}

	// authenticate uses uwdir and mathsocs database to authenticate users
	//$_SESSION['authenticated'] - a BOOLEAN
	//$_SESSION['username'] - the username of the authenticated user
	//$_SESSION['groups'] - an array of groups for which the user is a member.
	function getGroups( $userId )
	{
		$query = "SELECT DISTINCT CONCAT(units.alias,'-',holders.position,'-current')
			  FROM holders, terms, units
			  WHERE holders.term = terms.current_term
			    AND holders.unitId = units.id
			    AND holders.userid = ?;";

		$groups = $this->_db->fetchAll($query, $userId);
		return $groups;
	}

	/**
	 * Determine if the given user is a current member of the current_groups or was ever a member of the past_groups
	 */
	function isGroup( $userId, $groups )
	{
		$query = '(SELECT units1.alias AS unit,
						holders1.position,
						positions1.category,
						(holders1.term = terms1.current_term) AS current
				  FROM units units1, holders holders1, positions positions1, terms terms1
				  WHERE holders1.userId = ?
					AND holders1.unitId = units1.id
					AND holders1.position = positions1.alias)
				UNION DISTINCT
				  (SELECT units2.alias AS unit,
						positions2.alias AS position,
						positions2.category,
						(holders2.term = terms2.current_term) AS current
				  FROM positions_rules r, holders holders2, positions positions2, units units2, terms terms2
				  WHERE holders2.userId = ?
					AND holders2.unitId = r.ruler_unit
					AND holders2.position = r.ruler_position
					AND positions2.alias = r.ruled_position
					AND positions2.unitId = r.ruled_unit
					AND units2.id = r.ruled_unit)';
		$result = $this->_db->fetchAll( $query, array($userId, $userId) );

		// If the group is unset, give everyone who has ever held any position access
		if( !$groups )
		{	return !empty( $result );
		}

		// Otherwise, process the units
		foreach( $groups as $unit => $positions )
		{
			if( !$positions )
			{	foreach( $result AS $row )
				{	if( strtolower($row['unit']) == $unit )
						return true;
				}
			}

			foreach( $positions as $position => $terms )
			{
				if( !$terms )
				{	foreach( $result AS $row )
					{	if( strtolower($row['unit']) == $unit && ($row['position'] == $position || $row['category'] == $position))
							return true;
					}
				}elseif( $terms == 'current' )
				{	foreach( $result AS $row )
					{	if( strtolower($row['unit']) == $unit && ($row['position'] == $position || $row['category'] == $position) && $row['current'])
							return true;
					}
				}else
				{	// Code should not get here
				}
			}
		}

		return false;
	}

	function isMember( $userId )
	{
		$query = "SELECT *
			  FROM refunds_users
			  WHERE userId = ?";

		$result = $this->_db->fetchCol($query, $userId);

		if( empty( $result ) )
		{	return true;
		}
		return false;
	}

	function getRefunds( $status = 'RECEIVED', $term = null )
	{
		if( isset( $term ) )
		{	$query = "SELECT userId
					  FROM refunds
					  WHERE term = ?
						AND status = ?";
			return $this->_db->fetchCol( $query, array($term, $status) );
		}

		$query = "SELECT userId
				  FROM refunds, terms
				  WHERE term = terms.current_term
					AND status = ?";
		return $this->_db->fetchCol( $query, $status );
	}

	function getTrend()
	{	$query = "SELECT *
				  FROM refunds_trends";
		return $this->_db->fetchAll($query);
	}

	function allowRefund($userId)
	{	// Set term to the last term
		$term = (date('Y') - 1900) * 10 + floor((date('m') - 1) / 4) * 4 + 1;
		if( $term % 10 == 1 )
		{	$term -= 2;
		}else
		{	$term -= 4;
		}

		// Update the entry in table to 'REGULAR'
		if( $this->_db->update( 'refunds',
				array( 'status' => 'REGULAR' ),
				array( "userId = '$userId'", "term = '$term'", "status = 'RECEIVED'" ) ) == 1 )
		{	return true;
		}else
		{	return false;
		}
	}

	function rejectRefund($userId, $reason)
	{	// Set the term 
		$term = (date('Y') - 1900) * 10 + floor((date('m') - 1) / 4) * 4 + 1;

		// Update the entry for this terms user to 'REJECTED'
		if( $this->_db->update( 'refunds',
				array( 'status' => 'REJECTED', 'reason' => $reason ),
				array( "status = 'REQUESTED'", "userId = '$userId'" ) ) )
		{	return true;
		}else
		{	return false;
		}
	}

	function getProfile( $userId )
	{
		$user = array();

		$query = "SELECT *
				  FROM volunteers_current v
				  WHERE v.userId = ?";
		$row = $this->_db->fetchRow( $query, $userId );

		$user['name'] = $row['name'];
		$user['current'] = array();

		if( $row['positions'] )
		{	$positions = explode( ";", $row['positions'] );
			foreach( $positions as $position )
			{	$position = explode( ":", $position );
				$user['current'][$position[0]] = $position[1];
			}
		}

		if( $row['office_worker'] )
			$user['current']['office worker'] = "Office Worker";

		$query = "SELECT holders.term,
						GROUP_CONCAT(positions.alias SEPARATOR ';') AS alias,
						GROUP_CONCAT(positions.name SEPARAtor ';') AS name
				  FROM holders, positions, terms
				  WHERE holders.unitId = 1
					AND holders.position = positions.alias
					AND holders.userId = ?
					AND holders.term > (terms.last_term - 30)
				  GROUP BY holders.term
				  ORDER BY holders.term DESC";
		$results = $this->_db->fetchAll($query, $userId);

		$terms = array();

		foreach( $results as $row )
		{	$positions = array();

			$aliases = explode(';', $row['alias']);
			$names = explode(';', $row['name']);

			for( $i = 0; $i < count( $names ); $i++ )
			{	$positions[$aliases[$i]] = $names[$i];
			}

			$terms[$row['term']] = $positions;
		}
		$user['terms'] = $terms;

		return $user;
	}

	public function getPositionQuestions( $alias )
	{	$query = 'SELECT q.key, q.type, q.type_params, q.text
				  FROM questions q, positions_questions p
				  WHERE q.id = p.question
					AND p.position = ?';

		$questions = $this->_db->fetchAll( $query, $alias );
		return $questions;
	}

	/** Return the current position application
	 */
	public function getApplications()
	{
	}

	/** Return a given application
	 */
	public function getApplication( $id )
	{	
	}

	/** Add an application
	 */
	public function addApplication( $application )
	{	return $this->_db->insert( 'positions_applications', $application );
	}

	public static function lookup( $userId )
	{	$userDetails = array("userId" => $userId);

    		$safeusername = escapeshellarg($userId);

    		ob_start();
    		system("ldapsearch -x -h uwldap.uwaterloo.ca -b dc=uwaterloo,dc=ca uid=$safeusername | grep ^cn: | head -n 1 | awk '{print substr(\$0, 5) }'");
    		$userDetails['name'] = trim(ob_get_contents());
    		ob_end_clean();

    		ob_start();
    		system("ldapsearch -x -h uwldap.uwaterloo.ca -b dc=uwaterloo,dc=ca uid=$safeusername | grep ^mail: | head -n 1 | awk '{print substr(\$0, 7) }'");
    		$userDetails['email'] = trim(ob_get_contents());
    		ob_end_clean();

    		return $userDetails;
	}
}

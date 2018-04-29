<?php

class User {
    private $alive = true;

    protected $gender = '';
    protected $genitalOrgan = '';

    function __construct($init_parameter) {
        $this->db = $init_parameter;
    }

    private function userQuery($sel='',$joins='', $where = '', $order = '') {
    	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
    	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
    	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
    	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
    	$query = 'SELECT users.*,
    					 CONCAT(users.fname," ",users.lname) AS full_name,
    					 schools.*,
    					 CASE
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=0  THEN "Graduated"
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=12 THEN "Senior"
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=24 THEN "Junior"
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=36 THEN "Sophmore"
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=48 THEN "Freshman"
    						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
    						ELSE ""
    					 END AS student_grade
    					 '.$selStr.'
    			  FROM users
    			  LEFT JOIN schools USING (school_id)
    			  '.$joinStr.' '.$whereStr.' '.$orderStr;
    	return $query;
    }

    public function function getUserDataFromParam($param, $value) {
    	$data = array();
    	$where = ' WHERE users.'.db_escape($param).'='.db_quote($value);
    	$query = $this->userQuery($sel='',$joins='', $where, $order = '');
    	$user = $this->db->query($query);
    	if(!is_null($user)) {
    		$data = formatUserData($user);
    		//$data['notifiation_endpoints'] = getNotifiationEndpointsByUser($data['user_id']);
    	} else {
    		$data = false;
    	}
    	return $data;
    }


}












?>

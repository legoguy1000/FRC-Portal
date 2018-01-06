<?php
include('db.php');
include('functions/functions.php');


$data = array();
$projectData = array();
$row = array();
$userInfo = getUserData($rit_un);
$query = 'select * from users where rit_un = "'.$rit_un.'"';
$result = $db->query($query) or die(mysqli_error($db));
if($result->num_rows > 0)
{		
	$row = $result->fetch_assoc();
	foreach($row as $col=>$val)
	{
		if(explode('_',$col)[0]=='email' && count(explode('_',$col))>1)
		{
			$row[$col] = $val==1 ? true:false;
		}
	}
	$newUser = false;
}
else
{
	$query = 'Insert into users (rit_un,type) VALUES ("'.$rit_un.'","'.$userInfo['type'].'")';
	$result = $db->query($query) or die(mysqli_error($db));
	$newUser = true;
}

$data = array_merge($row,$userInfo);
$data['projectInfo'] = getProjectInfoByUser($rit_un);
$data['newUser'] = $newUser;
die(json_encode($data));



?>
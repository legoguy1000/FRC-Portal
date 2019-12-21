<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;
use \Firebase\JWT\JWT;
use FrcPortal\Utilities\IniConfig;

class User extends Eloquent {
  //use Traits\Admin;
  //table name
  protected $table = 'users';
  //Use Custom Primary Key
  protected $primaryKey = 'user_id'; // or null
  /**
 * The "type" of the primary key ID.
 *
 * @var string
 */
  protected $keyType = 'string';
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'user_id', 'fname', 'lname', 'email', 'full_name', 'student_grade', 'grad_year', 'admin', 'user_type'
  ];

  protected $appends = ['slack_enabled','room_type','adult','other_adult','student','mentor'];
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = ['signin_pin','lname','grad_year','email','team_email','student_grade','phone','admin','adult','first_login','gender','user_type','mentor','student','slack_id','room_type','former_student','school','school_id','slack_enabled', 'webauthn_challenge'];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'admin' => 'boolean',
    'status' => 'boolean',
    'first_login' => 'boolean',
    'admin' => 'boolean',
    'adult' => 'boolean',
    'other_adult' => 'boolean',
    'student' => 'boolean',
    'mentor' => 'boolean',
    'former_student' => 'boolean',
  ];

  public function newQuery() {
      return parent::newQuery()->select('users.*', DB::raw('CONCAT(users.fname," ",users.lname) AS full_name,
      CASE
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=0  THEN "Graduated"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=12 THEN "Senior"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=24 THEN "Junior"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=36 THEN "Sophmore"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=48 THEN "Freshman"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
       ELSE ""
      END AS student_grade'));
  }

  public function save($options = array()) {
    if(is_null($this->user_id)) {
      $this->user_id = uniqid();
    }
    return parent::save();
  }
/*  public static function boot() {
    parent::boot();``
    static::creating(function ($instance) {
      $instance->user_id = (string) uniqid();
    });
  } */



  public function setAttributeVisibility(){
    if(Utilities\Auth::isAuthenticated()) {
      $this->makeVisible(['lname','grad_year','email','team_email','student_grade','phone','admin','adult','first_login','gender','user_type','mentor','student','slack_id','room_type','former_student','school','school_id','slack_enabled']);
    }
  }

  public function toJson($options = 0) {
    $this->setAttributeVisibility();
    return parent::toJson();
  }
  public function toArray() {
    $this->setAttributeVisibility();
    return parent::toArray();
  }

  public function getFullNameAttribute($value) {
    if(Utilities\Auth::isAuthenticated()) {
      return $this->attributes['fname'].' '.$this->attributes['lname'];
    } else {
      return $this->attributes['fname'];
    }
  }



  public function getSlackEnabledAttribute() {
    return (bool) isset($this->attributes['slack_id']) && $this->attributes['slack_id'] != '';
  }
  public function getAdultAttribute() {
    return (bool) isset($this->attributes['user_type']) && ($this->attributes['user_type'] == 'Mentor' || $this->attributes['user_type'] == 'Alumni' || $this->attributes['user_type'] == 'Parent');
  }
  public function getOtherAdultAttribute() {
    return (bool) $this->adult && isset($this->attributes['user_type']) && ($this->attributes['user_type'] == 'Alumni' || $this->attributes['user_type'] == 'Parent');
  }
  public function getStudentAttribute() {
    return (bool) isset($this->attributes['user_type']) && $this->attributes['user_type'] == 'Student';
  }
  public function getMentorAttribute() {
    return (bool) $this->adult && isset($this->attributes['user_type']) && $this->attributes['user_type'] == 'Mentor';
  }
  public function getRoomTypeAttribute() {
    $return = null;
    if($this->adult) {
      $return = 'Adult';
    } else if(isset($this->attributes['user_type']) && isset($this->attributes['gender'])) {
      $return = $this->attributes['user_type'].'.'.$this->attributes['gender'];
    }
    return $return;
  }

  /**
  * Get the School.
  */
  public function school() {
    return $this->hasOne('FrcPortal\School', 'school_id', 'school_id');
  }
  /**
  * Get the Annual requirements.
  */
  public function annual_requirements() {
    return $this->hasOne('FrcPortal\AnnualRequirement', 'user_id', 'user_id')->withDefault();
  }
  /**
  * Get the Event requirements.
  */
  public function event_requirements() {
    return $this->hasOne('FrcPortal\EventRequirement', 'user_id', 'user_id')->withDefault();
  }
  /**
  * Get the Event Cars.
  */
  public function event_cars() {
    return $this->hasMany('FrcPortal\EventCar', 'user_id', 'user_id');
  }
  /**
   * Get the POC.
   */
  public function event_pocs() {
      return $this->belongsTo('FrcPortal\Event', 'poc_id', 'user_id');
  }
  /**
  * Get the Meeting Hours.
  */
  public function meeting_hours() {
    return $this->hasMany('FrcPortal\MeetingHour', 'user_id', 'user_id');
  }
  public function last_sign_in() {
    return $this->hasOne('FrcPortal\MeetingHour', 'user_id', 'user_id')->orderBy('time_in', 'DESC');
  }
  /**
  * Get the OAuth IDs
  */
  public function oauth() {
    return $this->hasMany('FrcPortal\Oauth', 'user_id', 'user_id');
  }
  /**
  * Get the WebAuthn IDs
  */
  public function web_authn_credentials() {
    return $this->hasMany('FrcPortal\UserCredential', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function notification_preferences() {
    return $this->hasMany('FrcPortal\NotificationPreference', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function notifications() {
    return $this->hasMany('FrcPortal\Notification', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function missing_hours_requests() {
    return $this->hasOne('FrcPortal\MissingHoursRequest', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function missing_hours_approvers() {
    return $this->hasOne('FrcPortal\MissingHoursRequest', 'approved_by', 'user_id');
  }
  /**
   * Get the Event Time Slots.
   */
  public function user_categories() {
    return $this->belongsToMany('FrcPortal\UserCategory', 'users_user_categories', 'user_id', 'cat_id');
  }


  public function updateUserOnLogin($userData) {
    $update = false;
  	if($this->profile_image == '') {
  		$this->profile_image = $userData['profile_image'];
  		$update = true;
  	}
  	$teamDomain = getSettingsProp('team_domain');
  	if($this->team_email == '' && !is_null($teamDomain) && strpos($userData['email'],'@'.$teamDomain) !== false) {
  		$this->team_email = $userData['email'];
  		$update = true;
  	}
  	if($update == true) {
  		$this->save();
  	}
  	return $this;
  }

  public function generateUserJWT() {
  	/* if(!$user instanceof FrcPortal\User) {
  		return false;
  	} */
  	$key = getSettingsProp('jwt_key');
  	$token = array(
  		"iss" => getSettingsProp('env_url'),
  		"iat" => time(),
  		"exp" => time()+60*60,
  		"jti" => bin2hex(random_bytes(10)),
  		'data' => array(
  			'user_id' => $this->user_id,
  			'full_name' => $this->full_name,
  			'admin' => $this->status && $this->admin,
  			'status' => $this->status,
  			'user_type' => $this->user_type,
  			'email' => $this->email,
        'localadmin' => $this->user_id == IniConfig::iniDataProperty('admin_user'),
  		)
  	);
  	$jwt = JWT::encode($token, $key);
  	return $jwt;
  }

  public function setDefaultNotifications() {
  	$data = getNotificationOptions();
  	$queryArr = array();
  	$queryStr	 = '';
  	foreach($data as $meth=>$types) {
  		foreach($types as $type=>$status) {
  			$note = new NotificationPreference();
  			$note->user_id = $this->user_id;
  			$note->method = $meth;
  			$note->type = $type;
  			$note->save();
  		}
  	}
  }

  public function getNotificationPreferences() {
    $data = getNotificationOptions();
    $method_enable = array(
      'slack' => getSettingsProp('slack_enable'),
      'email' => getSettingsProp('email_enable')
    );
  	$result = $this->notification_preferences()->get();
  	if(count($result) > 0) {
  		foreach($result as $re) {
  			$m = $re['method'];
  			$t = $re['type'];
  			$data[$m][$t] = true && $method_enable[$m];
  		}
  	}
  	return $data;
  }

  public function sendUserNotification($type, $msgData) {

  	$preferences = $this->getNotificationPreferences();
  	//$preferences = array('push' => true, 'email' => false);
    if(($type == '' || $preferences['email'][$type] == true) && isset($msgData['email'])) {
  		$msg = $msgData['email'];
  		$subject = $msg['subject'];
  		$content = $msg['content'];
  		$userData = $msg['userData'];
  		$attachments = isset($msg['attachments']) && is_array($msg['attachments']) ? $msg['attachments'] : false;
  		emailUser($userData,$subject,$content,$attachments);
  	}
  	if(($type == '' || $preferences['slack'][$type] == true) && isset($msgData['slack'])) {
  		$msg = $msgData['slack'];
  		$title = $msg['title'];
  		$body = $msg['body'];
  		$tag = '';
  		$note_id = uniqid();
  		slackMessageToUser($this->user_id, $body);
  	}
  }

  public function getGenderByFirstName() {
  	$return = false;
    $name = $this->fname;
  	if(!is_null($name) && $name != '') {
  		$base = 'https://api.genderize.io/';
  		$url = $base.'?name='.$name;
  		$contents = json_decode(file_get_contents($url));
  		if(isset($contents->gender) && !is_null($contents->gender) && $contents->gender != '' && $contents->probability > .90) {
  			$this->gender = ucfirst($contents->gender);
      	return true;
  		}
      $this->gender = '';
  	}
  	return false;
  }

  public function getGetSlackIdByEmail() {
    $return = false;
    $emails = array($this->email,$this->team_email);
    foreach($emails as $email) {
      if(!is_null($email) && $email != '') {
        $result = slackGetAPI('users.lookupByEmail', $params = array('email'=>$email));
        $data = json_decode($result);
        if(isset($data->ok) && $data->ok == true) {
          $this->slack_id = $data->user->id;
          return true;
        }
      }
    }
    return false;
  }

  public function deleteLinkedAccount($auth_id) {
    $auth = $this->oauth()->where('auth_id',$auth_id)->first();
    if($auth->delete()) {
      $message = $auth->oauth_provider_cap.' account "'.$auth->oauth_user.'" unlinked.';
      insertLogs($level = 'Information', $message);
      return true;
    }
    $message = 'Something went wrong unlinking '.$auth->oauth_provider_cap.' account "'.$auth->oauth_user.'".';
    insertLogs($level = 'Information', $message);
    return false;
  }

  public function deleteWebAuthnCredential($cred_id) {
    $cred = $this->web_authn_credentials()->where('cred_id',$cred_id)->first();
    if($cred->delete()) {
      $message = '"'.$cred->name.'" device credential deleted.';
      insertLogs($level = 'Information', $message);
      return true;
    }
    $message = 'Something went wrong deleting "'.$cred->name.'" device credential.';
    insertLogs($level = 'Information', $message);
    return false;
  }
}

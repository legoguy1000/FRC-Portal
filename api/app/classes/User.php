<?php
namespace FrcPortal;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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
    return (bool) !empty($this->attributes['slack_id']);
  }
  public function getAdultAttribute() {
    return (bool) !empty($this->attributes['user_type']) && ($this->attributes['user_type'] == 'Mentor' || $this->attributes['user_type'] == 'Alumni' || $this->attributes['user_type'] == 'Parent');
  }
  public function getOtherAdultAttribute() {
    return (bool) $this->adult && !empty($this->attributes['user_type']) && ($this->attributes['user_type'] == 'Alumni' || $this->attributes['user_type'] == 'Parent');
  }
  public function getStudentAttribute() {
    return (bool) !empty($this->attributes['user_type']) && $this->attributes['user_type'] == 'Student';
  }
  public function getMentorAttribute() {
    return (bool) $this->adult && !empty($this->attributes['user_type']) && $this->attributes['user_type'] == 'Mentor';
  }
  public function getRoomTypeAttribute() {
    $return = null;
    if($this->adult) {
      $return = 'Adult';
    } else if(!empty($this->attributes['user_type']) && !empty($this->attributes['gender'])) {
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
  	if($this->team_email == '' && !empty($teamDomain) && strpos($userData['email'],'@'.$teamDomain) !== false) {
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
  			'full_name' => $this->fname.' '.$this->lname,
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
  	$data = $this->getNotificationOptions();
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
    $data = $this->getNotificationOptions();
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
    $slack_enable = getSettingsProp('slack_enable');
    $email_enable = getSettingsProp('email_enable');
  	//$preferences = array('push' => true, 'email' => false);
    if($slack_enable && ($type == '' || $preferences['email'][$type] == true) && !empty($msgData['email'])) {
  		$msg = $msgData['email'];
  		$subject = $msg['subject'];
  		$content = $msg['content'];
  		$attachments = !empty($msg['attachments']) && is_array($msg['attachments']) ? $msg['attachments'] : false;
  		$this->emailUser($subject,$content,$attachments);
  	}
  	if($email_enable && ($type == '' || $preferences['slack'][$type] == true) && !empty($msgData['slack'])) {
  		$msg = $msgData['slack'];
  		$title = $msg['title'];
  		$body = $msg['body'];
  		$tag = '';
  		$note_id = uniqid();
  		$this->slackMessage($body);
  	}
  }

  public function getGenderByFirstName() {
  	$return = false;
    $name = $this->fname;
  	if(!empty($name)) {
      $client = new \GuzzleHttp\Client();
      $name = preg_replace("/[^A-Za-z ]/", '', $name);
      $names = array($name);
      $explode = explode(' ',$name);
      if(count($explode) > 1) {
        $names[] = str_replace(' ','',$name);
        $names = array_merge($names,$explode);
      }
      $response = $client->request('GET', 'https://api.genderize.io', array(
        'query' => array(
          'name' => $names
        )
      ));
      $contents = json_decode($response->getBody());
  		if(!empty($contents)) {
        foreach($contents as $name) {
          if(!empty($name->gender) && $name->probability > .90) {
      			$this->gender = ucfirst($name->gender);
          	return true;
      		}
        }
      }
  	}
    $this->gender = '';
  	return false;
  }

  public function slackMessage($msg = '', $attachments = null) {
    $slack_enable = getSettingsProp('slack_enable');
		if($slack_enable && $this->slack_enabled == true && !empty($msg)) {
			return postToSlack($msg, $this->slack_id);
		} else if(!$this->slack_enabled) {
      insertLogs('Warning', $this->full_name.' is not slack enabled.');
    }
  	return false;
  }

  public function getGetSlackIdByEmail() {
    if(getSettingsProp('slack_enable')) {
      $emails = array($this->email,$this->team_email);
      foreach($emails as $email) {
        if(!empty($email)) {
          $result = slackGetAPI('users.lookupByEmail', $params = array('email'=>$email));
          $data = json_decode($result);
          if(!empty($data->ok) && $data->ok == true) {
            $this->slack_id = $data->user->id;
            return true;
          }
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

  public function emailUser($subject = '',$content = '',$attachments = false) {
  	$email_enable = getSettingsProp('email_enable');
  	if(!$email_enable) {
  		return false;
  	}

  	$html = file_get_contents(__DIR__.'/../libraries/email/email_template.html');
  	$css = file_get_contents(__DIR__.'/../libraries/email/email_css.css');
  	$emogrifier = new \Pelago\Emogrifier($html, $css);
  	$mergedHtml = $emogrifier->emogrify();

  	$subjectLine = $subject;
  	$emailContent = $content ;
  	$teamName = getSettingsProp('team_name');
  	$teamNumber = getSettingsProp('team_number');
  	$teamLocation = getSettingsProp('location');
  	$envUrl = getSettingsProp('env_url');
  	$email = str_replace('###TEAM_NAME###',$teamName,$mergedHtml);
  	$email = str_replace('###TEAM_NUMBER###',$teamNumber,$email);
  	$email = str_replace('###TEAM_LOCATION###',$teamLocation,$email);
  	$email = str_replace('###ENV_URL###',$envUrl,$email);
  	$email = str_replace('###SUBJECT###',$subjectLine,$email);
  	$email = str_replace('###FNAME###',$this->fname,$email);
  	$email = str_replace('###CONTENT###',$emailContent,$email);
  	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
  	try {
  	    //Server settings
        if(getSettingsProp('email_enable_smtp')) {
          //$mail->SMTPDebug = 3;
          // $mail->Debugoutput = function($str, $level) {
          //   insertLogs($level = 'Warning', 'Email Error: '.$str);
          // };                                           // Enable verbose debug output
    	    $mail->isSMTP();                                                // Set mailer to use SMTP
    	    $mail->Host = getSettingsProp('email_smtp_server');            // Specify main and backup SMTP servers
    	    $mail->SMTPAuth = true;                                        // Enable SMTP authentication
    	    $mail->Username = getSettingsProp('email_smtp_user');          // SMTP username
          $password = decryptItems(getSettingsProp('email_smtp_password'));
          if($password == false) {
            throw new Exception("Could not decrypt SMTP password");
          }
    	    $mail->Password = $password;                                    // SMTP password
    	    $mail->SMTPSecure = getSettingsProp('email_smtp_encryption');  // Enable TLS encryption, `ssl` also accepted
    	    $mail->Port = getSettingsProp('email_smtp_port');              // TCP port to connect to
          $mail->SMTPOptions = array(
              'ssl' => array(
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true
              )
          );
        }
  	    //Recipients
  			$mailFrom = getSettingsProp('notification_email');
  			$teamNumber = getSettingsProp('team_number');
  			$mailFromName = 'Team '.$teamNumber.' Portal';
        $mail->setFrom($mailFrom, $mailFromName);
        $replyTo = getSettingsProp('email_replyto');
        $replyTo = !empty($replyTo) ? $replyTo : $mailFrom;
  	    $mail->addReplyTo($replyTo, $mailFromName);
  	    $mail->addAddress($this->email, $this->full_name);     // Add a recipient
  	   /*  $mail->addAddress('ellen@example.com');               // Name is optional
  	    $mail->addReplyTo('info@example.com', 'Information');
  	    $mail->addCC('cc@example.com');
  	    $mail->addBCC('bcc@example.com'); */

  	    //Attachments
  			if($attachments != false && is_array($attachments)) {
  				foreach($attachments as $file) {
  					if(is_array($file) && file_exists($file['path'])) {
  						$mail->addAttachment($file['path'], $file['name']);
  					} elseif(file_exists($file)) {
  						$mail->addAttachment($file);
  					}
  				}
  			}
  	    /* $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
  	    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name */

  	    //Content
  	    $mail->isHTML(true);                                  // Set email format to HTML
  	    $mail->Subject = $subject;
  	    $mail->Body    = $email;
  	    return $mail->send();
  	} catch (Exception $e) {
      insertLogs($level = 'Warning', 'Error sending email notification. Error: '.$mail->ErrorInfo);
  		return false;
  	}
  }

  public function getNotificationOptions() {
  	$default = array(
  		'sign_in_out' => false,
  		'new_season' => false,
  		'new_event' => false,
  		'join_team' => false,
  		'dues' => false,
  		'stims' => false,
  		'event_registration' => false,
  	);
  	$data = array(
  		'slack' => $default,
  		'email' => $default,
  	);
  	return $data;
  }
}

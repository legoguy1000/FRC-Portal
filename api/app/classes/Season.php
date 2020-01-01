<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;
use Google_Client;
use Google_Service_Sheets;


class Season extends Eloquent {
  //table name
  protected $table = 'seasons';
  //Use Custom Primary Key
  protected $primaryKey = 'season_id'; // or null
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
    'season_id', 'year', 'game_name', 'game_logo', 'start_date','bag_day','end_date','hour_requirement','hour_requirement_week','join_spreadsheet','membership_form_map','membership_form_sheet'
  ];

  protected $googleFormData;

  protected $appends = ['no_bagday', 'date', 'season_period'];
  //'start_date_unix','bag_day_unix','end_date_unix','start_date_formatted','bag_day_formatted','end_date_formatted','start_date_formatted','start_date_formatted'
  //$data['requirements'] = array();
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'year' => 'integer',
    'hour_requirement' => 'integer',
    'hour_requirement_week' => 'integer',
    'membership_form_map' => 'array',
  ];

  public function save($options = array()) {
    if(is_null($this->season_id)) {
      $this->season_id = uniqid();
    }
    return parent::save();
  }

  public function getNoBagdayAttribute() {
    return $this->attributes['year'] > 2019 ? true:false;

  }

  public function getDateAttribute() {
    $start = formatDateArrays($this->attributes['start_date']);
    $end = formatDateArrays($this->attributes['end_date']);
    $bag = $this->no_bagday ? null:formatDateArrays($this->attributes['bag_day']);
    return array(
      'start' => $start,
      'end' => $end,
      'bag' => $bag
    );
  }

  public function getSeasonPeriodAttribute() {
    $start = $this->attributes['start_date'];
    $end = $this->attributes['end_date'];
    $bag = $this->attributes['bag_day'];
    $eoy = date('Y').'-12-31';
  	$date = date('Y-m-d');
    if($this->no_bagday) {
      return array(
        'build_season' => $date >= $start && $date <= $end,
        'off_season' => $date > $end && $date <= $eoy
      );
    } else {
      return array(
        'build_season' => $date >= $start && $date <= $bag,
        'competition_season' => $date > $bag && $date <= $end,
        'off_season' => $date > $end && $date <= $eoy
      );
    }
  }

  public function updateSeasonRegistrationFromForm() {
		if(!empty($this->join_spreadsheet) && $this->pollMembershipForm() && !empty($this->googleFormData)) {
			return $this->itterateMembershipFormData();
		}
  	return false;
  }

  public function pollMembershipForm() {
  	if(!empty($this->join_spreadsheet)) {
  		$data = array();
  		try {
  			$creds = getServiceAccountData();
  		} catch (Exception $e) {
  				$error = handleExceptionMessage($e);
  				insertLogs('Warning', $error);
  		}
  		try {
  			$client = new Google_Client();
  			$client->setAuthConfig($creds);
  			$client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
  			$service = new Google_Service_Sheets($client);
				$range = !empty($this->membership_form_sheet) ? $this->membership_form_sheet : 'Form Responses 1';
  			$response = $service->spreadsheets_values->get($this->join_spreadsheet, $range);
  			$values = $response->getValues();
  			if (count($values) != 0) {
  				$headers = array_map('strtolower', array_shift($values));
  				foreach ($values as $row) {
  					$temp = array();
  					for($i=0; $i<count($headers);$i++) {
  						$key = $headers[$i];
  						$val = isset($row[$i]) ? $row[$i] : '';
  						$temp[$key] = $val;
  					}
  					$data[] = $temp;
  				}
  				//$result['msg'] = 'Data pulled from Google Spreadsheet';
          $this->googleFormData = $data;
          return true;
  			}
  		} catch (Exception $e) {
  				$error = handleGoogleAPIException($e, 'Google Sheets');
  				insertLogs('Warning', $error);
  		}
  	}
  	return false;
  }

  public function itterateMembershipFormData() {
  	$team_num = getSettingsProp('team_number');
  	$team_name = getSettingsProp('team_name');

  	$season_id = $this->season_id;
  	$form_map = $this->membership_form_map;
  	$email_column = strtolower($form_map['email']); //'email address';
  	$fname_column = strtolower($form_map['fname']); //'first name';
  	$lname_column = strtolower($form_map['lname']); //'last name';
  	$userType_column = strtolower($form_map['user_type']); //'member type';
  	$grad_column = strtolower($form_map['grad_year']); //'year of graduation';
  	$school_column = strtolower($form_map['school']); //'school';
  	$pin_column = strtolower($form_map['pin_number']); //'student id';
  	$phone_column = strtolower($form_map['phone']); //'phone';

  	//Itterate through data
  	if(!empty($this->googleFormData)) {
      $slack_enable = getSettingsProp('slack_enable');
  		foreach($this->googleFormData as $userInfo) {
  			//	$timestamp = $data['timestamp'];
  			$email = $userInfo[$email_column];
  			$fname = $userInfo[$fname_column];
  			$lname = $userInfo[$lname_column];
  			$form_user_type = !empty($userInfo[$userType_column]) ? $userInfo[$userType_column]: '';
  			$user_type = $form_user_type == 'Adult' ? 'Mentor' : $form_user_type;
  			//	$birthday = $userInfo['birthday'];
  			$grad_year = !empty($grad_column) && !empty($userInfo[$grad_column]) ? getGradYear($userInfo[$grad_column]) : '';
  			$school = !empty($school_column) && !empty($userInfo[$school_column]) ? $userInfo[$school_column]: '';
  			$pin = !empty($pin_column) && !empty($userInfo[$pin_column]) ? $userInfo[$pin_column]: '';
  			$phone = !empty($phone_column) && !empty($userInfo[$phone_column]) ? $userInfo[$phone_column] : '';
  			$clean_phone = preg_replace('/[^0-9]/s', '', $phone);
        if(empty($email)) {
          insertLogs($level = 'Information', $message = 'Email is blank. Cannot import '.$fname.' '.$lname.' from Google Form.');
          continue;
        }
  			$user = null;
  			$user_id = null;
  			$user = User::where('email',$email)->orWhere('team_email',$email)->first();
  			if(empty($user)) {
  				$user = User::where('fname',$fname)->where('lname',$lname)->where('user_type',$user_type)->first();
  			}
  			//If user doesn't exist, add data to user table
  			if(empty($user)) {
  				$school_id = '';
  				if($user_type == 'Student' && $school != '') {
  					$school_id = checkSchool($school);
  				}
  				$user = new User();
  				if(checkTeamEmail($email)) {
  					$user->team_email = $email;
  				}
  				$user->email = $email;
  				$user->fname = $fname;
  				$user->lname = $lname;
  				$user->getGenderByFirstName();
  				$user->user_type = $user_type;
  				if($user_type == 'Student') {
  					if($school_id != '') {
  						$user->school_id = $school_id;
  					}
  					if(!empty($grad_year) && is_numeric($grad_year)) {
  						$user->grad_year = $grad_year;
  					}
  					if($pin != '' && is_numeric($pin)) {
  						$signin_pin = hash('SHA256',$pin);
  						$user->signin_pin = $signin_pin;
  					}
  				}
  				if($clean_phone != '' && is_numeric($clean_phone)) {
  					$user->phone = $clean_phone;
  				}
          if($slack_enable) {
			      $user->getGetSlackIdByEmail();
          }
  				//Insert Data
  				if($user->save()) {
  					$user_id = $user->user_id;
  					insertLogs($level = 'Information', $message = $user->full_name.' imported from Google Form results.');
  					$user->setDefaultNotifications();
  					$host = getSettingsProp('env_url');
  					$msgData = array(
  						'email' => array(
  						'subject' => 'User account created for '.$team_name.'\s team portal',
  						'content' =>  'Congratulations! You have been added to '.$team_name.'\s team portal.  Please go to '.$host.' to view your annual registration, event registration, season hours and more.',
  						'userData' => $user
  						)
  					);
  					$user->sendUserNotification($type = '', $msgData);
  				}
  			}
  			//Add User info into the Annual Requirements Table
  			if(!empty($season_id) && !empty($user)) {
  				$user_id = $user->user_id;
  				$season_reg = $user->annual_requirements()->where('season_id', $season_id)->first();
  				if(empty($season_reg) || !$season_reg->join_team) {
  					$season_join = AnnualRequirement::updateOrCreate(['season_id' => $season_id, 'user_id' => $user_id], ['join_team' => true]);
  					if($season_join) {
  						$msgData = array(
  								'slack' => array(
  								'title' => 'Annual Registration Complete',
  								'body' => 'Congratulations! You have completed the Team '.$team_num.' membership form for the '.$this->year.' FRC season.'
  							),
  								'email' => array(
  								'subject' => 'Annual Registration Complete',
  								'content' =>  'Congratulations! You have completed the Team '.$team_num.' membership form for the '.$this->year.' FRC season.',
  								'userData' => $user
  							)
  						);
  						$user->sendUserNotification($type = 'join_team', $msgData);
  					}
  				}
  			}
  		}
  		return true;
  	} else {
  		return false;
  	}
  }


  /**
  * Get the Annual requirements.
  */
  public function annual_requirements() {
    return $this->hasOne('FrcPortal\AnnualRequirement', 'season_id', 'season_id')->withDefault();
  }

}

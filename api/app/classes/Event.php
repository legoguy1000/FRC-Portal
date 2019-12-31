<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class Event extends Eloquent {
  //table name
  protected $table = 'events';
  //Use Custom Primary Key
  protected $primaryKey = 'event_id'; // or null
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
    'event_id', 'google_cal_id', 'name', 'type', 'event_start', 'event_end', 'registration_deadline', 'registration_deadline_gcalid', 'details', 'location', 'payment_required', 'permission_slip_required', 'food_required', 'room_required', 'drivers_required', 'poc','time_slots_required','hotel_info'
  ];


  protected $appends = ['registration_deadline_google_event','season','num_days','date','registration_deadline_date', 'past_registration'];
  //'single_day','year','event_start_unix','event_end_unix','single_month','registration_deadline_unix','registration_deadline_formatted',
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
    'payment_required' => 'boolean',
    'food_required' => 'boolean',
    'room_required' => 'boolean',
    'drivers_required' => 'boolean',
    'permission_slip_required' => 'boolean',
    'time_slots_required' => 'boolean',
    'time_slots' => 'boolean',
    'past_registration' => 'boolean',
    //'single_day' => 'boolean',
    //'single_month' => 'boolean',
  ];

  public function newQuery() {
      return parent::newQuery()->select('events.*', DB::raw('YEAR(events.event_start) AS year'));
  }

  public function save($options = array()) {
    if(is_null($this->event_id)) {
      $this->event_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->event_id = (string) uniqid();
    });
  } */

/*  public function getSingleDayAttribute() {
    $start = new DateTime($this->attributes['event_start']);
    $end = new DateTime($this->attributes['event_end']);
    return (bool) ($start->format('Y-m-d') == $end->format('Y-m-d'));
  }
  public function getSingleMonthAttribute() {
    $start = new DateTime($this->attributes['event_start']);
    $end = new DateTime($this->attributes['event_end']);
    return (bool) ($start->format('Y-m') == $end->format('Y-m'));
  }
  public function getYearAttribute() {
    return (integer) date('Y',strtotime($this->attributes['event_start']));
  }
  public function getEventStartUnixAttribute() {
    $date = new DateTime($this->attributes['event_start']);
    return $date->format('U');
  }
  public function getEventEndUnixAttribute() {
    $date = new DateTime($this->attributes['event_end']);
    return $date->format('U');
  } */
  public function getNumDaysAttribute() {
    $start = strtotime($this->attributes['event_start']);
    $end = strtotime($this->attributes['event_end']);
    $diff = $end - $start;
    return ceil($diff / (60 * 60 * 24));
  }
  public function getDateAttribute() {
    $start = formatDateArrays($this->attributes['event_start']);
    $end = formatDateArrays($this->attributes['event_end']);
    return array(
      'single_day' => (bool) ($start['date_raw'] == $end['date_raw']),
      'single_month' => (bool) ($start['date_ym'] == $end['date_ym']),
      'year' => $start['year'],
      'start' => $start,
      'end' => $end
    );
  }
  public function getRegistrationDeadlineDateAttribute() {
    $return = null;
    if(!is_null($this->registration_deadline)) {
      $date = formatDateArrays($this->registration_deadline);
      return $date;
    }
    return $return;
  }
  public function getRegistrationDeadlineGoogleEventAttribute() {
    $return = null;
    // if(!is_null($this->registration_deadline_gcalid)) {
    //   try {
    //     //$return = getGoogleCalendarEvent($this->attributes['registration_deadline_gcalid']);
    //   } catch (Exception $e) {}
    // }
    return $return;
  }
  public function getPastRegistrationAttribute() {
    if(time() > strtotime($this->event_start) || (!empty($this->registration_deadline) && time() > strtotime($this->registration_deadline))) {
      return true;
    }
    return false;
  }
  /*
  public function getRegistrationDeadlineUnixAttribute() {
    $return = null;
    if(!is_null($this->attributes['registration_deadline'])) {
      $date = new DateTime($this->attributes['registration_deadline']);
      $return = $date->format('U');
    }
    return $return;
  }
  public function getRegistrationDeadlineFormattedAttribute() {
    $return = null;
    if(!is_null($this->attributes['registration_deadline'])) {
      $date = new DateTime($this->attributes['registration_deadline']);
      $return = $date->format('F j, Y');
    }
    return $return;
  }
*/

  public function getSeasonAttribute() {
    return Season::where('year',date('Y',strtotime($this->event_start)))->first();
  }

  /**
  * Get the Event requirements.
  */
  public function event_requirements() {
    return $this->hasOne('FrcPortal\EventRequirement', 'event_id', 'event_id')->withDefault();
  }
  public function registered_users() {
      return $this->hasManyThrough('FrcPortal\User','FrcPortal\EventRequirement', 'event_id', 'user_id', 'event_id', 'user_id');
  }
  /**
  * Get the Event Cars.
  */
  public function event_cars() {
    return $this->hasMany('FrcPortal\EventCar', 'event_id', 'event_id');
  }
  /**
   * Get the Room.
   */
  public function event_rooms() {
      return $this->hasMany('FrcPortal\EventRoom', 'event_id', 'event_id');
  }
  /**
   * Get the Time Slots.
   */
  public function event_time_slots() {
      return $this->hasMany('FrcPortal\EventTimeSlot', 'event_id', 'event_id');
  }
  /**
  * Get the POC.
  */
  public function poc() {
    return $this->hasOne('FrcPortal\User', 'user_id', 'poc_id');
  }
  /**
   * Get the Food Options.
   */
  public function event_food() {
      return $this->hasMany('FrcPortal\EventFood', 'event_id', 'event_id');
  }

  public function getUsersEventRequirements() {
		$eventReqs = User::with(['event_requirements' => function ($query) {
			  $query->where('event_id','=',$this->event_id);
			},'event_requirements.event_rooms','event_requirements.event_cars'])
			->whereExists(function ($query) {
			  $query->select(DB::raw(1))
				->from('event_requirements')
				->whereRaw('event_requirements.user_id = users.user_id')
				->where('event_requirements.registration',true)
				->where('event_requirements.event_id',$this->event_id);
			})
			->orWhere('status',true)
			->get();
  	return $eventReqs;
  }

  public function getEventCarList() {
  	$cars = array();
  	$carInfo = array();
    $event_id = $this->event_id;
  	$carInfo = $this->event_cars()->with(['driver','passengers'])->get();
  	$cars['cars'] = $carInfo->keyBy('car_id')->all();
  	//no user yet users
    $users = User::whereHas('event_requirements', function($q) {
      $q->where('event_id',$this->event_id)->where('registration',true)->whereNull('car_id');
    })->get();
  	$cars['non_select'] = $users;
  	return $cars;

  }

  public function getEventRoomList() {
  	$rooms = array();
  	$roomInfo = array();
  	$roomInfo = $this->event_rooms()->with('users')->get();
  	$rooms['rooms'] = $roomInfo->keyBy('room_id')->all();
  	//no user yet users
  	$users = User::whereHas('event_requirements', function($q) {
      $q->where('event_id',$this->event_id)->where('registration',true)->whereNull('room_id');
  	})->get();
  	$rooms['non_select'] = $users;
    #return array('rooms'=>$roomInfo, 'total'=>count($roomInfo), 'room_selection'=>$rooms);
  	return $rooms;
  }

  public function syncGoogleCalendarEvent() {
  	$calendar = getSettingsProp('google_calendar_id');
  	$google_cal_id = $this->google_cal_id;
  	if(empty($google_cal_id)) {
  		throw new Exception('Google Calendar Event ID cannot be blank', 400);
  	}
  	$ge = getGoogleCalendarEvent($google_cal_id);
  	$this->name = $ge['name'];
  	$this->details = !is_null($ge['details']) ? $ge['details'] : '';
  	$this->location = $ge['location'];
  	$this->event_start = $ge['event_start'];
  	$this->event_end = $ge['event_end'];
  	if(!empty($this->registration_deadline_gcalid)) {
  		try {
  			$ged = getGoogleCalendarEvent($this->registration_deadline_gcalid);
  			$this->registration_deadline = $ged['event_end'];
  		} catch (Exception $e) {
    		insertLogs('Warning', 'Unable to get Google Calendar event information for the registration deadline of '.$this->name);
      }
  	}
  	if(!$this->save()) {
  		throw new Exception('Something went wrong updating the event', 500);
  	}
  	return $this;
  }

}

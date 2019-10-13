<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class Season extends Eloquent {
  //table name
  protected $table = 'seasons';
  //Use Custom Primary Key
  protected $primaryKey = 'season_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'season_id', 'year', 'game_name', 'game_logo', 'start_date','bag_day','end_date','hour_requirement','hour_requirement_week','join_spreadsheet','membership_form_map','membership_form_sheet'
  ];


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
    $bag = $this->no_bagday ? null:formatDateArrays($this->no_bagday);
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

  /**
  * Get the Annual requirements.
  */
  public function annual_requirements() {
    return $this->hasOne('FrcPortal\AnnualRequirement', 'season_id', 'season_id')->withDefault();
  }

  function updateSeasonMembershipForm() {
    $result = false;
    try {
		    $searchResult = getSeasonMembershipForm($this->year);
        $result = $searchResult;
        if(is_array($searchResult) && array_key_exists('join_spreadsheet',$searchResult)) {
          $this->join_spreadsheet = $searchResult['join_spreadsheet'];
          $result = $this->save();
        }
    } catch (Exception $e) {
      $error = 'Something went wrong updating season membership form';
      insertLogs('Warning', $error);
    }
  	return $result;
  }

}

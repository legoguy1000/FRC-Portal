<?php
use \Firebase\JWT\JWT;
$app->group('/sign_in', function () {
  //Get the list of users and their last sign/out and hours
  $this->get('/list', function ($request, $response, $args) {
    $season = FrcPortal\Season::where('year',date('Y'))->get();
    $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
      $query->where('season_id', $season[0]->season_id); // fields from comments table,
    }, 'last_sign_in'])->where('status','1')->get();
    $response = $response->withJson($users);
    return $response;
  });
  //Create a new signin token
  $this->post('/authorize', function ($request, $response, $args) {
    $args = $request->getParsedBody();
    $responseArr = array();
    $user = false;
    if(isset($args['auth_token'])) {
      $key = getIniProp('jwt_key');
      $jwt = $args['auth_token'];
      try {
  			$decoded = JWT::decode($jwt, $key, array('HS256'));
        $user = $decoded->data;
  		} catch(\Firebase\JWT\ExpiredException $e) {
  			$responseArr = array('status'=>false, 'msg'=>'Authorization Error. '.$e->getMessage());
  		} catch(\Firebase\JWT\SignatureInvalidException $e){
  			$responseArr = array('status'=>false, 'msg'=>'Authorization Error. '.$e->getMessage())
  		}
    } elseif(isset($args['auth_code'])) {
      $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['auth_code']))->where('status','=','1')->where('admin','=','1');
      $user = $user[0];
    } else {
      $responseArr = array('status'=>false, 'msg'=>'Invalid request');
    }
    if($user != false) {
      $jti = md5(random_bytes(20));
      $key = getIniProp('jwt_signin_key');
      $token = array(
        "iss" => getIniProp('env_url'),
        "iat" => time(),
        "exp" => time()+60*60*12, //12 hours liftime
        "jti" => $jti,
        'data' => array(
          'signin' => true
        )
      );
      $jwt = JWT::encode($token, $key);
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$jwt);
    } else {
      $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
  //Deauthorize the current signin token
  $this->post('/deauthorize', function ($request, $response, $args) {

    $response = $response->withJson($users);
    return $response;
  });
  //Clock in and Out
  $this->post('', function ($request, $response, $args) {
    $args = $request->getParsedBody();
    if(isset($args['token'])) {
      $key = getIniProp('jwt_signin_key');
      try{
      	$decoded = JWT::decode($jwt, $key, array('HS256'));
        $data = (array) $decoded;
        if(isset($data['jti']) || $data['jti'] != '') {
          $jti = $data['jti'];
          if(isset($args['pin']) && isset($args['user_id']) && $args['pin'] != '' && $args['user_id'] != '') {
            $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['pin']))->where('user_id',$args['user_id'])->where('status','=','1');
            if($user->count() > 0) {
              $user_id = $user->user_id;
              $name = $user->full_name;
              $date = time();
              $hours = FrcPortal\MeeetingHour::where('user_id',$user_id)->whereNotNull('time_in')->whereNull('time_out')->orderBy('time_in','DESC')->limit(1)->get();
              if($hours->count() > 0) {
                $hours_id = $hours->hours_id;
                $hour = $hours[0];
                $hour->time_out = $date;
                if($hour->save()) {
            			/*$emailData = array(
            				'signin_time' => date('M d, Y H:i:s A', $date),
            				'signin_out' => 'sign_out'
            			);
            			$emailInfo = emailSignInOut($user_id,$emailData);
            			$msgData = array(
            				'slack' => array(
            					'title' => 'Sign out',
            					'body' => 'You signed out at '.$emailData['signin_time']
            				),
            				'email' => array(
            					'subject' => $emailInfo['subject'],
            					'content' =>  $emailInfo['content'],
            					'userData' => $userInfo
            				)
            			);
            			sendUserNotification($user_id, 'sign_in_out', $msgData);*/
                  $season = FrcPortal\Season::where('year',date('Y'))->get();
                  $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
                    $query->where('season_id', $season[0]->season_id); // fields from comments table,
                  }, 'last_sign_in'])->where('status','1')->get();

            			$responseArr = array('status'=>true, 'msg'=>$name.' signed out at '.date('M d, Y H:i:s A', $date), 'signInList'=>$users);
            		} else {
            		$responseArr = 	array('status'=>false, 'msg'=>'Something went wrong signing out');
            		}
              } else {
                $hours_id = uniqid();
                $hours = FrcPortal\MeeetingHour::create(['hours_id' => $hours_id, 'user_id' => $user_id, 'time_in' => $date]);
                if($hours) {
                  $season = FrcPortal\Season::where('year',date('Y'))->get();
                  $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
                    $query->where('season_id', $season[0]->season_id); // fields from comments table,
                  }, 'last_sign_in'])->where('status','1')->get();

            			$responseArr = array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i:s A', $date), 'signInList'=>$users);
            		} else {
                  $responseArr = array('status'=>false, 'msg'=>'Something went wrong signing out');
            		}
              }
            }  else {
            	$responseArr = array('status'=>false, 'msg'=>'PIN is incorrect');
            }
          } else {
            $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'User ID and/or PIN number cannot be blank!');
          }
        } else {
          $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.');
        }
      } catch(\Firebase\JWT\ExpiredException $e){
      	$responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
      } catch(\Firebase\JWT\SignatureInvalidException $e){
        $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
      }
    } else {
      $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.');
    }
    $response = $response->withJson($users);
    return $response;
  });
});

















?>

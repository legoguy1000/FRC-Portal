<?php
function getSignInList($year = null) {
	if(is_null($year)) {
		$year = date('Y');
	}
	$users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($year)  {
		//$query->leftJoin('seasons', 'annual_requirements.season_id', '=', 'seasons.season_id')->where('seasons.year', $year);
		$query->whereHas('seasons', function ($query) use ($year)  {
			$query->where('year', $year);
		});
	}, 'last_sign_in'])->where('status',true)->get();
	return $users;
}

 ?>

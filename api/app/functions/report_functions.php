<?php
function checkReportInputs($request, $response, $type = 'range') {
   if($type == 'range') {
	    if($request->getParam('start_date') == null|| $request->getParam('start_date') == '' || !is_numeric($request->getParam('start_date'))) {
				return badRequestResponse($response, $msg = 'Invalid Start Date');
	    }
	    if($request->getParam('end_date') == null || $request->getParam('end_date') == '' || !is_numeric($request->getParam('end_date'))) {
				return badRequestResponse($response, $msg = 'Invalid End Date');
	    }
    } elseif($type == 'single') {
	    if($request->getParam('year') == null|| $request->getParam('year') == '' || !is_numeric($request->getParam('year'))) {
				return badRequestResponse($response, $msg = 'Invalid Year');
	    }
    }
    return true;
}

function initializeMultiYearData($start = 0, $end = 0, $series = array()) {
	$years = array();
	for($i = $start; $i <= $end; $i++) {
		$years[] = (integer) $i;
	}
	$data = array();
	foreach($series as $se) {
		$data[$se] = array_fill_keys($years,0);
	}
	return array(
		'years' => $years,
		'data' => $data,
	);
}

function multiYearReportData($data = array(), $series = array(), $years = array()) {
	$newData = array();
	foreach($series as $se) {
		$newData[$se] = array_values($data[$se]);
	}

	$allData = array(
		'labels' => $years,
		'series' => $series,
		'data' => array_values($newData),
		//'csvData' => metricsCreateCsvData($data, $years, $series)
	);

	return $allData;
}

?>

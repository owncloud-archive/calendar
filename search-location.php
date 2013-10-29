<?php

\OCP\User::checkLoggedIn();

$result = \OCP\Contacts::search($_REQUEST['term'], array('FN', 'ADR'));

$contacts = array();

foreach ($result as $r) {
  $tmp = $r['ADR'][0];
  $address = trim(implode(" ", $tmp));
  
  $contacts[] = array('label' => $address);
}

echo json_encode($contacts);
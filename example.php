<?php 
include "otpravka.php";

$otpravka = new otpravka("-=accessToken=-", "-=passwordToken=-");
$res = $otpravka->getBlockInfoExtended($partiakNumber);

if ($res == false) {
    print "error".PHP_EOL;
    print_r ($otpravka->lastError());
} else {
    foreach ($res as $rec) {
        print $rec['id');
    }
}

#!/bin/php
<?php

$ncid = '123456';
$apikey = '1234567890asdfghjkl0987654321';
$apipw = '1234567890asdfghjkl0987654321';
$domain = 'xyz.de';

$ipv4 = true;
$ipv6 = false;

$url4 = 'https://ip4.irgendwas.ti';
$url6 = 'https://ip6.irgendwas.ti';

$url = 'https://ccp.netcup.net/run/webservice/servers/endpoint.php?JSON';

//get ip4
function getip4($url4) {

   $pip = rtrim(@file_get_contents($url4));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
       return $pip;
   }
   echo "Get IP4 Fehler !\n";
   exit(1);
}

//get ip6
function getip6($url6) {

   $pip = rtrim(@file_get_contents($url6));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
       return $pip;
   }
   echo "Get IP6 Fehler ! --> IP6 is enabled you have IP6 ?\n";
   exit(1);
}

//Declare some variables
$dir = getcwd();
$cip4 = '/cip4.log';
$cip6 = '/cip6.log';

function checkcachedip($dir, $ip, $pip) {
    //Checks if local log exists
    if (!file_exists($dir.$ip)) {
        file_put_contents($dir.$ip, '');
        chmod($dir.$ip, 0600);
    }
    //Compare local ip - public ip
    if (trim(file_get_contents($dir.$ip)) === $pip) {
       return true;
       }
       else {
       return false;
    }
}

function curlsend($data) {

global $url;

    $curlopt = [CURLOPT_POST => 1,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FAILONERROR => 1,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => $data
               ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $curlopt);
    $code = curl_exec($ch);
    //$cod = trim($cod1);
      if (curl_errno($ch)) {
          echo "Fehler: CURL_EXEC";
          exit(1);
      }
    curl_close($ch);
    $code = json_decode($code, true);
    return $code;
}

function login($ncid, $apikey, $apipw) {

    $logindata = ['action' => 'login',
                  'param' => ['customernumber' => $ncid,
                              'apikey' => $apikey,
                              'apipassword' => $apipw
                             ]
                 ];

    $data = json_encode($logindata);
    $rcode = curlsend($data);

      if ($rcode['status'] === 'success') {
          echo "Login: ".$rcode['status']."\n";
          return $rcode['responsedata']['apisessionid'];
      }
    //Login failed
    echo "Login: ".$rcode['status']."\n";
    exit(1);
}

function logout($ncid, $apikey, $apid) {

    $logoutdata = ['action' => 'logout',
                   'param' => ['customernumber' => $ncid,
                               'apikey' => $apikey,
                               'apisessionid' => $apid
                              ]
                  ];

    $data = json_encode($logoutdata);
    $rcode = curlsend($data);

      if ($rcode['status'] === 'success') {
          echo "Logout: ".$rcode['status']."\n";
         }
         else {
            //Logout failed
            echo "Logout: ".$rcode['status']."\n";
            exit(1);
      }
}

function getrecords($domain, $ncid, $apikey, $apid) {

    $recordsdata = ['action' => 'infoDnsRecords',
                    'param' => ['domainname' => $domain,
                                'customernumber' => $ncid,
                                'apikey' => $apikey,
                                'apisessionid' => $apid
                               ]
                   ];

    $data = json_encode($recordsdata);
    $rcode = curlsend($data);

    if ($rcode['status'] === 'success') {
        return $rcode;
    }
    return false;
}

function modrecords($domain, $ncid, $apikey, $apid, $dnsrecords) {

    $moddata = ['action' => 'updateDnsRecords',
                'param' => ['domainname' => $domain,
                            'customernumber' => $ncid,
                            'apikey' => $apikey,
                            'apisessionid' => $apid,
                            'dnsrecordset' => ['dnsrecords' => [$dnsrecords]]
                           ]
               ];

    $data = json_encode($moddata);
    $rcode = curlsend($data);

    if ($rcode['status'] === 'success') {
        return $rcode;
    }
    return false;
}

//Start

//Declare option
$force = false;

//Check option
if (isset($argv[1]) && $argv[1] === "--force") {
   $force = true;
}

//check local ip4
if ($ipv4 && $pip4 = getip4($url4)) {
    if (checkcachedip($dir, $cip4, $pip4) && !$force) {
        exit(1);
        }
        else {
          //write new valid public IPv4 address to local file
          echo "IPv4 Address changed or Option --force is enabled.\n";
          file_put_contents($dir.$cip4, $pip4);
    }
}

//check local ip6
if ($ipv6 && $pip6 = getip6($url6)) {
    if (checkcachedip($dir, $cip6, $pip6) && !$force) {
        exit(1);
        }
        else {
          //write new valid public IPv6 address to local file
          echo "IPv6 Address changed or Option --force is enabled.\n";
          file_put_contents($dir.$cip6, $pip6);
    }
}

//get login ID
$apid = login($ncid, $apikey, $apipw);

//get records
if ($rec = getrecords($domain, $ncid, $apikey, $apid)) {
    //get ip4 records
    foreach ($rec['responsedata']['dnsrecords'] as $record) {
         if ($ipv4 && $record['type'] === "A") {
             $foundV4[] = $record;
         }
         //get ip6 records
         elseif ($ipv6 && $record['type'] === "AAAA") {
             $foundV6[] = $record;
         }
    }
}

//IP4 has changed ?
if ($ipv4) {
    foreach ($foundV4 as $record) {
        if ($record['destination'] !== $pip4) {
            //Yes, ip has changed.
            echo "IPv4 address for: ".$record['hostname']." has changed.\n";
            $record['destination'] = $pip4;
            if (modrecords($domain, $ncid, $apikey, $apid, $record)) {
                echo "IPv4 address for: ".$record['hostname']." updated successfully!\n";
                } else {
                  exit(1);
            }
        }
        else {
        //No, ip hasn't changed.
        echo "IPv4 address for: ".$record['hostname']." not changed.\n";
        }
    }
}

//IP6 has changed ?
if ($ipv6) {
    foreach ($foundV6 as $record) {
        if ($record['destination'] !== $pip6) {
            //Yes, ip has changed.
            echo "IPv6 address for: ".$record['hostname']." has changed.\n";
            $record['destination'] = $pip6;
            if (modrecords($domain, $ncid, $apikey, $apid, $record)) {
                echo "IPv6 address for: ".$record['hostname']." updated successfully!\n";
                } else {
                  exit(1);
            }
        }
        else {
        //No, ip hasn't changed.
        echo "IPv6 address for: ".$record['hostname']." not changed.\n";
        }
    }
}

//logout
logout($ncid, $apikey, $apid);

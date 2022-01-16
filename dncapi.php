#!/bin/php
<?php

$ncid = '123456';
$apikey = 'Your-apikey';
$apipw = 'Your-apipassword';
$domain = 'yourdomain.de';

$ipv4 = false;
$ipv6 = false;

$hostsv4 = [];
$hostsv6 = [];

//Example
//$hostsv4 = ['*', '@', 'server', ...];

$url4 = 'https://ip4.first.de';
$url4b = 'https://ip4.second.de';
$url6 = 'https://ip6.first.de';
$url6b = 'https://ip6.second.de';


$url = 'https://ccp.netcup.net/run/webservice/servers/endpoint.php?JSON';

// printf
$rr = "|";
$headi = "%s %16s %24s %8s %28s\n";
$bodyi = "%s %-14s %s %-22s %s %6s %s %26s %s\n";

$headf = "%s %12s %24s %27s %27s %12s\n";
$bodyf = "%s %-10s %s %-22s %s %25s %s %25s %s %10s %s\n";
$bodyf1 = "%s %-10s %s %-22s %s %66s %s\n";

function line($w) {

$full1 = '-------------------------';
$full1 = $full1.$full1.$full1.$full1.$full1;
printf("%.".$w."s\n",$full1);
}

function top($pat, $pat1, $w) {

line($w);
if ($pat1 == 1) {
$infh = sprintf($pat, "|", "ID     |", "HostName        |", "DNS IP          |", "Public IP         |", "Status   |");
} else
$infh = sprintf($pat, "|", "ID       |", "Name          |", "Type  |", "IP             |");
echo $infh;
line($w);
}

// # --> declare some functions <-- #

//Debug Mode
function debug($msg) {

global $debug;

   if ($debug) {
       echo "$msg\n";
   }
}

//get ip4
function getip4($url4, $url4b) {

   $pip = rtrim(@file_get_contents($url4));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
       debug("\nGet IPv4 from first server ip: $pip is valid");
       return $pip;
   }
   $pip = rtrim(@file_get_contents($url4b));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
       debug("\nGet IPv4 from Backup-Server connection problem ?");
       return $pip;
   }
   echo "Error --> Get IPv4 Fehler --> Exit!.\n";
   exit(1);
}

//get ip6
function getip6($url6, $url6b) {

   $pip = rtrim(@file_get_contents($url6));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
       debug("\nGet IPv6 from first server ip: $pip is valid");
       return $pip;
   }
   $pip = rtrim(@file_get_contents($url6b));
   if (filter_var($pip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
       debug("\nGet IPv6 from Backup-Server connection problem ?");
       return $pip;
   }
   echo "Error --> Get IPv6 Fehler ! --> IPv6 is enabled --> you have IPv6 ? --> Exit!.\n";
   exit(1);
}

//Declare some variables
$dir = dirname(__FILE__);
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
         echo "Error --> CURL_EXEC";
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
         echo "\nLogin: ".$rcode['status']."\n";
         return $rcode['responsedata']['apisessionid'];
     }
    //Login failed
    echo "\nError --> Login: ".$rcode['status']."\n";
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
           echo "Error --> Logout: ".$rcode['status']."\n";
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
     echo "Error --> is domainname: ".$domain." correct and exists ? --> Exit!\n";
     logout($ncid, $apikey, $apid);
     exit(1);
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

// # --> end functions <-- #
// # --> Start

//Declare options
$force = false;
$debug = false;
$hosts = false;
$info = false;

if ($argc > 1) {
    foreach($argv as $opt) {
        if ($opt === "--force") {
            echo "\nOption \"--force\" is Set: ignore's ipcheck and different ip(4/6) will be changed.\n";
            $force = true;
        }
        elseif ($opt === "--debug") {
            $debug = true;
        }
        elseif ($opt === "--hosts") {
            $hosts = true;
        }
        elseif ($opt === "--info") {
            echo "\nOption \"--info\" is Set: no changes will be made.\n";
            $info = true;
            $force = true;
            $debug = true;
        }
    }
}

//Check IP's and Options
//Check local IPv4
$chk4 = false;
if ($ipv4 && $pip4 = getip4($url4, $url4b)) {

$chk4 = checkcachedip($dir, $cip4, $pip4);

    if ($chk4 && !$force && !$ipv6) {
        //Nothing to do --> Quit
        debug("\nPublic IPv4 & cached IPv4 are equal no Option is Set --> Exit!.\n");
        exit(1);
        }
        elseif ($chk4 && $force) {
          //echo (!$debug? "\n":"")."IPv4 Address not changed but Option ".($info? "\"--info\"":"\"--force\"")." is Set. --> continue.\n";
          echo "\nIPv4 Address not changed but Option ".($info? "\"--info\"":"\"--force\"")." is Set. --> continue.\n";
          }
          elseif (!$chk4 && $info) {
            echo "\nIPv4 Address changed but Option \"--info\" is Set --> no changes will be made.\n";
            }
            elseif (!$chk4 && !$info) {
              //Write new valid public IPv4 address to local file
              echo "Info --> Public IPv4 changed --> Updating Cached IPv4";
              file_put_contents($dir.$cip4, $pip4);
    }
}

//Check local IPv6
if ($ipv6 && $pip6 = getip6($url6, $url6b)) {

$chk6 = checkcachedip($dir, $cip6, $pip6);

    if ($chk6 && !$force) {
        //Nothing to do --> Quit
        debug("\nPublic ".($chk4? "IPv4/v6":"IPv6"). " & cached ".($chk4? "IPv4/v6":"IPv6"). " are equal no Option is Set --> Exit!.\n");
        exit(1);
        }
        elseif ($chk6 && $force) {
          echo "\nIPv6 Address not changed but Option ".($info? "\"--info\"":"\"--force\"")." is Set. --> continue.\n";
          }
          elseif (!$chk6 && $info) {
            echo "\nIPv6 Address changed but Option \"--info\" is Set --> no changes will be made.\n";
            }
            elseif (!$chk6 && !$info) {
              //Write new valid public IPv4 address to local file
              echo "Info --> Public IPv6 changed --> Updating Cached IPv6";
              file_put_contents($dir.$cip6, $pip6);
    }
}

//If IPv4(and/or)/6 is set --> get login ID
if (!$ipv4 && !$ipv6) {
    echo "Error --> There is no IPv4/6 type activated - set one on true --> Exit!.\n";
    exit(1);
    }
    else {
    //some information
    debug("\nIPv4 your choice: ".($ipv4 ? "IPv4 = true.":"IPv4 = false."));
    debug("IPv6 your choice: ".($ipv6 ? "IPv6 = true.":"IPv6 = false."));
    debug("\nOption --hosts = ".($hosts ? "true.":"false."));
    debug("Your host declaration v4: ".(count($hostsv4) == 0? "0":count($hostsv4). " --> \"" .implode("\", \"", $hostsv4)."\""));
    debug("Your host declaration v6: ".(count($hostsv6) == 0? "0":count($hostsv6). " --> \"" .implode("\", \"", $hostsv6)."\""));
//Get Login ID
$apid = login($ncid, $apikey, $apipw);
debug("\nYour Login ID: ".$apid);
}

$foundV4id = [];
$foundV4names = [];
$foundV6id = [];
$foundV6names = [];

//Get All or Declared Hosts in --> $hostv4/6 if option --host is set
if ($rec = getrecords($domain, $ncid, $apikey, $apid)) {
    $anzv4 = count($hostsv4);
    $anzv6 = count($hostsv6);

    foreach ($rec['responsedata']['dnsrecords'] as $record) {
         //Get IPv4 records
         if ($ipv4 && $record['type'] == "A") {
             //If no host declaration --> $anzv4 == 0 and ...
             if ($anzv4 == 0 || ($anzv4 > 0 && $hosts == false) || ($anzv4 == 0 && $hosts == true)) {
                 $foundV4[] = $record;
                 $foundV4id[] = $record['id'];
             }
             //If hosts are declared --> $anzv4 > 0 and exists
             elseif (in_array($record['hostname'], $hostsv4)) {
                     $foundV4[] = $record;
                     $foundV4names[] = $record['hostname'];
                     $foundV4id[] = $record['id'];
             }
         }
         //Get IPv6 records
         elseif ($ipv6 && $record['type'] == "AAAA") {
         //If no host declaration --> $anzv6 == 0 and ...
             if ($anzv6 == 0 || ($anzv6 > 0 && $hosts == false) || ($anzv6 == 0 && $hosts == true)) {
                 $foundV6[] = $record;
                 $foundV6id[] = $record['id'];
             }
             //If hosts are declared --> $anzv6 > 0 and exists
             elseif (in_array($record['hostname'], $hostsv6)) {
                     $foundV6[] = $record;
                     $foundV6names[] = $record['hostname'];
                     $foundV6id[] = $record['id'];
             }
         }
    }
}

//Check Hostname declaration --> hostsv4
if ($ipv4 && $anzv4 !== 0 && $hosts == true && count($foundV4names) !== $anzv4) {
    echo "\nThere is something wrong with your IPv4 Hostname Declaration\n";
    echo "your declaration: $anzv4 --> ".implode(", ", $hostsv4)."\n";
    echo "found hostnames : ".(count($foundV4names) == 0? "0":count($foundV4names). " --> " .implode(", ", $foundV4names)). "\n";
    if (!$ipv6) {
        echo "Error --> So we --> Exit!.\n\n";
        logout($ncid, $apikey, $apid);
        echo "\n";
        exit(1);
        } else {
          echo "we don't exit cause IPv6 is enabled\n";
          echo "but we skip updating IPv4 - check please!.\n";
          $ipv4 = false;
    }
}

//Check Hostname declaration --> hostsv6
if ($ipv6 && $anzv6 !== 0 && $hosts == true && count($foundV6names) !== $anzv6) {
    echo "\nThere is something wrong with your IPv6 Hostname Declaration\n";
    echo "your declaration: $anzv6 --> ".implode(", ", $hostsv6)."\n";
    echo "found hostnames : ".(count($foundV6names) == 0? "0":count($foundV6names). " --> " .implode(", ", $foundV6names)). "\n";
    echo "Error --> So we --> Exit!.\n\n";
    logout($ncid, $apikey, $apid);
    echo "\n";
    exit(1);
}

//IPv4 has changed ?
if ($ipv4) {
    echo "\n";
    if (!$info) {
      top($headf, 1, 108);
      foreach ($foundV4 as $record) {
          if ($record['destination'] !== $pip4) {
              //Yes, IPv4 has changed.
              printf($bodyf, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['destination'], $rr, $pip4, $rr, "different", $rr);
              $record['destination'] = $pip4;
              if (modrecords($domain, $ncid, $apikey, $apid, $record)) {
                  printf($bodyf1, $rr, $record['id'], $rr, $record['hostname'], $rr, "updated successfully!                       ", $rr);
                  } else {
                    echo "\nError --> by Updating IPv4 address for: ".$record['hostname']." --> Exit!.\n";
                    logout($ncid, $apikey, $apid);
                    exit(1);
              }
          }
          else {
          //No, IPv4 hasn't changed.
          printf($bodyf, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['destination'], $rr, $pip4, $rr, "equal", $rr);
          }
      }
      line(108);
    }
    elseif ($info) {

      top($headi, 2, 81);
      foreach ($foundV4 as $record) {
          printf($bodyi, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['type'], $rr, $record['destination'], $rr);
      }
      line(81);
    }
}

//IPv6 has changed ?
if ($ipv6) {
    echo "\n";
    if (!$info) {
      top($headf, 1, 108);
      foreach ($foundV6 as $record) {
          if ($record['destination'] !== $pip6) {
              //Yes, IPv6 has changed.
              printf($bodyf, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['destination'], $rr, $pip6, $rr, "different", $rr);
              $record['destination'] = $pip6;
              if (modrecords($domain, $ncid, $apikey, $apid, $record)) {
                  printf($bodyf1, $rr, $record['id'], $rr, $record['hostname'], $rr, "updated successfully!                       ", $rr);
                  } else {
                    echo "\nError --> by Updating IPv6 address for: ".$record['hostname']." --> Exit!.\n";
                    logout($ncid, $apikey, $apid);
                    exit(1);
              }
          }
          else {
          //No, IPv6 hasn't changed.
          printf($bodyf, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['destination'], $rr, $pip6, $rr, "equal", $rr);
          }
      }
      line(108);
    }
    elseif ($info) {
      top($headi, 2, 81);
      foreach ($foundV6 as $record) {
          printf($bodyi, $rr, $record['id'], $rr, $record['hostname'], $rr, $record['type'], $rr, $record['destination'], $rr);
      }
      line(81);
    }
}

//logout
echo "\n";
logout($ncid, $apikey, $apid);
echo "\n";

<?php

$dsn='mysql:host=localhost;dbname=otp';
$username='database-username';
$password='database-password';
$basedomain='your.base.domain';

$db = new PDO($dsn, $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("SET NAMES 'utf8'");

$infile = fopen('import.csv', 'r');
$outfile = fopen('output.csv', 'w');
while (($line = fgetcsv($infile)) !== FALSE) {
  $password=randomPassword();
  array_push($line,$password);
  $st = $db->prepare('INSERT INTO users(givenName,sn,mail,schachome,password) values(?,?,?,?,?)');

  if (!$st->execute($line)) {
    throw new Exception('Failed to query database for user.');
  }

  $uid=$db->lastInsertId();
  $eppn=$uid."@".$line[3].".".$basedomain;
  array_push($line,$eppn);
  echo $eppn."\n";
  fputcsv($outfile, $line);
}

fclose($infile);
fclose($outfile);


function randomPassword() {
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ23456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 6; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}


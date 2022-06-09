<?php 
$password=null;
$hash=null;
$hash2=null;
$hash3=null;

$password = "AdminPassWord";
$hash = password_hash($password,PASSWORD_DEFAULT);
$hash2 = password_hash($password,PASSWORD_DEFAULT);
$hash3 = password_hash($password,PASSWORD_DEFAULT);

echo nl2br($hash . PHP_EOL);
echo nl2br($hash2 . PHP_EOL);
echo nl2br($hash3 . PHP_EOL . PHP_EOL);

echo var_dump(password_verify($password,$hash));
echo var_dump(password_verify($password,$hash2));
echo var_dump(password_verify($password,$hash3)) . PHP_EOL;

/*

$2y$10$M1KC.io/AbeKgpi32hZcE.75sBLjcAO3ZBuLdnuo1holPdQmpcB7.
$2y$10$nwgvDQbnCjvktuYaY/QoE.OjEso0fdmY3Z2z8o4wBA2wtQLb/2xVq
$2y$10$WfwBTVRU/tCpv/s9vTdzye/u88T79glzB/JZ9fGhVy3FVtNy93qeq
bool(true) 
bool(true) 
bool(true)

*/

?>
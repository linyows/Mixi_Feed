<?php
require_once('Mixi/Feed.php');

$mail     = 'your@email.com';
$password = 'yourpassword';

$mixi = new Mixi_Feed($mail, $password);
echo $mixi->getFriendNewDiaries('GoogleReader');

<?php
print_r($_FILES);
$uploaddir = getcwd().'/../runtime/mailchart/'; //a directory inside 
//echo $uploaddir."<br />";
//echo $_FILES["fff"]["name"]."<br />";
$file_name=basename($_FILES["fileKey"]["name"]);
//echo $file_name."<br />";
move_uploaded_file($_FILES['fileKey']['tmp_name'],$uploaddir.$file_name);
//?>

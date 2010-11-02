<?php

//echo Url::anchor('welcome/index', 'Google');
//echo Url::mailto('abdelm.is@gmail.com', 'Mail Me', 'this is a subject');
//echo Url::prep('example.com');
//echo Url::friendly_title('omg this is cool?'); // default sep: -, can use _
echo Url::mailto_safe('abdelm.is@gmail.com', 'email me');

?>
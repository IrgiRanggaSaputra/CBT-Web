<?php

$password = 'irgi'; 

$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h3>Password Asli:</h3>";
echo "<pre>$password</pre>";

echo "<h3>Hash Password:</h3>";
echo "<pre>$hash</pre>";

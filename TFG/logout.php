<?php

session_start();

//Vacío todas las variables de sesión en las que había guardado las credenciales del usuario
$_SESSION = [];

//Cierro la sesión existente.
session_destroy();

//Redirijo a la página de login
header("Location: login.html");

//Detengo cualquier otra ejecución
exit();

?>
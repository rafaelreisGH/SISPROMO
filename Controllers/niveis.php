<?php

switch($_SERVER['PHP_SELF']){
    case '/Views/pagina_gestor.php':
        $nivel_requerido = 1;
        break;
    case '/Views/pagina_admin.php':
        $nivel_requerido = 2;
        break;
}
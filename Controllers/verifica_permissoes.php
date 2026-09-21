<?php

/*
1 => Gestor
2 => Administrador
3 => Usuário
*/

if (isset($_SESSION['nivel_de_acesso']) && ($_SESSION['nivel_de_acesso'] != $nivel)) {
    header('Location: ../Views/acesso_restrito.php');
    exit();
}

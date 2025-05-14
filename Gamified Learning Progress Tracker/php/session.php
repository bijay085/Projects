<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    Header('Location: ../register/login.php');
}
$roleid = $_SESSION['roleid'];
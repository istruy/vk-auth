<?php
    require_once 'VkApi.php';

    session_start();
    $token = VkApi::authVK();
    $_SESSION['token'] = $token;

    if (isset($_SESSION['token'])) {
        header('Location: ../main.php');
    } else {
        echo "<p> Произошла ошибка </p>";
    }
<?php
    require_once 'php/VkApi.php';
    require_once 'vendor/autoload.php';

    VkApi::startSessionWithCookies();

    $loader = new Twig_Loader_Filesystem('views/');
    $twig = new Twig_Environment($loader);

    $url_index = 'index.html';

    if (!isset($_SESSION['token'])) {
        header('Location: index.html');
    } else {
        header('Location: main.php');
    }

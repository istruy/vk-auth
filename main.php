<?php
    require_once 'php/VkApi.php';
    require_once 'vendor/autoload.php';

    session_start();
    $loader = new Twig_Loader_Filesystem('views/');
    $twig = new Twig_Environment($loader);

    $user = VkApi::getUserInfo($_SESSION['token']);

    $name = $user['response'][0]['first_name'];
    $last_name = $user['response'][0]['last_name'];
    $photo = $user['response'][0]['photo_big'];

    $friends = VkApi::getFriendsUser($_SESSION['token']);
    $info_friends = array(array());

    for ($i = 0; $i < 5; $i++) {
        $info_friends[$i][1] = $friends['response']["{$i}"]['photo_100'];
        $info_friends[$i][2] = $friends['response']["{$i}"]['first_name'];
        $info_friends[$i][3] = $friends['response']["{$i}"]['last_name'];
    }

    echo $twig->render('friends.html.twig', array(
        'name' => $name, 'last_name' => $last_name, 'image_user' => $photo,
        'info_friends' => $info_friends));


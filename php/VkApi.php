<?php

class VkApi
{
    private static $client_id = "6155151"; // ID приложения
    private static $client_secret = "kNqb3G92v4zOiunCY2Ak";
    private static $redirect_uri = "https://test-vk-api.000webhostapp.com/php/auth.php"; // Адрес сайта
    public static $url = 'http://oauth.vk.com/authorize';
    public static $token_access;
    public static $code;

    public static function getAuthParams()
    {
        $params = array(
            'client_id' => self::$client_id,
            'redirect_uri' => self::$redirect_uri,
            'response_type' => 'code',
            'scope' => 'offline,friends',
            'display' => 'popup'
        );
        return $params;
    }

    private function launchUrl($url)
    {
        $result = json_decode(file_get_contents($url), true);
        return $result;
    }

    public static function authVK()
    {
        if (!isset($_SESSION['token'])) {
            //self::startSession();
            if (isset($_GET['code'])) {
                $params = array(
                    'client_id' => self::$client_id,
                    'client_secret' => self::$client_secret,
                    'redirect_uri' => self::$redirect_uri,
                    'code' => $_GET['code'],
                );
                self::$token_access = self::launchUrl('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params)));
                $_SESSION['token']=self::$token_access;
                $_SESSION['code']=$_GET['code'];
                self::$code=$_GET['code'];
                return self::$token_access;
            }
        } else {
            self::$token_access = $_SESSION['token'];
            return self::$token_access;
        }
    }

    public function buildQuery($params)
    {
        return $result = urldecode(http_build_query($params));
    }

    public static function getUserInfo($token)
    {
        $accessToken = $token;

        $urlUsersGet = 'https://api.vk.com/method/users.get';

        if (isset($accessToken)) {
            $params = array(
                'uids' => $accessToken['user_id'],
                'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                'access_token' => $accessToken['access_token'],
            );

            $query = self::buildQuery($params);
            $userInfo = self::launchUrl("{$urlUsersGet}?{$query}");
            return $userInfo;
        } else {
            echo '<p>Ошибка при получении токена</p>';
        }
    }

    private static function startSession() {
        $sessionLifetime = 1000000;

        if ( session_id() ) return true;
        ini_set('session.cookie_lifetime', $sessionLifetime);
        if ( $sessionLifetime ) ini_set('session.gc_maxlifetime', $sessionLifetime);
        if ( session_start() ) {
            setcookie(session_name(), session_id(), time()+$sessionLifetime);
            return true;
        }
        else return false;
    }

    public static function startSessionWithCookies($isUserActivity=true) {
            $sessionLifetime = 120960;

            if ( session_id() ) return true;
            // Устанавливаем время жизни куки до закрытия браузера (контролировать все будем на стороне сервера)
            ini_set('session.cookie_lifetime', 0);
            if ( ! session_start() ) return false;

            $t = time();

            if ( $sessionLifetime ) {
                // Если таймаут отсутствия активности пользователя задан,
                // проверяем время, прошедшее с момента последней активности пользователя
                // (время последнего запроса, когда была обновлена сессионная переменная lastactivity)
                if ( isset($_SESSION['lastactivity']) && $t-$_SESSION['lastactivity'] >= $sessionLifetime ) {
                    // Если время, прошедшее с момента последней активности пользователя,
                    // больше таймаута отсутствия активности, значит сессия истекла, и нужно завершить сеанс
                    self::destroySession();
                    return false;
                }
                else {
                    // Если таймаут еще не наступил,
                    // и если запрос пришел как результат активности пользователя,
                    // обновляем переменную lastactivity значением текущего времени,
                    // продлевая тем самым время сеанса еще на sessionLifetime секунд
                    if ( $isUserActivity ) $_SESSION['lastactivity'] = $t;
                }
            }

            return true;
        }

    function destroySession() {
        if ( session_id() ) {
            // Если есть активная сессия, удаляем куки сессии,
            setcookie(session_name(), session_id(), time()-60*60*24);
            // и уничтожаем сессию
            session_unset();
            session_destroy();
        }
    }

    public static function getFriendsUser($token)
    {
        $accessToken = $token;
        $urlFriendsGet = 'https://api.vk.com/method/friends.get';

        $userInfo = self::getUserInfo($token);

        if (isset($accessToken)) {
            $paramsFriendsGet = array(
                'uids' => $userInfo['response'][0]['uid'],
                'fields' => 'uid,first_name,last_name,photo_100',
                'access_token' => $accessToken['access_token'],
                'order' => 'random'
            );
            $query = self::buildQuery($paramsFriendsGet);
            $friends = self::launchUrl("{$urlFriendsGet}?{$query}");
            return $friends;
        } else {
            echo '<p>Ошибка при получении токена</p>';
        }
    }
}

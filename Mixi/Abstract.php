<?php
/**
 * Mixi_Abstract
 *
 * @package mixi
 * @version 1.0
 * @copyright 2006-2010 linyows
 * @author linyows <linyows@gmail.com>
 * @license linyows {@link http://linyo.ws/}
 */
Abstract class Mixi_Abstract
{
    const MIXI_URL          = 'http://mixi.jp/';
    const MIXI_LOGIN        = 'login.pl';
    const MIXI_HOME         = 'home.pl';
    const MIXI_FRIEND       = 'list_friend.pl';
    const MIXI_FRIEND_DIARY = 'new_friend_diary.pl';

    /**
     * @var string Mixi Login Mail & Password
     */
    protected $_accessKey = array();

    /**
     * @var mixed Login Cookies
     */
    protected $_cookies;

    /**
     * @var mixed
     */
    public $views;

    /**
     * __construct
     *
     * @param string $email
     * @param string $password
     * @return void
     */
    public function __construct($email = '', $password = '')
    {
        if (!$email || !$password) { throw new Exception('Mixi keys were not supplied'); }

        $this->_accessKey = array(
            'email'    => $email,
            'password' => $password,
        );

        $this->setCookies();
    }

    /**
     * setKeys
     *
     * @param mixed $email
     * @param mixed $password
     * @return void
     */
    public static function setKeys($email, $password)
    {
        self::$_accessKey = array(
            'email'    => $email,
            'password' => $password,
        );
    }

    /**
     * setCookies
     *
     * @return void
     */
    public function setCookies()
    {
        $data = http_build_query($this->_accessKey + array('next_url' => self::MIXI_HOME), '', '&');

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n", array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: ' . strlen($data),
                )),
                'content' => $data,
            )
        ));

        file_get_contents(self::MIXI_URL . self::MIXI_LOGIN, false, $context);

        $cookies = array();

        foreach ($http_response_header as $r) {
            if (strpos($r, 'Set-Cookie') === false) { continue; }
            $c = explode(' ', $r);
            $c = str_replace(';', '', $c[1]);
            $cookies[] = $c;
        }

        $this->_cookies = $cookies;
    }

    /**
     * getPage
     *
     * @param string $url
     * @return string
     */
    public function getPage($url = '')
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => implode("\r\n", array(
                    'Cookie: ' . implode('; ', $this->_cookies)
                ))
            )
        ));
        return mb_convert_encoding(file_get_contents($url, false, $context), 'UTF-8', 'auto');
    }

    /**
     * getHomeHtml
     *
     * @param string $url
     * @return string
     */
    public function getHomeHtml($url = '')
    {
        if ($url == '') { $url = self::MIXI_URL . self::MIXI_HOME; }
        return $this->getPage($url);
    }

    /**
     * getFriendHtml
     *
     * @param string $url
     * @return string
     */
    public function getFriendHtml($url = '')
    {
        if ($url == '') { $url = self::MIXI_URL . self::MIXI_FRIEND; }
        return $this->getPage($url);
    }

    /**
     * getFriendNewDiariesHtml
     *
     * @param string $url
     * @return string
     */
    public function getFriendNewDiariesHtml($url = '')
    {
        if ($url == '') { $url = self::MIXI_URL . self::MIXI_FRIEND_DIARY; }
        $html = $this->getPage($url);
        $html = str_replace(array("\n", "\r"), '', $html);
        preg_match_all("/href=\"(view_diary.pl\?id=.+?)\"/", $html, $matches);
        return $this->parseFriendDiaries($matches[1]);
    }

    /**
     * parseFriendDiaries
     *
     * @param array $urls
     * @return array
     */
    public function parseFriendDiaries($urls = array())
    {
        foreach ($urls as $url) {
            $html = $this->getPage(self::MIXI_URL . $url);
            $html = str_replace(array("\n", "\r"), '', $html);
            $regex = "/<div class=\"diaryTitleFriend clearfix\">.*?<h2>(.*?)<\/h2>.*?<div class=\"listDiaryTitle\">.*?<dt>(.*?)<\/dt>.*?<dd>(.*?)<\/dd>.*?<\/div>.*?<div class=\"txtconfirmArea\">(.*?)<!--\/viewDiaryBox-->/";
            if (preg_match($regex, $html, $matches)) {
                $diaries[] = array(
                    'title' => strip_tags($matches[2]) . ' - ' . strip_tags($matches[1]),
                    'date'  => preg_replace("/([0-9]{4}).*?([0-9]{2}).*?([0-9]{2}).*?([0-9]{2}):([0-9]{2})/", "$1-$2-$3T$4:$5:00Z", strip_tags($matches[3])),
                    'link'  => self::MIXI_URL . $url,
                    'body'  => strip_tags($matches[4], '<a><img><br>'),
                    'author'=> mb_ereg_replace("さんの日記", "", strip_tags($matches[1])),
                );
            }
        }
        return $diaries;
    }

    /**
     * _render
     *
     * @param string $template
     * @return string
     */
    protected function _render($template = '')
    {
        ob_start();
        $this->{'_' . $template}();
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * h
     *
     * @param mixed $v
     * @return mixed
     */
    public static function h($v = null)
    {
        return ((is_array($v))? array_map('myhtmlspecialchars', $v): htmlspecialchars($v, ENT_QUOTES));
    }
}

<?php
require_once('Abstract.php');
require_once('Cache.php');

/**
 * Mixi_Feed
 *
 * @package mixi
 * @version 1.0
 * @copyright 2006-2010 linyows
 * @author linyows <linyows@gmail.com>
 * @license linyows {@link http://linyo.ws/}
 */
class Mixi_Feed extends Mixi_Abstract
{
    const CACHE_TIME = 21600;
    const CACHE_NAME = 'mixiFeed_getFriendNewDiaries';

    /**
     * @var array Reader UA list
     */
    var $_allowUserAgent = array(
        'GoogleReader' => 'Feedfetcher-Google',
    );

    /**
     * getFriendNewDiaries
     *
     * @return string
     */
    public function getFriendNewDiaries($allowUA = '')
    {
        if ($allowUA) {
            $allowUA = (isset($this->_allowUserAgent[$allowUA]))? $this->_allowUserAgent[$allowUA]: $allowUA;
            if (!strstr($_SERVER['HTTP_USER_AGENT'], $allowUA)) {
                header('HTTP/1.0 403 Forbidden');
                echo '403 Forbidden';
                exit;
            }
        }

        $cache = new Mixi_Cache();

        if (!$feed = $cache->getCache(self::CACHE_NAME, self::CACHE_TIME)) {

            if ($feed = $this->_getFriendNewDiaries()) {
                $cache->saveCache(self::CACHE_NAME, $feed);
            } else {
                $feed = $cache->getCache(self::CACHE_NAME, 0);
            }
        }
        return $feed;
    }

    /**
     * _getFriendNewDiaries
     *
     * @param string $url
     * @return string
     */
    protected function _getFriendNewDiaries($url = '')
    {
        if ($url == '') { $url = self::MIXI_URL . self::MIXI_FRIEND_DIARY; }
        $this->views['diaries'] = $this->getFriendNewDiariesHtml($url);
        $this->views['httpHost'] = $_SERVER['HTTP_HOST'];
        $this->views['requestUri'] = $_SERVER['REQUEST_URI'];
        return $this->_render('atom');
    }

    /**
     * _atom
     *
     * @return void
     */
    protected function _atom()
    {
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Mixi Friend Diaries</title>
    <link rel="alternate" type="text/html" href="<?php echo self::h($this->views['httpHost']); ?>" />
    <link rel="self" type="application/atom+xml" href="<?php echo self::h($this->views['requestUri']); ?>" />
    <id></id>
    <updated><?php echo self::h($val['published']); ?></updated>
    <subtitle>Mixi Friend Diaries Feed. Please Check Security</subtitle>
    <generator uri="http://linyo.ws/">LINYOWS</generator>
<?php foreach ($this->views['diaries'] as $val): ?>
<entry>
    <title><?php echo self::h($val['title']); ?></title>
    <link rel="alternate" type="text/html" href="<?php echo self::h($val['link']); ?>" />
    <id></id>
    <published><?php echo self::h($val['published']); ?></published>
    <updated><?php echo self::h($val['published']); ?></updated>
    <summary type="html">
        <?php //echo mb_strimwidth(self::h($val['body']), 0, 100, '...'); ?>
        <?php echo self::h($val['body']); ?>
    </summary>
    <author>
        <name><?php echo self::h($val['author']); ?></name>
    </author>
    <content type="html">
        <?php echo self::h($val['body']); ?>
    </content>
</entry>
<?php endforeach; ?>
</feed>
<?php
    }
}

<?php
/**
 * Mixi_Cache
 *
 * @package mixi
 * @version 1.0
 * @copyright 2006-2010 linyows
 * @author linyows <linyows@gmail.com>
 * @license linyows {@link http://linyo.ws/}
 */
class Mixi_Cache
{
    /**
     * @var string
     */
    var $_directory = 'cache/';

    /**
     * @var string
     */
    var $_prefix = 'cache---';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * getStaticFile
     *
     * @param string $cacheName
     * @param string $filePath
     * @return array | string
     */
    public function getStaticFile($cacheName, $filePath)
    {
        $cachePath = dirname(__FILE__) . '/' . $this->_directory . $this->_prefix . $cacheName;
        if (file_exists($filePath)) {
            if (file_exists($cachePath) && (filemtime($filePath) < filemtime($cachePath))) {
                $data = unserialize(file_get_contents($cachePath));
            } else {
                $data = parse_ini_file($filePath, true);
                if (!empty($data)) { $this->saveCache($cacheName, serialize($data)); }
            }
            return $data;
        }
        return 'file not exists';
    }

    /**
     * getCache
     *
     * @param string $cacheName
     * @param string $cacheTime
     * @return string | boolean
     */
    public function getCache($cacheName, $cacheTime)
    {
        $cachePath = dirname(__FILE__) . '/' . $this->_directory . $this->_prefix . $cacheName;
        if (file_exists($cachePath) && (time() - filemtime($cachePath) < $cacheTime)) {
            $data = file_get_contents($cachePath);
            return $data;
        }
        return false;
    }

    /**
     * saveCache
     *
     * @param string $cacheName
     * @param mixed $data
     * @return void
     */
    public function saveCache($cacheName, $data)
    {
        $cachePath = dirname(__FILE__) . '/' . $this->_directory . $this->_prefix . $cacheName;
        $fp = @fopen($cachePath, 'w');
        if (false != $fp) {
            @fwrite($fp, $data, strlen($data));
            @fclose($fp);
            @chmod($cachePath, 0664);
        }
    }

    /**
     * setParam
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setParam($key, $value)
    {
        switch ($key) {
            case 'prefix':    $this->_prefix    = $value; break;
            case 'directory': $this->_directory = $value; break;
            default:                                      break;
        }
    }
}

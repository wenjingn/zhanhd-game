<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Library;

/**
 *
 */
class Curl
{
    /**
     * @const integer
     */
    const FORMAT_STRING = 1;
    const FORMAT_ARRAY  = 2;

    /**
     * @resource CURL
     */
    private $curl = null;

    /**
     * @var string
     */
    private $protocol = 'http';

    /**
     * @var string
     */
    private $host = null;

    /**
     * @var integer
     */
    private $port = null;

    /**
     * @var string
     */
    private $path = null;

    /**
     * @var string
     */
    private $query = null;

    /**
     * @var string
     */
    private $cookie = null;

    /**
     * @var string
     */
    private $post = null;

    /**
     * @var integer
     */
    private $errno = null;

    /**
     * @var string
     */
    private $error = null;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->curl = curl_init(); 
    }

    /**
     * @param array $params
     * @param string $glue
     * @return string
     */
    private static function buildQuery(array $params, $glue = '&')
    {
        $ret = [];
        foreach ($params as $k => $v) {
            $ret[] = rawurlencode($k).'='.rawurlencode($v);
        }
        
        return implode($glue, $ret);
    }

    /**
     * @param string $params
     * @param string $delimiter
     * @return array
     */
    private static function parseQuery($params, $delimiter = '&')
    {
        $params = explode($delimiter, $params);
        $ret = [];
        foreach ($params as $p) {
            $pair = explode('=', $p);
            $ret[rawurldecode($pair[0])] = rawurldecode($pair[1]);
        }
        return $ret;
    }

    /**
     * @return integer
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $protocol
     * @return self
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $host
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string $host
     */
    public function getHost($host)
    {
        return $this->host;
    }

    /**
     * @param integer $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string|array $query
     * @return self
     */
    public function setQuery($query)
    {
        if (is_string($query)) {
            $this->query = $query;
            return $this;
        }

        if (!is_array($query)) {
            return $this;
        }

        $this->query = self::buildQuery($query);
        return $this;
    }

    /**
     * @param integer $flag
     * @return string|array
     */
    public function getQuery($flag = 1)
    {
        if ($flag === self::FORMAT_STRING) {
            return $this->query;
        }

        return self::parseQuery($this->query);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->port === null) {
            if (!empty($this->query)) {
                return sprintf('%s://%s%s?%s', $this->protocol, $this->host, $this->path, $this->query);
            }
            return sprintf('%s://%s%s', $this->protocol, $this->host, $this->path);
        } else {
            if (!empty($this->query)) {
                return sprintf('%s://%s:%d%s?%s', $this->protocol, $this->host, $this->port, $this->path, $this->query);
            }
            return sprintf('%s://%s:%d%s', $this->protocol, $this->host, $this->port, $this->path);
        }
    }

    /**
     * @param string|array $post
     * @param callable $encoder
     * @return self
     */
    public function setPost($post, callable $encoder = null)
    {
        if (is_string($post)) {
            $this->post = $post;
            return $this;
        }

        if (!is_array($post)) {
            return $this;
        }
        
        if (null === $encoder) {
            $this->post = self::buildQuery($post);
            return $this;
        }

        $this->post = $encoder($post);
        return $this;
    }
    
    /**
     * @param integer $flag
     * @param callable $decoder
     * @return string|array
     */
    public function getPost($flag = 1, callable $decoder = null)
    {
        if ($flag === self::FORMAT_STRING) {
            return $this->post;
        }

        if (null === $decoder) {
            return self::parseQuery($this->post);
        }

        return $decoder($this->post);
    }

    /**
     * @param string|array $cookie
     * @return self
     */
    public function setCookie($cookie)
    {
        if (is_string($cookie)) {
            $this->cookie = $cookie;
            return $this;
        }

        if (!is_array($cookie)) {
            return $this;
        }

        $this->cookie = self::buildQuery($cookie, '; ');
        return $this;
    }

    /**
     * @param integer $flag
     * @return string|array
     */
    public function getCookie($flag = 1)
    {
        if ($flag === self::FORMAT_STRING) {
            return $this->cookie;
        }

        return self::parseQuery($this->cookie, '; ');
    }

    /**
     * @return boolean|string
     */
    public function request()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->getUrl());
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 3);

        if ($this->protocol == 'https') {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($this->post) {
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->post);
        }

        if ($this->cookie) {
            curl_setopt($this->curl, CURLOPT_COOKIE, $this->cookie);
        }

        /* process response */
        $ret = curl_exec($this->curl);
        if (false === $ret) {
            $this->errno = curl_errno($this->curl);
            $this->error = curl_error($this->curl);
            return false;
        }

        return $ret;
    }

    /**
     * @return void
     */
    public function close()
    {
        curl_close($this->curl);
    }

    /**
     * @return void
     */
    public function debug()
    {
        printf("Url:    %s\n", $this->getUrl());
        printf("Cookie: %s\n", $this->cookie);
        printf("Post:   %s\n", $this->post);
    }
}

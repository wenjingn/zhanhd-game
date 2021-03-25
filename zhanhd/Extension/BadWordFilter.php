<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
class BadWordFilter
{
    /**
     * @var array
     */
    private static $tree;

    /**
     * @param array $badwords
     * @return void
     */
    public static function setBadwords($badwords)
    {
        self::$tree = $badwords;
    }

    /**
     * @param string $text
     * @return boolean
     */
    public function check($text)
    {
        $len = mb_strlen($text);
        $tree = self::$tree;

        for ($i = 0; $i < $len; $i++) {
            $singleWord = mb_substr($text, $i, 1);
            if (false === isset($tree[$singleWord])) {
                continue;
            }

            $tree = $tree[$singleWord];
            if (isset($tree['EOF'])) {
                return false;
            }

            for ($j = $i + 1; $j < $len; $j++) {
                $singleWord = mb_substr($text, $j, 1);
                if (false === isset($tree[$singleWord])) {
                    break;
                }

                $tree = $tree[$singleWord];
                if (isset($tree['EOF'])) {
                    return false;
                }
            }

            $tree = self::$tree;
        }

        return true;
    }
    
    /**
     * @param string $text
     * @param string &$badword
     * @return boolean
     */
    public function checkVerbose($text, &$badword)
    {
        $len = mb_strlen($text);
        $tree = self::$tree;

        for ($i = 0; $i < $len; $i++) {
            $singleWord = mb_substr($text, $i, 1);
            if (false === isset($tree[$singleWord])) {
                continue;
            }

            $tree = $tree[$singleWord];
            if (isset($tree['EOF'])) {
                $badword = $singleWord;
                return false;
            }

            for ($j = $i + 1; $j < $len; $j++) {
                $singleWord = mb_substr($text, $j, 1);
                if (false === isset($tree[$singleWord])) {
                    break;
                }

                $tree = $tree[$singleWord];
                if (isset($tree['EOF'])) {
                    $badword = mb_substr($text, $i, $j-$i+1);
                    return false;
                }
            }

            $tree = self::$tree;
        }

        return true;
    }
}

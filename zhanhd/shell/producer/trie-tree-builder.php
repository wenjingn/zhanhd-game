<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';
require '/data/php/library/phpexcel/PHPExcel.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
class TrieTreeBuilder
{
    /**
     * @return void
     */
    private $tree = [];

    /**
     * @param string $infile
     * @return void
     */
    public function import($infile)
    {
        $excel = PHPExcel_IOFactory::load($infile);
        $sheet = $excel->getSheet(0);
        $row = 1;

        $emptyRow = 0;
        while ($emptyRow < 5) {
            $word = trim($sheet->getCellByColumnAndRow(0, $row++)->getCalculatedValue());
            if (empty($word)) {
                $emptyRow++;
                continue;
            }
            $this->insert($word);
            $emptyRow = 0;
        }
        return $this;
    }

    /**
     * @param string $word
     * @return void
     */
    public function insert($word)
    {
        $len = mb_strlen($word);
        $tree = &$this->tree;
        for ($i = 0; $i < $len; $i++) {
            $singleWord = mb_substr($word, $i, 1);
            if (false === isset($tree[$singleWord])) {
                $tree[$singleWord] = [];
            }
            $tree = &$tree[$singleWord];
        }
        $tree['EOF'] = 1;
    }

    /**
     * @param string $outfile
     * @return void
     */
    public function export($dest)
    {
        file_put_contents($dest, '<?php return '.var_export($this->tree, true).';');
    }
}

$argvs = (new Object)->import(getlongopt([
    'source' => false,
    'dest'   => false,
]));

$builder = new TrieTreeBuilder;
$builder->import($argvs->source)->export($argvs->dest);

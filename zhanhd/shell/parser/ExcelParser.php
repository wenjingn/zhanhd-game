<?php
/**
 * $Id$
 */

/**
 *
 */
(new PsrAutoloader)->register('System', '/data/php/games/system');
(new PsrAutoloader)->register('Zhanhd', '/data/php/games/zhanhd');
require '/data/php/library/phpexcel/PHPExcel.php';

/**
 *
 */
abstract class ExcelParser
{
    /**
     * @const integer
     */
    const MODE_ALL  = 1;
    const MODE_CALL = 2;
    const MODE_SHOW = 3;

    /**
     * @param string $excel
     * @param string $calls
     * @return void
     */
    public function __construct($excel, $calls)
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods();
        $this->handlers = [];
        foreach ($methods as $method) {
            if ($i = strpos($method->name, '_handler')) {
                $this->handlers[substr($method->name, 0, $i)] = $method;
            }
        }


        $calls = explode(',', $calls);
        $this->mode = self::MODE_CALL;
        $this->calls = [];
        foreach ($calls as $call) {
            if ($call == 'all') {
                $this->mode = self::MODE_ALL;
                break;
            } else if ($call == 'handlers') {
                $this->mode = self::MODE_SHOW;
                return; /* no need to load excel */
            }
            $this->calls[] = $call;
        }

        $this->excel = PHPExcel_IOFactory::load($excel);
    }

    /**
     * @return void
     */
    public function exec()
    {
        if ($this->mode == self::MODE_ALL) {
            foreach ($this->handlers as $handler) {
                $handler->invoke($this, $this->excel);
            }
        } else if ($this->mode == self::MODE_SHOW) {
            print_r(array_keys($this->handlers));
        } else {
            foreach ($this->calls as $call) {
                if (!isset($this->handlers[$call])) {
                    throw new Exception(sprintf('notfound handler [%s]', $call));
                }
                $this->handlers[$call]->invoke($this, $this->excel);
            }
        }
    }
}

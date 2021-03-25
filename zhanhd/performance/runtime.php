<?php
/**
 * $Id$
 */

/**
 *
 */
class Runtime
{
    /**
     * @var string
     */
    private $info = '';

    /**
     * @var integer
     */
    private $start = 0;

    /**
     * @return void
     */
    public function __construct($info)
    {
        $this->info = $info;
        $this->start = ustime();
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        printf("%s cost: %d us\n", $this->info, ustime() - $this->start);
    }
}

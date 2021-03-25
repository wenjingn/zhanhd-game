<?php
/**
 * $Id$
 */

/**
 *
 */
$params = getlongopt(['appdir' => '/data/php/games/zhanhd', 'rundir' => null, 'verbose' => 0, 'logfile' => null]);
define('APPDIR', $params['appdir']);
if (isset($params['rundir'])) {
    define('RUNDIR', $params['rundir']);
}
(new PsrAutoloader)->register('System', '/data/php/games/system');
(new PsrAutoloader)->register('Zhanhd', APPDIR);

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Object\DatabaseObject,
    System\Object\DatabaseProfile;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Extension\PvpRank\Module as PvpRankingModule;

/**
 *
 */
class Bootstrap
{
    /**
     * @var Object
     */
    public $globals;

    /**
     * @return void
     */
    public function __construct($verbose = 0, $logfile = null)
    {
        $this->verbose = $verbose;
        $this->logfile = $logfile;
        $this->svConfig = new Object;
        $this->svConfig->appdir = APPDIR;
        if (defined('RUNDIR')) {
            $this->svConfig->rundir = RUNDIR;
        }
        $this->globals = new Object;
        $this->appcfgs = (new Object)->import(include APPDIR.'/settings.php');
        $this->appcfgs->initial();

        $this->globals->setTime(ustime());
		$this->globals->task = function($type, $req) {
			printf("run task:%s\n", $type);
			print_r($req);
		};
    }

    /**
     * @param  callable $callback
     * @param  integer  $times
     * @return void
     */
    public function runtimes(callable $callback, $times)
    {
        $start = ustime();
        for ($i = 0; $i < $times; $i++) {
            $callback();
        }
        $waste = ustime() - $start;
        printf("times : %d , waste : %d micro sec\n", $times, $waste);
    }

    /**
     * @param mixed
     * @return string
     */
    public function phpdata_export($data)
    {
        $phpdata = var_export($data, true);
        return str_replace('stdClass::__set_state', '(object)', $phpdata);
    }

    /**
     * @var string
     */
    protected static $logFlags = '+-$#';

    /**
     * @var integer
     */
    const LOG_DEBUG   = 0;
    const LOG_VERBOSE = 1;
    const LOG_NOTICE  = 2;
    const LOG_WARNING = 3;

    /**
     * @return void
     */
    public function log($lvl, $fmt, ...$argvs)
    {
        if ($lvl < $this->verbose) {
            return;
        }

        if (false === ($fp = fopen($this->logfile ?: 'php://stdout', 'a'))) {
            return;
        }

        fprintf($fp, "[%s] %s $fmt\n", usdate(), self::$logFlags[$lvl], ... $argvs);

        fflush($fp);
        if ($this->logfile) {
            fclose($fp);
        }
    }
}

$boot = new Bootstrap($params['verbose'], isset($params['logfile']) ? $params['logfile'] : null);

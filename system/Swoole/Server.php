<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Swoole;

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\SingletonTrait;

/**
 *
 */
use ReflectionClass;

/**
 *
 */
use RecursiveIteratorIterator,
    RecursiveDirectoryIterator;

/**
 *
 */
use Exception,
    ErrorException;

/**
 *
 */
use swoole_server as PhpSwooleServer;

/**
 *
 */
final class Server
{
    /**
     *
     */
    use SingletonTrait;

    /**
     * @var PhpSwooleServer
     */
    private $swServer = null;

    /**
     * @var Object
     */
    private $svConfig = null;

    /**
     * @var Object
     */
    public $appcfgs = null,
            $globals = null,
            $clients = null,
            $cmdSets = null,
            $taskMap = null,
            $taskSet = null,
            $pipeSet = null;

    /**
     * @var mixed
     */
    private $startustime = null,
            $clientcache = null,
            $bulkmaxsize = null,
            $slowcmdproc = null;

    /**
     *
     * @param  Object $options
     * @return void
     */
    public function start(Object $options)
    {
        /**
         * validating application settings
         */
        $options->settings = $options->appdir . '/settings.php';
        $options->runtimes = isset($options->rundir) ? $options->rundir.'/runtimes' : $options->appdir.'/runtimes';

        if (false === is_readable($options->settings)) {
            throw new Exception('invalid application settings');
        }

        /**
         * validating application runtimes
         */
        if (false === is_writable($options->runtimes)) {
            throw new Exception('invalid application runtimes');
        }

        /**
         * merging default options
         */
        $this->svConfig = $options->import(array(
            'dispatch-mode' => 2,
        ));

        /**
         * initialize SwooleServer
         */
        $this->swServer = new PhpSwooleServer(
            $this->svConfig->host,
            $this->svConfig->port,
            SWOOLE_PROCESS,
            SWOOLE_SOCK_TCP
        );

        /**
         * setup SwooleServer options
         */
        $this->swServer->set($this->svConfig->map(null, function($k) {
            return str_replace('-', '_', $k);
        })->export());

        /**
         * setup SwooleServer listener if needed
         */
        if (($monitor = $this->svConfig->monitor) && $monitor > 0) {
            $this->swServer->addlistener('127.0.0.1', $monitor, SWOOLE_SOCK_TCP);
        }

        /**
         * setup SwooleServer ipv6 listener if needed
         */
        if (($host6 = $this->svConfig->host6) && ($port6 = $this->svConfig->port6)) {
            $this->swServer->addListener($host6, $port6, SWOOLE_SOCK_TCP6);
        }

        /**
         * setup SwooleServer callbacks
         */
        foreach ((new ReflectionClass($this))->getMethods() as $o) {
            if ($o->isStatic()) {
                continue;
            }

            if ($o->isPublic() && preg_match('~@SwooleCallback ([a-z]+)~', $o->getDocComment(), $matches)) {
                $this->swServer->on(end($matches), array(
                    $this,
                    $o->name,
                ));
            }
        }

        /**
         * cleanup previous cache
         */
        foreach (glob(sprintf('%s/*.cache', $this->svConfig->runtimes)) as $c) {
            unlink($c);
        }

        /**
         * start SwooleServer
         */
        $this->swServer->start();
    }

    /**
     *
     * @return boolean
     */
    public function stop()
    {
        if ($this->swServer && $this->swServer->shutdown()) {
            $this->appcfgs->stop();
            return true;
        }

        $this->logmessage(self::LOG_WARNING, 'failed to stop SwooleServer');
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function restart()
    {
        if ($this->swServer && $this->swServer->reload()) {
            return true;
        }

        $this->logmessage(self::LOG_WARNING, 'failed to restart SwooleServer');
        return false;
    }

    /**
     * @return PhpSwooleServer
     */
    public function getSwooleServer()
    {
        return $this->swServer;
    }

    /**
     * @SwooleCallback workerstart
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $w
     * @return void
     */
    public function onSwooleWorkerStartup(PhpSwooleServer $swServer, $w)
    {
        /**
         * change error messages into ErrorException
         */
        set_error_handler(function($errno, $error, $errfile, $errline) {
            throw new ErrorException($error, 0, $errno, $errfile, $errline);
        }, E_ALL);

        /**
         * setup server properties
         */
        $this->startustime = ustime();
        $this->clientcache = sprintf('%s/%d-%d-clients.cache', $this->svConfig->runtimes, $w, $swServer->master_pid);
        $this->bulkmaxsize = $this->svConfig->get('bulk-max-size');
        $this->slowcmdproc = $this->svConfig->get('slow-cmd-proc');

        /**
         * initialize server variables
         */
        $this->appcfgs = (new Object)->import((array) include $this->svConfig->settings);
        $this->globals = (new Object);
        $this->clients = (new Object);
        $this->cmdSets = (new Object);
        $this->taskMap = (new Object);
        $this->pipeSet = (new Object);

        /**
         * initialize application
         */
        $this->appcfgs->initial();

        /**
         * swoole-worker-process-only
         */
        if ($w >= $this->svConfig->get('worker-num')) {
            $this->taskSet = (new Object);
            /**
             * populate application tasks
             */
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->appcfgs->tasks)) as $o) {
                if ($o->isdir()) {
                    continue;
                } else if (false === $o->isReadable()) {
                    $this->logmessage(self::LOG_WARNING, 'invalid task "%s"', $o->getPathname());
                    continue;
                } else if (false === is_callable(($proc = include $o->getPathname()))) {
                    $this->logmessage(self::LOG_WARNING, 'invalid task "%s"', $o->getPathname());
                    continue;
                }

                try {
                    $pathname = trim(substr($o->getPathname(), strlen($this->appcfgs->tasks)), DIRECTORY_SEPARATOR);
                    $pathname = str_replace(DIRECTORY_SEPARATOR, '-', $pathname);
                    list($name, $code) = explode('.', $pathname);
                } catch (Exception $e) {
                    $this->logmessage(self::LOG_WARNING, 'invalid task %s', $o->getPathname());
                    continue;
                }

                $this->populateTask($name, $code, $proc);
            }
            return;
        }

        /**
         * build task map
         */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->appcfgs->tasks)) as $o) {
            if ($o->isdir()) {
                continue;
            } else if (false === $o->isReadable()) {
                $this->logmessage(self::LOG_WARNING, 'invalid task "%s"', $o->getPathname());
                continue;
            } else if (false === is_callable(($proc = include $o->getPathname()))) {
                $this->logmessage(self::LOG_WARNING, 'invalid task "%s"', $o->getPathname());
                continue;
            }

            try {
                $pathname = trim(substr($o->getPathname(), strlen($this->appcfgs->tasks)), DIRECTORY_SEPARATOR);
                $pathname = str_replace(DIRECTORY_SEPARATOR, '-', $pathname);
                list($name, $code) = explode('.', $pathname);
            } catch (Exception $e) {
                $this->logmessage(self::LOG_WARNING, 'invalid task %s', $o->getPathname());
                continue;
            }

            $this->taskMap->set($name, $code);
        }

        /**
         * populate application pipeMessage handlers
         */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->appcfgs->pipes)) as $o) {
            if ($o->isdir()) {
                continue;
            } else if (false === $o->isReadable()) {
                $this->logmessage(self::LOG_WARNING, 'invalid pipe "%s"', $o->getPathname());
                continue;
            } else if (false === is_callable(($proc = include $o->getPathname()))) {
                $this->logmessage(self::LOG_WARNING, 'invalid pipe "%s"', $o->getPathname());
                continue;
            }

            try {
                $pathname = trim(substr($o->getPathname(), strlen($this->appcfgs->pipes)), DIRECTORY_SEPARATOR);
                $pathname = str_replace(DIRECTORY_SEPARATOR, '-', $pathname);
                list($name, $code) = explode('.', $pathname);
            } catch (Exception $e) {
                $this->logmessage(self::LOG_WARNING, 'invalid task %s', $o->getPathname());
                continue;
            }

            $this->populatePipe($name, $code, $proc);
        }

        /**
         * populate reserved commands
         */
        $this->populateCommand('sys.ctl.stop', 'a', 1000, function(Client $c) {
            if ($this->stop()) {
                $c->flags->bitset(Client::FLAG_CLOSING);
            }
        }, $this);
        $this->populateCommand('sys.ctl.restart', 'a', 1001, function(Client $c) {
            if ($this->restart()) {
                $c->flags->bitset(Client::FLAG_CLOSING);
            }
        }, $this);

        /**
         * populate application commands
         */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->appcfgs->commands)) as $o) {
            if ($o->isdir()) {
                continue;
            } else if (false === $o->isReadable()) {
                $this->logmessage(self::LOG_WARNING, 'invalid command "%s"', $o->getPathname());
                continue;
            } else if (false === is_callable(($proc = include $o->getPathname()))) {
                $this->logmessage(self::LOG_WARNING, 'invalid command "%s"', $o->getPathname());
                continue;
            }

            try {
                list($alias, $flags, $codes) = explode('.', $o->getPathname());
            } catch (Exception $e) {
                $this->logmessage(self::LOG_WARNING, 'invalid command "%s"', $o->getPathname());
                continue;
            }

            $this->populateCommand(str_replace([
                $this->appcfgs->commands,
                DIRECTORY_SEPARATOR,
            ], [
                'app',
                '.',
            ], $alias), $flags, explode('-', $codes), $proc);
        }

        /**
         * reload previous clients into current process
         */
        $this->reloadClients();
    }

    /**
     * @SwooleCallback workerstop
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $w
     * @return void
     */
    public function onSwooleWorkerShutdown(PhpSwooleServer $swServer, $w)
    {
        /**
         * cleanup application
         */
        $this->appcfgs->cleanup();

        /**
         * swoole-worker-process-only
         */
        if ($w >= $this->svConfig->get('worker-num')) {
            return;
        }

        /**
         * backup clients in this process
         */
        $this->backupClients();

        /**
         * dump server info
         */
        $this->exportServerStatistics();
    }

    /**
     * @SwooleCallback connect
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $s
     * @param  integer         $r
     * @return void
     */
    public function onSwooleConnect(PhpSwooleServer $swServer, $s, $r)
    {
        if (false === ($o = $swServer->connection_info($s, -1, false, true))) {
            /* debugging */ $this->logmessage(self::LOG_NOTICE, 'initialize client failed');
            return;
        }

        $c = new Client;
        $c->sock->intval($o->sock);
        $c->host->strval($o->host);
        $c->port->intval($o->port);
        $c->from->intval($o->from);
        $c->work->intval($r);

        if ($o->from == $this->svConfig->monitor) {
            $c->flags->bitset(Client::FLAG_MONITOR);
        }

        /**
         * application connect callback
         */
        $this->appcfgs->connect($c, $r);

        /**
         * cache into this process
         */
        $this->clients->set($s, $c);

        /* debugging */ $this->logmessage(self::LOG_VERBOSE, 'accepted [%d] %s:%d', $o->sock, $o->host, $o->port);
    }

    /**
     * @SwooleCallback close
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $s
     * @param  integer         $r
     * @return void
     */
    public function onSwooleClose(PhpSwooleServer $swServer, $s, $r)
    {
        if (null === ($c = $this->clients->get($s))) {
            return;
        }

        /**
         * application close callback
         */
        $this->globals->setTime(ustime());
        $this->appcfgs->close($c);

        /**
         * remove from this process
         */
        unset($this->clients->$s);

        /* debugging */ $this->logmessage(self::LOG_VERBOSE, 'closed [%d] %s:%d', $c->sock->intval(), $c->host->strval(), $c->port->intval());
    }

    /**
     * @SwooleCallback receive
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $s
     * @param  integer         $r
     * @param  string          $q
     * @return void
     */
    public function onSwooleReceive(PhpSwooleServer $swServer, $s, $r, $q)
    {
        if (null === ($c = $this->clients->get($s))) {
            $swServer->close($s);

            /* debugging */ $this->logmessage(self::LOG_NOTICE, 'missing client cache');
            return;
        } else if ($c->sock->intval() <> $s) {
            $swServer->close($c->sock->intval());
            $swServer->close($s);

            /* debugging */ $this->logmessage(self::LOG_NOTICE, 'unmatch client cache');
            return;
        } else if ($c->flags->bithas(Client::FLAG_CLOSING)) {
            $swServer->close($s);
            return;
        }

        // merging previous query buffer
        $c->query->concat($q);

        // keep processing while there is something in the input buffer
        while ($c->query->strlen()) {
            if (($retval = $c->processInputBuffer($this->bulkmaxsize)) == -1) {
                /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s protocol error', $c->toString());

                // invalid query
                $c->flags->bitset(Client::FLAG_CLOSING);
                break;
            } else if ($retval) {
                // waiting for '$retval' bytes more
                break;
            }

            // lookup command
            if (null === ($o = $this->cmdSets->get($c->qhead->command->intval()))) {
                /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s not found', $c->toString());

                $c->flags->bitset(Client::FLAG_CLOSING);
                break;
            }

            // checking admin-command
            if ((boolean) ($o->flags & self::CMD_ADMIN) <> (boolean) $c->flags->bithas(Client::FLAG_MONITOR)) {
                /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s forbidded', $c->toString());

                $c->flags->bitset(Client::FLAG_CLOSING);
                break;
            }

            // checking login-command
            if (($o->flags & self::CMD_IGNORE_LOGIN) == 0 &&
                ((boolean) ($o->flags & self::CMD_LOGIN) <> (boolean) $c->login->intval())) {
                /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s unauthorized', $c->toString());

                $c->flags->bitset(Client::FLAG_CLOSING);
                break;
            }

            // update server time cache
            $this->globals->setTime(ustime());

            // update variables for command-processing
            $this->globals->currcmd = $o;

            /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s processing command',$c->toString());

            try {
                // application-request-startup-hook
                $this->appcfgs->requestStartup($c, $o);

                // processing command
                $o->proc($c);

                // application-request-shutdown-hook
                $this->appcfgs->requestShutdown($c, $o);
            } catch (Exception $e) {
                if ($this->svConfig->verbose == static::LOG_DEBUG) {
                    file_put_contents('/tmp/zhanhd.exceptions.log', print_r(array(
                        'ustime' => ustime(),
                        'c' => $c ? $c->toString() : '',
                        'e' => $e,
                    ), true));
                }

                /* debugging */ $this->logmessage(self::LOG_WARNING, '%s exception=%s file=%s line=%d', $c->toString(),
                    $e->getMessage(), $e->getFile(), $e->getLine()
                );

                $c->flags->bitset(Client::FLAG_CLOSING);
                break;
            }

            /* debugging */ $this->logmessage(self::LOG_VERBOSE, '%s sending response',$c->toString());

            // sending response
            foreach ($c->reply as $r) {
                $swServer->send($s, $r->encode());
            }

            // time elapsed of processing command and sending response
            $duration = ustime() - $this->globals->ustime;

            // update command statistics
            $this->updateCommandStatistics($o, $duration);

            // slowlog
            if ($this->slowcmdproc > 0 && $this->slowcmdproc < $duration) {
                $this->logmessage(self::LOG_VERBOSE, '%s duration=%d', $c->toString(), $duration);
            }

            // don't process more commands if the connection is going to be closed
            if ($c->flags->bithas(Client::FLAG_CLOSING)) {
                break;
            }

            // reset client if everything goes well
            $c->reset();
        }

        // close connection if needed
        if ($c->flags->bithas(Client::FLAG_CLOSING)) {
            $swServer->close($s);
        }
    }

    /**
     * @SwooleCallback timer
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $t
     * @return void
     */
    public function onSwooleTimer(PhpSwooleServer $swServer, $t)
    {}

    /**
     * @SwooleCallback task
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $t
     * @param  integer         $w
     * @param  string          $j
     * @return void
     */
    public function onSwooleJobinit(PhpSwooleServer $swServer, $t, $w, $j)
    {
        $this->globals->setTime(ustime());
        $t = new Task;
        $t->decode($j);
        $o = $this->taskSet->get($t->code->intval());
        $this->globals->currtask = $o;
        $o->proc(substr($j, 2));
    }

    /**
     * @SwooleCallback finish
     *
     * @param  PhpSwooleServer $swServer
     * @param  integer         $t
     * @param  string          $r
     * @return void
     */
    public function onSwooleJobdone(PhpSwooleServer $swServer, $t, $r)
    {}

    /**
     * @SwooleCallback pipeMessage
     *
     * @param PhpSwooleServer $swServer
     * @param integer         $r
     * @param string          $j
     * @return void
     */
    public function onSwoolePipeMessage(PhpSwooleServer $swServer, $r, $j)
    {
        $this->globals->setTime(ustime());
        $p = new Pipe;
        $p->decode($j);
        $c = $this->globals->getClient($p->pid->intval());
        if ($c === null) return;
        $data = substr($j, 8);

        $h = new ReqResHeader;
        $h->decode($data);
        $o = $this->pipeSet->get($h->command->intval());
        $this->globals->currpipe = $o;
        $o->proc($c, $data);
    }

    /**
     *
     * @return void
     */
    private function backupClients()
    {
        if ($this->clients->count() == 0) {
            if (false === file_exists($this->clientcache)) {
                return;
            }

            unlink($this->clientcache);
            return;
        }

        if (false === ($fp = fopen($this->clientcache, 'wb'))) {
            return;
        }

        foreach ($this->clients as $c) {
            fwrite($fp, $c->encode());
        }

        fclose($fp);
    }

    /**
     *
     * @return void
     */
    private function reloadClients()
    {
        if (false === file_exists($this->clientcache) || false === ($fp = fopen($this->clientcache, 'rb'))) {
            return;
        }

        $buffer = '';
        while (($packed = fread($fp, 1024))) {
            if ($buffer) {
                $packed = $buffer . $packed;
                $buffer = '';
            }

            for ($offset = 0, $length = strlen($packed); $offset < $length; $offset += $c->length()) {
                try {
                    $c = new Client;

                    // maybe setup client-binding
                    $this->appcfgs->connect($c);

                    // now we can recover this client
                    $c->decode($packed, $offset);

                    $this->clients->set($c->sock->intval(), $c);
                } catch (Exception $e) {
                    $buffer = substr($packed, $offset);
                    break;
                }
            }
        }
    }

    /**
     * @var integer
     */
    const LOG_DEBUG   = 0;
    const LOG_VERBOSE = 1;
    const LOG_NOTICE  = 2;
    const LOG_WARNING = 3;

    /**
     *
     * @return void
     */
    private function logmessage($lvl, $fmt, ... $argv)
    {
        static $char = '+-$#';

        if ($lvl < $this->svConfig->verbose) {
            return;
        }

        if (false === ($fp = fopen($this->svConfig->logfile ?: 'php://stdout', 'a'))) {
            return;
        }

        fprintf($fp, "%d %s %s $fmt\n", $this->swServer->worker_id,
            usdate(), $char{$lvl},
            ... $argv
        );

        fflush($fp);
        if ($fp) {
            fclose($fp);
        }
    }

    /**
     * @var integer
     */
    const CMD_WRITE           = 1;
    const CMD_ADMIN           = 2;
    const CMD_LOGIN           = 4;
    const CMD_IGNORE_LOGIN    = 8;
    const CMD_IGNORE_LAST_CMD = 16;

    /**
     *
     * @param  string        $name
     * @param  string        $flags
     * @param  integer|array $codes
     * @param  callable      $proc
     * @param  object|null   $binding
     * @return void
     */
    private function populateCommand($name, $flags, $codes, callable $proc, $binding = null)
    {
        $iflags = 0;
        $sflags = [];
        for ($i = 0, $j = strlen($flags); $i < $j; $i++) {
            switch ($flags{$i}) {
            case 'w': $iflags |= self::CMD_WRITE;           $sflags['w'] = 1; break;
            case 'a': $iflags |= self::CMD_ADMIN;           $sflags['a'] = 1; break;
            case 'l': $iflags |= self::CMD_LOGIN;           $sflags['l'] = 1; break;
            case 'L': $iflags |= self::CMD_IGNORE_LOGIN;    $sflags['L'] = 1; break;
            case 'R': $iflags |= self::CMD_IGNORE_LAST_CMD; $sflags['R'] = 1; break;
            }
        }

        foreach ((array) $codes as $code) {
            if (($o = $this->cmdSets->get($code))) {
                $this->logmessage(self::LOG_WARNING, 'duplicate command "%s" coded %d, already registered to "%s"',
                    $name,
                    $code,
                    $o->name
                );
            } else {
                $this->cmdSets->set($code, array(
                    'proc' => $proc->bindTo($binding ?: $this->globals),
                    'name' => $name,
                    'code' => $code,

                    'sflag' => implode(array_keys($sflags)),
                    'flags' => $iflags,
                    'calls' => 0,
                    'msecs' => 0,
                ));
            }
        }
    }

    /**
     * @param string  $name
     * @param integer $code
     * @param callable $proc
     * @return void
     */
    private function populateTask($name, $code, callable $proc)
    {
        if ($o = $this->taskSet->get($code)) {
            $this->logmessage(self::LOG_WARNING, 'duplicate task "%s" coded %d, already registered to "%s"',
                $name,
                $code,
                $o->name
            );
            return;
        }

        $this->taskSet->set($code, [
            'proc' => $proc->bindTo($this->globals),
            'name' => $name,
            'code' => $code,
        ]);
    }

    /**
     * @param string  $name
     * @param integer $code
     * @param callable $proc
     * @return void
     */
    private function populatePipe($name, $code, callable $proc)
    {
        if ($o = $this->pipeSet->get($code)) {
            $this->logmessage(self::LOG_WARNING, 'duplicate pipe "%s" coded %d, already registered to "%s"',
                $name,
                $code,
                $o->name
            );
            return;
        }

        $this->pipeSet->set($code, [
            'proc' => $proc->bindTo($this->globals),
            'name' => $name,
            'code' => $code,
        ]);
    }

    /**
     *
     * @param  Object  $c
     * @param  integer $duration
     * @return void
     */
    private function updateCommandStatistics(Object $c, $duration)
    {
        $c->calls ++;
        $c->msecs += $duration;
    }

    /**
     *
     * @param  string|null $section
     * @return void
     */
    private function exportServerStatistics($section = null)
    {
        $summary = 'Server Statistics';
        $allsecs = false;
        $uptimes = ustime() - $this->startustime;

        if ($section === null) $section = 'all';
        if ($section == 'all') $allsecs = true;

        /* Server */
        if ($allsecs || $section == 'server') {
            $summary .= "\n# Server\n";
            $summary .= sprintf("client-nums: %d\n", $this->clients->count());
            $summary .= sprintf("uptime-secs: %d\n", $uptimes/1000000);
            $summary .= sprintf("uptime-days: %d\n", $uptimes/1000000/86400);
        }

        /* Memory */
        if ($allsecs || $section == 'memory') {
            $summary .= "\n# Memory\n";
            $summary .= sprintf("memory-used: %0.2fk\n", memory_get_usage()/1024);
            $summary .= sprintf("memory-peak: %0.2fk\n", memory_get_peak_usage()/1024);
        }

        /* Commands */
        if ($allsecs || $section == 'action') {
            $summary .= "\n# Commands\n";
            foreach ($this->cmdSets as $o) {
                if ($o->calls == 0) {
                    continue;
                }

                $summary .= sprintf("%s@%d: num=%d,sum=%d,avg=%0.3f\n", $o->name, $o->code,
                    $o->calls,
                    $o->msecs,
                    $o->msecs/$o->calls
                );
            }
        }

        $this->logmessage(self::LOG_WARNING, $summary);
    }

    /**
     * @param callable $callback
     * @param mix $extra
     * @param array $filters
     * @return void
     */
    public function scanFds(callable $callback, $extra = null, array $filters = null)
    {
        $fd = 0;
        while ($list = $this->swServer->connection_list($fd, 20)) {
            foreach ($list as $fd) {
                if ($filters && isset($filters[$fd])) {
                    continue;
                }
                $callback($this, $fd, $extra);
            }
        }
    }
}

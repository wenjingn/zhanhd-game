<?php
/**
 * $Id$
 */

/**
 *
 */
class Sync
{
    /**
     * @const integer
     */
    const AUTH_PASSWORD = 1;
    const AUTH_PUBKEY   = 2;
    
    /**
     * @var array
     */
    protected $servers = null;

    /**
     * @param array $argvs
     * @return void
     */
    public function __construct($argvs)
    {
        $this->servers = include $argvs['servers'];
        $this->version = $argvs['version'];
        $this->upcache = $argvs['upcache'];
    }

    /**
     * @var array
     */
    protected $outputs = [];

    /**
     * @return void
     */
    public function exec()
    {
        $date = date('Ymd');
        $tmpdir = sprintf('/tmp/php/%s', $date);
        $exec = sprintf('rm -rf %s && mkdir -p %s', $tmpdir, $tmpdir);
        exec($exec);
        if ($this->version == 'test') {
            exec(sprintf('/usr/bin/svn export /data/php/games/system %s/system', $tmpdir));
            exec(sprintf('/usr/bin/svn export /data/php/games/zhanhd %s/zhanhd', $tmpdir));
        } else {
            exec(sprintf('/usr/bin/svn export /home/jing/games/system/tags/release-%s %s/system', $this->version, $tmpdir));
            exec(sprintf('/usr/bin/svn export /home/jing/games/zhanhd/tags/release-%s %s/zhanhd', $this->version, $tmpdir));
        }
        
        if ($this->upcache) {
            exec(sprintf('cp /data/php/games/zhanhd/cache/* %s/zhanhd/cache/', $tmpdir));
        }

        foreach ($this->servers as $server) {
            $output = [];
            switch ($server['auth']) {
            case self::AUTH_PASSWORD:
                $login = sprintf('sshpass -p %s ssh -l %s', $server['pass'], $server['user']);
                $ssh   = sprintf("sshpass -p '%s' ssh %s@%s", $server['pass'], $server['user'], $server['host']);
                break;
            case self::AUTH_PUBKEY:
                $login = sprintf('ssh -l %s', $server['user']);
                $ssh   = sprintf('ssh %s@%s', $server['user'], $server['host']);
                break;
            }

            $exec = sprintf("/usr/bin/rsync -avz -e '%s' /tmp/php/%s %s:/home/ubuntu/php", $login, date('Ymd'), $server['host']);
            printf("%s\n", $exec);
            exec($exec, $output);

            $cmd = 'rm /data/php/games';
            exec(sprintf("%s '%s'", $ssh, $cmd));
            $cmd = sprintf('ln -s /home/ubuntu/php/%d /data/php/games', $date);
            exec(sprintf("%s '%s'", $ssh, $cmd));

            foreach ($server['zones'] as $zoneid => $zone) {
                $cmd = '/usr/local/php/bin/php /data/php/games/zhanhd/tests/monitor/restart.php --appdir=/data/php/games/zhanhd --port='.$zone['monitor'];
                var_dump(sprintf("%s '%s'", $ssh, $cmd));
                exec(sprintf("%s '%s'", $ssh, $cmd));
            }
            $this->log($server, $output);
        }
    }

    /**
     * @return void
     */
    protected function log($server, $output)
    {
        printf("rsync code to host:%s\n", $server['host']);
        print_r($output);
    }
}

/**
 *
 */
$argvs = getlongopt([
    'servers' => false,
    'version' => false,
    'upcache' => false,
]);
$sync = new Sync($argvs);
$sync->exec();

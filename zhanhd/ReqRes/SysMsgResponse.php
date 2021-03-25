<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
    System\ReqRes\Str,
    System\ReqRes\Set;

/**
 *
 */
class SysMsgResponse extends ReqResHeader
{
    /**
     * @param int    $id
     * @param int    $priority
     * @param array  $body
     * @return void
     */
    public function format($id, $priority, array $body = array())
    {
        $this->id      ->intval($id);
        $this->priority->intval($priority);

        $i = 0;
        $this->body->resize(count($body));
        foreach ($body as $value) {
            $color = 'green';
            if (is_array($value)) {
                switch (count($value)) {
                case 0:
                    $value = '';
                    break;

                case 1:
                    $value = array_shift($value);
                    break;

                default:
                    list($value, $color) = array_values($value);
                    break;
                }
            }

            $this->body->get($i)->strval(sprintf('%s:%s', $color, $value));
            $i++;
        }
    }

    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(219);

        $this->attach('id',       new U16);
        $this->attach('priority', new U16);

        $this->attach('body',     new Set(new Str));
    }
}

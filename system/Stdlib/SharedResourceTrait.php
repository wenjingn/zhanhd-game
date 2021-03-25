<?php
/**
 * $Id$
 */

 /**
  *
  */
namespace System\Stdlib;

/**
 *
 */
use InvalidArgumentException;

/**
 *
 */
trait SharedResourceTrait
{
    /**
     * @var Object
     */
    private static $sharedResources = null;

    /**
     *
     * @param  Object $object
     * @param  mixed  $scope
     * @return void
     */
    public static function registerResource(Object $object, $scope = null)
    {
        if (self::$sharedResources === null) {
            self::$sharedResources = new Object;
        }

        self::$sharedResources->set($scope, $object);
    }

    /**
     *
     * @param  mixed $name
     * @param  mixed $scope
     * @return mixed
     * @throws Invalid
     */
    protected function retrieveResource($name, $scope = null)
    {
        if (self::$sharedResources === null) {
            self::$sharedResources = new Object;
            goto notfound;
        }

        if ($scope) {
            if (($object = self::$sharedResources->get($scope))) {
                return $object->get($name);
            }

            goto notfound;
        }

        foreach (self::$sharedResources as $object) {
            if (($o = $object->get($name))) {
                return $o;
            }
        }

        notfound: throw new InvalidArgumentException(sprintf('Resource named "%s" does not exist in scope(%s)',
            $name,
            $scope ?: 'null'
        ));
    }

    /**
     * @param string $scope
     * @return mixed
     */
    protected function retrieveScope($scope)
    {
        return self::$sharedResources->get($scope);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Exception;

/**
 *
 */
class User extends DatabaseObject
{
    /**
     * @const integer
     */
    const PF_ZHANHD  = 1;
    const PF_BAIDU   = 2;
    const PF_QQ      = 3;
    const PF_WECHAT  = 4;
    const PF_AY      = 5;
    const PF_LEZHUO  = 6;

    /**
     * @var UserProfile
     */
    public $profile = null;

    /**
     * @var string
     */
    public $rawpswd = null;

    /**
     * @var integer
     */
    const FLAG_NORMAL = 0;
    const FLAG_BANNED = 1;

    /**
     * @var SecretContainer
     */
    private $secretContainer = null;

    /**
     * @return boolean
     */
    public function belongTencent()
    {
        return $this->platform == self::PF_QQ || $this->platform == self::PF_WECHAT;
    }

    /**
     * @param  string $platform
     * @param  string $login
     * @return boolean
     */
    public static function loginExists(PhpPdo $pdo, $platform, $login)
    {
        $stmt = $pdo->prepare(sprintf('SELECT COUNT(*) FROM `zhanhd.global`.`User` WHERE `platform` = ? AND `login` = ?'));
        $stmt->execute(array($platform, $login));

        return (boolean) $stmt->fetchColumn();
    }

    /**
     * @param  string $platform
     * @param  string $login
     * @return boolean
     */
    public function findByLogin($platform, $login)
    {
        return $this->findBySql(sprintf('SELECT %s FROM %s WHERE `platform` = ? AND `login` = ? LIMIT 1',
            $this->getSelectColumns(),
            $this->schema()
        ), array(
            $platform,
            $login,
        ));
    }

    /**
     *
     * @return boolean
     */
    public function validateStatus()
    {
        switch ($this->flags) {
        case self::FLAG_BANNED: return false;
        case self::FLAG_NORMAL: return true;
        }

        return false;
    }

    /**
     * @when auto-signin $accessToken pass boolean false
     * @param string|boolean $accessToken
     * @param string $pf
     * @param string $pfkey
     * @param string $anotherToken
     * @return void
     */
    public function loginSuccess($accessToken = null, $pf = null, $pfkey = null, $anotherToken = null)
    {
        if ($accessToken === false) {
            $this->lastLogin = $this->ustime;
            $this->save();
            return;
        }
        switch ($this->platform) {
        case self::PF_BAIDU:
        case self::PF_AY:
            $this->passwd = $accessToken;
            break;
        case self::PF_QQ:
            $this->passwd = $accessToken;
            $this->profile->pf = $pf;
            $this->profile->pfkey = $pfkey;
            $this->profile->payToken = $anotherToken;
            break;
        case self::PF_WECHAT:
            $this->passwd = $accessToken;
            $this->profile->pf = $pf;
            $this->profile->pfkey = $pfkey;
            $this->profile->refreshToken = $anotherToken;
            break;
        }

        $this->lastLogin = $this->ustime;
        $this->save();
    }

    /**
     *
     * @return string
     */
    public function createSecret()
    {
        $this->secretContainer->ll->intval($this->lastLogin);
        $this->secretContainer->ph->strval(md5($this->passwd));
        $this->secretContainer->id->intval($this->id);
        $this->secretContainer->ih->strval(md5($this->id));
        $this->secretContainer->ht->strval(md5(sprintf('%s.%s.%s.%s',
            $this->secretContainer->ll->intval(),
            $this->secretContainer->ph->strval(),
            $this->secretContainer->id->intval(),
            $this->secretContainer->ih->strval()
        )));

        return base64_encode($this->secretContainer->encode());
    }

    /**
     *
     * @param  string $secret
     * @return boolean
     */
    public function decodeSecret($secret)
    {
        try {
            $this->secretContainer->decode(base64_decode($secret));
        } catch (Exception $e) { return false; }

        // hash
        if ($this->secretContainer->ht->strval() <> md5(sprintf('%s.%s.%s.%s',
                $this->secretContainer->ll->intval(),
                $this->secretContainer->ph->strval(),
                $this->secretContainer->id->intval(),
                $this->secretContainer->ih->strval()
            ))) {
            return false;
        }

        // hash id
        if (md5($this->secretContainer->id->intval()) <> $this->secretContainer->ih->strval()) {
            return false;
        }

        // find user
        if (false === $this->find($this->secretContainer->id->intval())) {
            return false;
        }

        // hash passwd
        if ($this->secretContainer->ph->strval() <> md5($this->passwd)) {
            return false;
        }

        // last-login
        if ($this->secretContainer->ll->intval() <> $this->lastLogin) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.global`.`User`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'id'        => null,
            'platform'  => '',
            'login'     => '',
            'passwd'    => '',
            'email'     => '',
            'flags'     => 0,
            'created'   => 0,
            'lastLogin' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'id' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->secretContainer = new User\SecretContainer;

        $this->profile = new User\Profile;
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->profile->setUserId($this->id);
        $this->profile->find();
    }

    /**
     *
     * @return void
     */
    protected function preInsert()
    {
        if ($this->platform == self::PF_ZHANHD && false === empty($this->rawpswd)) {
            $this->passwd = password_hash($this->rawpswd, PASSWORD_BCRYPT, array(
                'cost' => 11,
                'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
            ));
        }
        $this->created   = $this->ustime;
    }

    /**
     *
     * @return void
     */
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();

        $this->profile->setUserId($this->id);
        $this->profile->save();
    }

    /**
     *
     * @return void
     */
    protected function postUpdate()
    {
        $this->profile->save();
    }

    /**
     *
     * @return void
     */
    protected function preDelete()
    {
        $this->profile->drop();
    }
}

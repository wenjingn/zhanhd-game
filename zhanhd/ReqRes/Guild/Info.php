<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo,
    Zhanhd\Task\Guild\Request  as TaskGuildRequest,
    Zhanhd\Object\Guild,
    Zhanhd\Object\Guild\Member as GuildMember,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Coherence,
    Zhanhd\Config\Store,
    Zhanhd\Object\Message;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U64);
        $this->attach('exp', new U32);
        $this->attach('lvl', new U16);
        $this->attach('memnum', new U32);
        $this->attach('name',   new Str);
        $this->attach('presidentId',    new U64);
        $this->attach('presidentLeader',new LeaderInfo);
        $this->attach('viceChairmanId', new U64);
        $this->attach('bulletin',       new Str);

        $this->attach('ipost',             new U16);
        $this->attach('impeachNum',        new U16);
        $this->attach('CONFIG_IMPEACHNUM', new U16);
        $this->attach('impeached',         new U16);
        $this->attach('canContribute',     new U16);
        $this->attach('contribution',      new U32);
        $this->attach('acceptedGifts',     new Set(new U16));
        $this->attach('chestAccepted',     new U16);
    }

    /**
     * @param Guild       $guild
     * @param GuildMember $guildMember
     * @param Global      $g
     * @return void
     */
    public function fromObject(Guild $guild, GuildMember $guildMember, $g = null)
    {
        $this->gid->intval($guild->id);
        $this->exp->intval($guild->exp);
        $this->lvl->intval($guild->lvl);
        $this->memnum->intval($guild->memnum);
        $this->name->strval($guild->name);
        $president = $guild->getPresident();
        $viceChairman = $guild->getViceChairman();
        $this->presidentId->intval($president->pid);
        $this->presidentLeader->fromPlayerObject($president->player);
        $this->viceChairmanId->intval($viceChairman ? $viceChairman->pid : 0);
        $this->bulletin->strval($guild->bulletin);

        $post = $guildMember->getPost();
        $this->ipost->intval($post);
        if ($post != GuildMember::POST_GREENER) {
            $impeach = $guild->getImpeach();
            if ($impeach) {
                if ($guildMember->isPresident()) {
                    $impeach->drop();
                    $m = new Message;
                    $m->gid = $guildMember->gid;
                    $m->tag = Message::TAG_GUILD_IMPEACHFAIL;
                    $m->save();
                    $impeachNotify = new Impeach\Response;
                    if ($g) {
                        $taskReq = new TaskGuildRequest;
                        $taskReq->setup($guild->id, $impeachNotify);
                        $g->task('broadcast-guild', $taskReq);
                    }
                } else {
                    $this->impeachNum->intval($impeach->members->count());
                    $this->CONFIG_IMPEACHNUM->intval(Guild::IMPEACHNUM);
                }
            }
        }

        $this->canContribute->intval($guildMember->daily->contribution ? 0 : 1);
        $this->contribution->intval($guildMember->cont - $guildMember->contused);
        
        $gifts = Store::get('guildGift');
        $accepted = [];
        $giftAccepted = $guildMember->player->profile->guildGiftAccepted;
        foreach ($gifts as $o) {
            $bit = 1 << ($o->lvl-1);
            if ($giftAccepted&$bit) {
                $accepted[] = $o;
            }
        }
        $this->acceptedGifts->resize(count($accepted));
        foreach ($this->acceptedGifts as $i => $o) {
            $o->intval($accepted[$i]->id);
        }

        $this->chestAccepted->intval((boolean)$guildMember->player->counterWeekly->guildChest);
    }
}

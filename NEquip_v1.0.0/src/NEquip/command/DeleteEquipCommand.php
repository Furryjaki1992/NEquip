<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use NEquip\NEquip;

class DeleteEquipCommand extends Command
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	/** @var string */
	public const DELETE_EQUIPCOMMAND_PERMISSION = "op";
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비삭제", "장비삭제 명령어 입니다.");
		$this->setPermission (self::DELETE_EQUIPCOMMAND_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player->hasPermission (self::DELETE_EQUIPCOMMAND_PERMISSION)) {
			if (isset ($args [0])) {
				if ($this->plugin->isEquip ($args [0])) {
					$this->plugin->deleteEquip ($args [0]);
					NEquip::message ($player, "[ {$args [0]} ] 장비를 삭제하셨습니다.");
					
					foreach ($this->plugin->getServer ()->getOnlinePlayers () as $players)
						NEquip::message ($players, "관리자 §a{$player->getName ()}님§7에 의해 [ §a{$args [0]}§7 ] 장비가 삭제되었습니다.");
				} else {
					NEquip::message ($player, "해당 이름의 장비는 존재하지 않습니다.");
				}
			} else {
				NEquip::message ($player, "/장비삭제 (장비명)");
			}
		} else {
			NEquip::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
		}
		return true;
	}
}
<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use NEquip\NEquip;

class AddEquipCommand extends Command
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	/** @var string */
	public const ADD_EQUIPCOMMAND_PERMISSION = "op";
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비추가", "장비추가 명령어 입니다.");
		$this->setPermission (self::ADD_EQUIPCOMMAND_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::ADD_EQUIPCOMMAND_PERMISSION)) {
				$item = $player->getInventory ()->getItemInHand ();
				if ($item->isNull ()) {
					NEquip::message ($player, "손에 아이템을 들고 명령어를 사용해주세요.");
					return true;
				}
				if (isset ($args [0]) and isset ($args [1]) and isset ($args [2]) and is_numeric ($args [2]) and isset ($args [3]) and is_numeric ($args [3]) and isset ($args [4])) {
					if (!$this->plugin->isEquip ($args [0])) {
						$this->plugin->addEquip ($args [0], $args [1], $args [2], $args [3], $args [4], $item);
						NEquip::message ($player, "[ {$args [0]} ] 장비를 추가하셨습니다.");
					} else {
						NEquip::message ($player, "해당 이름의 장비는 이미 존재합니다.");
					}
				} else {
					NEquip::message ($player, "/장비추가 (장비명) (장비 등급) (레벨제한) (확률 0.1 ~ 100) (체력:공격:방어:민첩:흡혈:크리티컬)");
				}
			} else {
				NEquip::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
			}
		} else {
			NEquip::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
}
<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use NEquip\NEquip;

class GetEquipCommand extends Command
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	/** @var string */
	public const GET_EQUIPCOMMAND_PERMISSION = "op";
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비얻기", "장비얻기 명령어 입니다.");
		$this->setPermission (self::GET_EQUIPCOMMAND_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::GET_EQUIPCOMMAND_PERMISSION)) {
				if (isset ($args [0])) {
					if ($this->plugin->isEquip ($args [0])) {
						$class = NEquip::getEquip ($args [0]);
						$player->getInventory ()->addItem ($class->getDefaultEquipItem ());
						NEquip::message ($player, "인벤토리를 확인해주세요.");
					} else {
						NEquip::message ($player, "해당 장비는 존재하지 않습니다.");
					}
				} else {
					NEquip::message ($player, "/장비얻기 (장비 이름)");
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
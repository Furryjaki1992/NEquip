<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

use NEquip\NEquip;

class UpgradeEquipCommand extends Command
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	/** @var string */
	public const UPGRADE_EQUIPCOMMAND_PERMISSION = "user";
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비강화", "장비강화 명령어 입니다.");
		$this->setPermission (self::UPGRADE_EQUIPCOMMAND_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			$item = $player->getInventory ()->getItemInHand ();
			if ($item->isNull ()) {
				NEquip::message ($player, "손에 장비를 들고 명령어를 실행해주세요.");
				return true;
			}
			if (!is_null ($item->getNamedTagEntry ("equip"))) {
				$equip = $item->getNamedTagEntry ("equip")->getValue ();
				if ($this->plugin->isEquip ($equip)) {
					$class = NEquip::getEquip ($equip);
					$upgrade = $this->plugin->getEquipUpgrade ($item);
					$iitem = Item::get (409, 1, 20);
					if ($player->getInventory ()->contains ($iitem)) {
						if (EconomyAPI::getInstance ()->myMoney ($player) >= $class->getUpgradePrice ()) {
							EconomyAPI::getInstance ()->reduceMoney ($player, $class->getUpgradePrice ());
							$class->startUpgrade ($player, $item, $upgrade);
							$player->getInventory ()->removeItem ($iitem);
						} else {
							NEquip::message ($player, "강화를 하실려면 §a" . number_format ($class->getUpgradePrice ()) . "원§7이 필요합니다.");
						}
					} else {
						NEquip::message ($player, "강화를 하실려면 강화석 20개가 필요합니다.");
					}
				} else {
					NEquip::message ($player, "손에든 아이템은 장비로 인식이 불가능 합니다. *관리자에게 문의해주세요.*");
				}
			} else {
				NEquip::message ($player, "손에 장비를 들고 명령어를 사용해주세요.");
			}
		} else {
			NEquip::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
}
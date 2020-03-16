<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

use NEquip\NEquip;

class DecomposeEquipCommand extends Command
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	/** @var string */
	public const DECOMPOSE_EQUIPCOMMAND_PERMISSION = "user";
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비분해", "장비분해 명령어 입니다.");
		$this->setPermission (self::DECOMPOSE_EQUIPCOMMAND_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if (isset ($args [0]) and $args [0] === "전체") {
				$count = 0;
				$money = 0;
				foreach ($player->getInventory ()->getContents () as $item) {
					if (!is_null ($item->getNamedTagEntry ("equip"))) {
						$equip = $item->getNamedTagEntry ("equip")->getValue ();
						if ($this->plugin->isEquip ($equip)) {
							$class = NEquip::getEquip ($equip);
							$total = $class->getDefaultTotalStat () + $class->getUpgradeTotalStat ($item);
							$total = $total * 200;
							$player->getInventory ()->removeItem ($item);
							$player->getInventory ()->addItem (Item::get (409, 1, $class->getDecomposeItem ()));
							EconomyAPI::getInstance ()->addMoney ($player, $total);
							$money += $total;
							$count ++;
						}
					}
				}
				NEquip::message ($player, "총 §a{$count}§7 개의 장비를 분해해서 §a{$money}원§7을 획득하셨습니다.");
			} else {
				$item = $player->getInventory ()->getItemInHand ();
				if ($item->isNull ()) {
					NEquip::message ($player, "손에 장비를 들고 명령어를 사용해주세요.");
					return true;
				}
				if (!is_null ($item->getNamedTagEntry ("equip"))) {
					$equip = $item->getNamedTagEntry ("equip")->getValue ();
					if ($this->plugin->isEquip ($equip)) {
						$class = NEquip::getEquip ($equip);
						$total = $class->getDefaultTotalStat () + $class->getUpgradeTotalStat ($item);
						$total = $total * 200;
						$player->getInventory ()->removeItem ($item);
						$citem = $class->getDecomposeItem ();
						$player->getInventory ()->addItem (Item::get (409, 1, $citem));
						EconomyAPI::getInstance ()->addMoney ($player, $total);
						NEquip::message ($player, "[ {$equip} ] 장비를 분해하셔서 {$citem} 개의 강화석, {$total}원 을(를) 획득하셨습니다.");
					} else {
						NEquip::message ($player, "해당 장비는 존재하지 않습니다. *관리자에게 문의해주세요.*");
					}
				} else {
					NEquip::message ($player, "손에 장비를 들고 명령어를 사용해주세요.");
				}
			}
		} else {
			NEquip::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
}
<?php


namespace NEquip\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use NEquip\NEquip;
use pocketmine\nbt\tag\StringTag;

class EquipBoxCommand extends Command implements Listener
{
	
	/** @var null|NEquip */
	protected $plugin = null;
	
	
	public function __construct (NEquip $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("장비조합", "장비조합 명령어 입니다.");
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			$item = $player->getInventory ()->getItemInHand ();
			if ($item->getId () === 0) {
				NEquip::message ($player, "장비조각을 들고 명령어를 실행해주세요.");
				return true;
			}
			$arr = [
				"378:8",
				"378:7",
				"378:6",
				"378:5",
				"378:4",
				"378:3",
				"378:2",
				"378:1"
			];
			if (!in_array ($item->getId () . ":" . $item->getDamage (), $arr) or $item->getCount () < 10) {
				NEquip::message ($player, "장비 조각이 아니거나 10개 이상 손에 들고계셔야 합니다.");
				return true;
			}
			$this->sendMakeBox ($player, $item);
			$player->getInventory ()->setItemInHand ($player->getInventory ()->getItemInHand ()->setCount ($item->getCount () - 10));
			NEquip::message ($player, "인벤토리를 확인해주세요.");
		} else {
			NEquip::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
	
	public function sendMakeBox (Player $player, Item $item): void
	{
		$box = Item::get (399, 15, 1);
		if ($item->getDamage () === 8) {
			$box->setCustomName ("§d레전더리§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "레전더리"));
		} else if ($item->getDamage () === 7) {
			$box->setCustomName ("§6유니크§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "유니크"));
		} else if ($item->getDamage () === 6) {
			$box->setCustomName ("§9레어§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "레어"));
		} else if ($item->getDamage () === 5) {
			$box->setCustomName ("§eS§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "S"));
		} else if ($item->getDamage () === 4) {
			$box->setCustomName ("§cA§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "A"));
		} else if ($item->getDamage () === 3) {
			$box->setCustomName ("§aB§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "B"));
		} else if ($item->getDamage () === 2) {
			$box->setCustomName ("§3C§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "C"));
		} else if ($item->getDamage () === 1) {
			$box->setCustomName ("§7D§f 장비상자");
			$box->setNamedTagEntry (new StringTag ("box", "D"));
		}
		$player->getInventory ()->addItem ($box);
	}
	
	public function onDraw (Player $player, int $damage, string $rate)
	{
		$arr = [];
		foreach ($this->plugin->db ["equip"] as $equip => $data) {
			if ($data ["rating"] === $rate) {
				$arr [] = $equip;
			}
		}
		if (count ($arr) <= 0) {
			NEquip::message ($player, "현재 뽑힐 장비가 추가되지 않았습니다.");
			return true;
		}
		$r = $arr [mt_rand (0, count ($arr) - 1)];
		$class = NEquip::getEquip ($r);

		$player->getInventory ()->addItem ($class->getDefaultEquipItem ());
		NEquip::message ($player, "[ {$r} §r§7] 장비 이(가) 뽑혔습니다.");
		$item = $player->getInventory ()->getItemInHand ();
		$player->getInventory ()->setItemInHand ($player->getInventory ()->getItemInHand ()->setCount ($item->getCount () - 1));
		$player->getInventory ()->addItem (Item::get (437, $damage, 1));
	}
	
	public function onInteract (PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer ();
		$item = $player->getInventory ()->getItemInHand ();
		if (!is_null ($item->getNamedTagEntry ("box"))) {
			$value = $item->getNamedTagEntry ("box")->getValue ();
			$count = [ "레전더리" => 8, "유니크" => 7, "레어" => 6, "S" => 5, "A" => 4, "B" => 3, "C" => 2, "D" => 1 ];
			if (isset ($count [$value])) {
				if ($player->getInventory ()->contains (Item::get (437, $count [$value], 1))) {
					$this->onDraw ($player, $count [$value], $value);
				} else {
					NEquip::message ($player, "{$count [$value]}열쇠가 부족합니다. 상점에 가셔서 열쇠를 구매해주세요.");
				}
			}
		}
	}
}
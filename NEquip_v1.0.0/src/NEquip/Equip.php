<?php


namespace NEquip;

use pocketmine\item\Item;

use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\Player;

class Equip
{
	
	/** @var string */
	protected $equipName = "";
	
	/** @var array */
	protected $data = [];
	
	/** @var array */
	private static $allStat = [
		"hp",
		"str",
		"def",
		"dex",
		"stp",
		"ctk"
	];
	
	private const EQUIP_STAT_FORMAT = [
		"hp" => "체력",
		"str" => "공격",
		"def" => "방어",
		"dex" => "민첩",
		"stp" => "흡혈",
		"ctk" => "크리티컬"
	];
	
	private const EQUIP_RATING_FORMAT = [
		"D" => "§7D",
		"C" => "§3C",
		"B" => "§aB",
		"A" => "§cA",
		"S" => "§eS",
		"레어" => "§9레어",
		"유니크" => "§6유니크",
		"레전더리" => "§d레전더리"
	];
	
	
	public function __construct (string $equipName, array $data)
	{
		$this->equipName = $equipName;
		$this->data = $data;
	}
	
	
	public function getEquipData (): array
	{
		return $this->data;
	}
	
	public function getStatusStat (string $stat): int
	{
		if (isset ($this->data ["status"] [$stat])) {
			return (int) $this->data ["status"] [$stat];
		}
		return 0;
	}
	
	public function getDefaultTotalStat (): int
	{
		$result = 0;
		foreach (self::$allStat as $stat) {
			$result += $this->getStatusStat ($stat);
		}
		return $result;
	}
	
	public function getUpgradeTotalStat (Item $item): int
	{
		$result = 0;
		foreach (self::$allStat as $stat) {
			if (!is_null ($item->getNamedTagEntry ("{$stat}up"))) {
				$result += (int) $item->getNamedTagEntry ($stat . "up")->getValue ();
			}
		}
		return $result;
	}
	
	public function getUpgradeStat (Item $item, string $stat): int
	{
		if (!is_null ($item->getNamedTagEntry ("{$stat}up"))) {
			return (int) $item->getNamedTagEntry ("{$stat}up")->getValue ();
		}
		return 0;
	}
	
	public function getDefaultEquipItem (): Item
	{
		$slot = explode (":", $this->data ["item"]);
		$item = Item::get ($slot [0], $slot [1], 1);
		$item->setCustomName ("§f{$this->equipName}");
		
		$lore = [];
		$lore [] = "§r§d- - -§f 장비 정보 §d- - -";
		$lore [] = "§r§f총합 스탯 : " . $this->getDefaultTotalStat () . "";
		$lore [] = "§r§f장비 등급 : " . self::EQUIP_RATING_FORMAT [$this->data ["rating"]] . "";
		$lore [] = "§r§f레벨 제한 : " . $this->data ["level"] . "";
		$lore [] = "§r§f강화 : §d+§f0";
		$lore [] = "";
		
		$lore [] = "§r§d- - -§f 장비 스탯 §d- - -";
		foreach (self::$allStat as $stat) {
			$lore [] = "§r§f" . self::EQUIP_STAT_FORMAT [$stat] . " : +§d" . $this->data ["status"] [$stat] . "§f (+§d0§f)";
		}
		$item->setLore ($lore);
		
		$item->setNamedTagEntry (new StringTag ("equip", $this->equipName));
		$item->setNamedTagEntry (new IntTag ("upgrade", 0));
		
		foreach (self::$allStat as $stat) {
			$item->setNamedTagEntry (new IntTag ("{$stat}up", 0));
		}
		return $item;
	}
	
	public function getRating (): string
	{
		return $this->data ["rating"];
	}
	
	public function getUpgradePrice (): int
	{
		if ($this->getRating () === "D") {
			return 15000;
		} else if ($this->getRating () === "C") {
			return 30000;
		} else if ($this->getRating () === "B") {
			return 45000;
		} else if ($this->getRating () === "A") {
			return 60000;
		} else if ($this->getRating () === "S") {
			return 75000;
		} else if ($this->getRating () === "레어") {
			return 90000;
		} else if ($this->getRating () === "유니크") {
			return 105000;
		} else if ($this->getRating () === "레전더리") {
			return 120000;
		} else {
			return PHP_INT_MAX;
		}
	}
	
	public function startUpgrade (Player $player, Item $item, int $nowUpgrade = 0): void
	{
		$arr = [
			"hpup" => 0,
			"strup" => 0,
			"defup" => 0,
			"dexup" => 0,
			"stpup" => 0,
			"ctkup" => 0
		];
		foreach (self::$allStat as $stat) {
			if (!is_null ($item->getNamedTagEntry ($stat . "up"))) {
				if (isset ($arr [$stat . "up"]))
					$arr [$stat . "up"] += (int) $item->getNamedTagEntry ($stat . "up")->getValue ();
				$item->removeNamedTagEntry ($stat . "up");
			}
		}
		$rand = mt_rand (1, 2);
		if ($rand === 1) {
			$statRand = mt_rand (0, 6);
			if (isset (self::$allStat [$statRand])) {
			   if (self::$allStat [$statRand] === "stp")
			      $statRand = 3;
				$arr [self::$allStat [$statRand] . "up"] += mt_rand (1, 25);
			}
			$player->getInventory ()->setItemInHand ($this->getNewEquipItem ($item, $arr, $nowUpgrade + 1));
			NEquip::message ($player, "[ {$this->equipName} ] 장비 강화를 성공하셨습니다!");
		} else {
			NEquip::message ($player, "강화를 실패하셨습니다.");
		}
	}
	
	public function getNewEquipItem (Item $item, array $stats = [], int $upgrade = 0): Item
	{
		$item_ = $item;
		
		foreach (self::$allStat as $stat) {
			$item_->setNamedTagEntry (new IntTag ("{$stat}up", (int) $stats [$stat . "up"]));
		}
		$lore = $item_->getLore ();
		
		$total = $this->getDefaultTotalStat () + $this->getUpgradeTotalStat ($item_);
		$lore [1] = "§r§f총합 스탯 : " . $total . "";
		$lore [4] = "§r§f강화 : §d+§f{$upgrade}";
		
		$index = 7;
		foreach ($stats as $stat => $value) {
			$lore [$index ++] = "§r§f" . self::EQUIP_STAT_FORMAT [str_replace ("up", "", $stat)] . " : +§d" . $this->data ["status"] [str_replace ("up", "", $stat)] . "§f (+§d{$value}§f)";
		}
		$item_->setLore ($lore);
		$item_->removeNamedTagEntry ("upgrade");
		$item_->setNamedTagEntry (new IntTag ("upgrade", $upgrade));
		return $item_;
	}
	
	public function getDecomposeItem (): int
	{
		if ($this->getRating () === "D") {
			return mt_rand (1, 2);
		} else if ($this->getRating () === "C") {
			return mt_rand (1, 4);
		} else if ($this->getRating () === "B") {
			return mt_rand (1, 6);
		} else if ($this->getRating () === "A") {
			return mt_rand (1, 6);
		} else if ($this->getRating () === "S") {
			return mt_rand (1, 7);
		} else if ($this->getRating () === "레어") {
			return mt_rand (2, 9);
		} else if ($this->getRating () === "유니크") {
			return mt_rand (3, 10);
		} else if ($this->getRating () === "레전더리") {
			return mt_rand (5, 15);
		} else {
			return 0;
		}
	}
}
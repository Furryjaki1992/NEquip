<?php


namespace NEquip;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use pocketmine\item\Item;

use NEquip\command\{
	AddEquipCommand,
	DeleteEquipCommand,
	GetEquipCommand,
	UpgradeEquipCommand,
	DecomposeEquipCommand,
	EquipBoxCommand
};
use pocketmine\Player;

class NEquip extends PluginBase
{
	
	private static $instance = null;
	
	public static $prefix = "§l§6[알림]§r§7 ";
	
	public $config, $db;
	
	public static $equip = [];
	
	
	public static function runFunction (): NEquip
	{
		return self::$instance;
	}
	
	public function onLoad (): void
	{
		if (self::$instance === null) {
			self::$instance = $this;
		}
	}
	
	public function onEnable (): void
	{
		if (!file_exists ($this->getDataFolder ())) {
			@mkdir ($this->getDataFolder ());
		}
		$this->config = new Config ($this->getDataFolder () . "config.yml", Config::YAML, [
			"equip" => []
		]);
		$this->db = $this->config->getAll ();
		
		foreach (array_keys ($this->db ["equip"]) as $equipName) {
			$data = $this->db ["equip"] [$equipName];
			self::$equip [$equipName] = new Equip ($equipName, $data);
		}
		
		foreach ([
			AddEquipCommand::class,
			DeleteEquipCommand::class,
			GetEquipCommand::class,
			UpgradeEquipCommand::class,
			DecomposeEquipCommand::class,
			EquipBoxCommand::class
		] as $class) {
			$this->getServer ()->getCommandMap ()->register ("avas", new $class ($this));
		}
	}
	
	public function onDisable (): void
	{
		foreach (self::$equip as $equipName => $class) {
			if ($class instanceof Equip) {
				$this->db ["equip"] [$equipName] = $class->getEquipData ();
			}
		}
		$this->config->setAll ($this->db);
		$this->config->save ();
	}
	
	/**
	 * @param Player|CommandSender $player
	 */
	public static function message ($player, string $msg)
	{
		$player->sendMessage (self::$prefix . $msg);
	}
	
	public function isEquip (string $equipName): bool
	{
		return isset ($this->db ["equip"] [$equipName]);
	}
	
	public function addEquip (string $equipName, string $rating, int $level = 0, int $percent = 1, string $status, Item $item): void
	{
		[ $hp, $str, $def, $dex, $stp, $ctk ] = explode (":", $status);
		
		$this->db ["equip"] [$equipName] = [
			"item" => $item->getId () . ":" . $item->getDamage (),
			"rating" => $rating,
			"level" => $level,
			"percent" => $percent,
			"status" => [
				"hp" => $hp,
				"str" => $str,
				"def" => $def,
				"dex" => $dex,
				"stp" => $stp,
				"ctk" => $ctk
			]
		];
		self::$equip [$equipName] = new Equip ($equipName, $this->db ["equip"] [$equipName]);
	}
	
	public function deleteEquip (string $equipName): void
	{
		unset ($this->db ["equip"] [$equipName]);
		unset (self::$equip [$equipName]);
	}
	
	public static function getEquip (string $equipName): ?Equip
	{
		return isset (self::$equip [$equipName]) ? self::$equip [$equipName] : null;
	}
	
	public function getEquipUpgrade (Item $item): int
	{
		if (!is_null ($item->getNamedTagEntry ("upgrade"))) {
			return (int) $item->getNamedTagEntry ("upgrade")->getValue ();
		}
		return -1;
	}
	
	public function getStatArray (Player $player): array
	{
		$stat = [
			"hp" => 0,
			"str" => 0,
			"def" => 0,
			"dex" => 0,
			"stp" => 0,
			"ctk" => 0
		];
		foreach ($player->getArmorInventory ()->getContents () as $item) {
			if ($item->getNamedTagEntry ("equip") !== null) {
				$equip = $item->getNamedTagEntry ("equip")->getValue ();
				$class = self::getEquip ($equip);
				if ($class instanceof Equip) {
					foreach (array_keys ($stat) as $format) {
						$stat [$format] += ($class->getStatusStat ($format) + $class->getUpgradeStat ($item, $format));
					}
				}
			}
		}
		$item = $player->getInventory ()->getItemInHand ();
		if ($item->getId () !== 0) {
			if ($item->getNamedTagEntry ("equip") !== null) {
				$equip = $item->getNamedTagEntry ("equip")->getValue ();
				$class = self::getEquip ($equip);
				if ($class instanceof Equip) {
					foreach (array_keys ($stat) as $format) {
						$stat [$format] += ($class->getStatusStat ($format) + $class->getUpgradeStat ($item, $format));
					}
				}
			}
		}
		return $stat;
	}
}
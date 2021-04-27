<?php
namespace xXKHaLeD098Xx\SpinningCoin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

	/**
	 * @return void
	 */

	public function onEnable() : void {
		Entity::registerEntity(CoinEntity::class, true);
		$this->saveDefaultConfig();
	}

	/**
	 * @param CommandSender $sender
	 * @param Command $command
	 * @param string $label
	 * @param array $args
	 * @return bool
	 */

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		if($command->getName() === "spinningcoin"){
			if($sender instanceof Player){
				if($sender->isOp()){
					if(!isset($args[0])){
						$sender->sendMessage("§aUsage: /spinningcoin spawn|remove");
						return false;
					}
					if($args[0] === "spawn"){
						$this->spawnCoin($sender);
						$sender->sendMessage("§bSpinning coin spawned.");
					} elseif ($args[0] === "remove"){
						$coinEntity = $this->getNearSpinningCoin($sender);
						if($coinEntity !== null){
							$coinEntity->flagForDespawn();
							$sender->sendMessage("§bSpinning coin removed.");
							return true;
						}
						$sender->sendMessage("§cNo spinning coin found.");
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param Player $player
	 * @return CoinEntity|null
	 */

	public function getNearSpinningCoin(Player $player) : ?CoinEntity{
		$level = $player->getLevel();
		foreach ($level->getEntities() as $entity){
			if($entity instanceof CoinEntity){
				if($player->distance($entity) <= 5 && $entity->distance($player) > 0){
					return $entity;
				}
			}
		}
		return null;
	}

	/**
	 * @param Player $sender
	 */

	public function spawnCoin(Player $sender){
		$nbt = Entity::createBaseNBT($sender);
		$path = $this->getFile()."resources/texture.pmskin";
		$skinbytes = file_get_contents($path);
		$skinTag = new CompoundTag("Skin", [
			"Name" => new StringTag("Name", $sender->getSkin()->getSkinId()),
			"Data" => new ByteArrayTag("Data", $skinbytes),
			"GeometryName" => new StringTag("GeometryName", "geometry.geometry.coin"),
			"GeometryData" => new ByteArrayTag("GeometryData", file_get_contents($this->getFile()."resources/Coin.geo.json"))
		]);
		$nbt->setTag($skinTag);
		$entity = new CoinEntity($sender->getLevel(), $nbt);
		$nametag = $this->getConfig()->get("nametag") ?? "&bYou have &e{coins} coins, &a{player}";
		$entity->setNameTag($nametag);
		$entity->setNameTagAlwaysVisible(true);
		$entity->setNameTagVisible(true);
		$entity->spawnToAll();
	}
}


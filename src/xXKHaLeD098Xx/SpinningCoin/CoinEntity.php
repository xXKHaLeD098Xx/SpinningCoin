<?php
namespace xXKHaLeD098Xx\SpinningCoin;

use onebone\economyapi\EconomyAPI;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CoinEntity extends Human {

	/**
	 * CoinEntity constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 */
	public function __construct(Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
	}

	/**
	 * @return void
	 */
	public function saveNBT(): void
	{
		parent::saveNBT();
	}

	/**
	 * @return EconomyAPI
	 */
	public function getEconomyAPI() : EconomyAPI{
		/** @var EconomyAPI $economy */
		$economy = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
		return $economy;
	}

	/**
	 * @param int $currentTick
	 * @return bool
	 */
	public function onUpdate(int $currentTick): bool
	{
		$this->yaw += 5.5;
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		$this->updateMovement();
		foreach ($this->getViewers() as $player){
			if(class_exists(EconomyAPI::class) && class_exists(Main::class)){
				$pk = new SetActorDataPacket();
				$pk->entityRuntimeId = $this->getId();
				$economy = $this->getEconomyAPI();
				$coins = $economy->myMoney($player);
				$pk->metadata = [self::DATA_NAMETAG => [self::DATA_TYPE_STRING, TextFormat::colorize(str_replace(["{coins}", "{player}"], [$coins, $player->getName()], $this->getNameTag()))]];
				$player->dataPacket($pk);
			} else {
				$this->flagForDespawn();
			}
		}
		return parent::onUpdate($currentTick);
	}

	/**
	 * @param EntityDamageEvent $source
	 */

	public function attack(EntityDamageEvent $source): void
	{
		$source->setCancelled();
	}
}
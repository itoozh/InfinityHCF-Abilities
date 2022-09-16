<?php


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class yevwi extends PluginBase implements Listener
{

    private array $cooldowns = [
        'Power' => [],
        'Time' => [],
        'Space' => [],
        'Reality' => []
    ];

    public function onEnable(): void
    {
        $this->saveResource("config.yml");
        $this->getLogger()->info("ez code enabled");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "abilities":
                if (!$sender instanceof Player) {
                    return false;
                }
                if (!$sender->hasPermission("abilities.command")) {
                    return false;
                }
                $powerstone = ItemFactory::getInstance()->get(ItemIds::DYE, 5);
                $powerstone->setCustomName("§r§l§dPower Stone");
                $powerstone->setLore([
                    " ",
                    "§r§7When you use this gem, it gives you the effect of",
                    "§r§7Strength II for 5s & Resistance III for 7s",
                    "§r§7",
                    "§r§ePurchase at §3" . $this->getConfig()->get("store")]);
                $powerstone->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 1, ));
                $powerstone->getNamedTag()->setString("Stone","Power");

                $timestone = ItemFactory::getInstance()->get(ItemIds::DYE, 10);
                $timestone->setCustomName("§r§l§aTime Stone");
                $timestone->setLore([
                    " ",
                    "§r§7When you use this gem, it removes all",
                    "§r§7cooldowns that you have on the scoreboard, like a time travel",
                    "§r§7",
                    "§r§ePurchase at §3" . $this->getConfig()->get("store")]);
                $timestone->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 1, ));
                $timestone->getNamedTag()->setString("Stone","Time");

                $spacestone = ItemFactory::getInstance()->get(ItemIds::DYE, 12);
                $spacestone->setCustomName("§r§l§9Space Stone");
                $spacestone->setLore([
                    " ",
                    "§r§7When you use this gem, all people who are near you",
                    "§r§7within a radius of 10 blocks will give you the",
                    "§r§7levitation effect for 6 seconds",
                    "§r§7",
                    "§r§ePurchase at §3" . $this->getConfig()->get("store")]);
                $spacestone->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 1, ));
                $spacestone->getNamedTag()->setString("Stone","Space");

                $realitystone = ItemFactory::getInstance()->get(ItemIds::DYE, 1);
                $realitystone->setCustomName("§r§l§cReality Stone");
                $realitystone->setLore([
                    " ",
                    "§r§7When you use this gem it will teleport you to the",
                    "§r§7last person who hit you within 15 seconds",
                    "§r§7",
                    "§r§ePurchase at §3" . $this->getConfig()->get("store")]);
                $realitystone->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 1, ));
                $realitystone->getNamedTag()->setString("Stone","Reality");

                $sender->getInventory()->addItem($realitystone);
                $sender->getInventory()->addItem($spacestone);
                $sender->getInventory()->addItem($timestone);
                $sender->getInventory()->addItem($powerstone);
        }
        return true;
    }

    public function inCooldown(string $type, string $player): bool
    {
        if (isset($this->cooldowns[$type]) && isset($this->cooldowns[$type][$player])) {
            return $this->cooldowns[$type][$player] > time();
        }
        return false;
    }

    public function getCooldown(string $type, string $player): int
    {
        return $this->cooldowns[$type][$player] - time();
    }

    public function addCooldown(string $type, string $player, int $time): void
    {
        $this->cooldowns[$type][$player] = time() + $time;
    }

    public function removeCooldown(string $type, string $player): void
    {
        $this->cooldowns[$type][$player] = 0;
    }

    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Stone") !== null) {
                switch ($item->getNamedTag()->getString("Stone")) {
                    case "Reality":
                        $event->cancel();
                        if (!$this->inCooldown("Reality", $player->getName())) {
                            if (!$this->inCooldown("Global", $player->getName())) {

                                if ($player->getLastDamageCause() instanceof Player)
                                    $item = $event->getItem();
                                $cause = $player->getLastDamageCause();
                                if ($player->getLastDamageCause() === null) {
                                    if ($this->inCooldown("Global", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Global", $player->getName()));
                                        $player->sendMessage("cYou have §5§l" . "Global" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    if ($this->inCooldown("Reality", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Reality", $player->getName()));
                                        $player->sendMessage("§cYou have §6§l" . "Reality" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    $player->sendMessage("§cYou tried using the Reality Stone but we couldn't find the player.");
                                }
                                if (!$cause instanceof EntityDamageByEntityEvent) {
                                    if ($this->inCooldown("Global", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Global", $player->getName()));
                                        $player->sendMessage("§cYou have §5§l" . "Global" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    if ($this->inCooldown("Reality", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Reality", $player->getName()));
                                        $player->sendMessage("§cYou have §6§l" . "Reality" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    $player->sendMessage("§cYou tried using the Reality Stone but we couldn't find the player.");
                                    return;
                                }
                                $damager = $cause->getDamager();
                                if (!$damager instanceof Player) {
                                    if ($this->inCooldown("Global", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Global", $player->getName()));
                                        $player->sendMessage("§cYou have §5§l" . "Global" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    if ($this->inCooldown("Reality", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Reality", $player->getName()));
                                        $player->sendMessage("§cYou have §6§l" . "Reality" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    $player->sendMessage("§cYou tried using the Reality Stone but we couldn't find the player.");
                                    return;
                                }
                                if ($player->getLastDamageCause() === null) {
                                    if ($this->inCooldown("Global", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Global", $player->getName()));
                                        $player->sendMessage("§cYou have §5§l" . "Global" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    if ($this->inCooldown("Reality", $player->getName())) {
                                        $cooldown = ($this->getCooldown("Reality", $player->getName()));
                                        $player->sendMessage("§cYou have §6§l" . "Reality" . " §r§ccooldown, you need wait §l" . $cooldown . " seconds!");
                                        return;
                                    }
                                    $player->sendMessage("§cYou tried using the Reality but we couldn't find the player.");
                                    return;
                                }
                                $item = $event->getItem();
                                $item->pop();
                                $player->getInventory()->setItemInHand($item);

                                $this->addCooldown("Reality", $player->getName(), $this->getConfig()->get("reality.cooldown"));
                                $this->addCooldown("Global", $player->getName(), $this->getConfig()->get("global.cooldown"));

                            } else {
                                $time = $this->getCooldown("Global", $player->getName());
                                $player->sendMessage("§cNo puedes usar ninguna gema por ahora, debes esperar " . $time . " segundos");
                            }
                        } else {
                            $time = $this->getCooldown("Reality", $player->getName());
                            $player->sendMessage("§cNo puedes usar la gema de la realidad por ahora, debes esperar " . $time . " segundos");
                        }
                        break;
                    case "Space":
                        $event->cancel();
                        if (!$this->inCooldown("Space", $player->getName())) {
                            if (!$this->inCooldown("Global", $player->getName())) {

                                foreach (Server::getInstance()->getOnlinePlayers() as $online_player) {
                                    if ($player->getPosition()->distance($online_player->getPosition()) <= 10) {
                                        $online_player->getEffects()->add(new EffectInstance(VanillaEffects::LEVITATION(), 20 * 15, 1));
                                        $player->getEffects()->clear();
                                    }
                                }
                                $item = $event->getItem();
                                $item->pop();
                                $player->getInventory()->setItemInHand($item);

                                $this->addCooldown("Space", $player->getName(), $this->getConfig()->get("space.cooldown"));
                                $this->addCooldown("Global", $player->getName(), $this->getConfig()->get("global.cooldown"));

                            } else {
                                $time = $this->getCooldown("Global", $player->getName());
                                $player->sendMessage("§cNo puedes usar ninguna gema por ahora, debes esperar " . $time . " segundos");
                            }
                        } else {
                            $time = $this->getCooldown("Space", $player->getName());
                            $player->sendMessage("§cNo puedes usar la gema del espacio por ahora, debes esperar " . $time . " segundos");;
                        }
                        break;
                    case "Time":
                        $event->cancel();
                        if (!$this->inCooldown("Time", $player->getName())) {
                                $item = $event->getItem();
                                $item->pop();
                                $player->getInventory()->setItemInHand($item);

                                $this->removeCooldown("Power", $player->getName());
                                $this->removeCooldown("Space", $player->getName());
                                $this->removeCooldown("Reality", $player->getName());
                                $this->removeCooldown("Global", $player->getName());
                                $this->addCooldown("Time", $player->getName(), $this->getConfig()->get("time.cooldown"));

                        } else {
                            $time = $this->getCooldown("Time", $player->getName());
                            $player->sendMessage("§cNo puedes usar la gema del tiempo por ahora, debes esperar " . $time . " segundos");
                        }
                        break;
                    case "Power":
                        $event->cancel();
                        if (!$this->inCooldown("Power", $player->getName())) {
                            if (!$this->inCooldown("Global", $player->getName())) {

                                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 5, 1));
                                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 7, 2));
                                $item = $event->getItem();
                                $item->pop();
                                $player->getInventory()->setItemInHand($item);

                                $this->addCooldown("Power", $player->getName(), $this->getConfig()->get("power.cooldown"));
                                $this->addCooldown("Global", $player->getName(), $this->getConfig()->get("global.cooldown"));

                            } else {
                                $time = $this->getCooldown("Global", $player->getName());
                                $player->sendMessage("§cNo puedes usar ninguna gema por ahora, debes esperar " . $time . " segundos");
                            }
                        } else {
                            $time = $this->getCooldown("Power", $player->getName());
                            $player->sendMessage("§cNo puedes usar la gema del poder por ahora, debes esperar " . $time . " segundos");
                        }
                        break;
                }
            }
    }
}
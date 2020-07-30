<?php

/*
 *
 *   _____      _ _ ________   _______
 *  / ____|    | | |  ____\ \ / /  __ \
 * | (___   ___| | | |__   \ V /| |__) |
 *  \___ \ / _ \ | |  __|   > < |  ___/
 *  ____) |  __/ | | |____ / . \| |
 * |_____/ \___|_|_|______/_/ \_\_|
 *
*/

namespace SellEXP;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\PlayerInventory;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener
{

    public function onEnable(): void
    {
        $files = array("sellexp.yml", "xpmessages.yml");
        foreach ($files as $file) {
            if (!file_exists($this->getDataFolder() . $file)) {
                @mkdir($this->getDataFolder());
                file_put_contents($this->getDataFolder() . $file, $this->getResource($file));
            }
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->sellexp = new Config($this->getDataFolder() . "sellexp.yml", Config::YAML);
        $this->xpmessages = new Config($this->getDataFolder() . "xpmessages.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "sellexp":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage(TextFormat::RED . TextFormat::BOLD ."Error: ". TextFormat::RESET . TextFormat::DARK_RED ."Please use this command in game");
                    return true;
                }

                if ($sender->hasPermission("sellexp") || $sender->hasPermission("sellexp.amount") || $sender->hasPermission("sellexp.all")) {
                    if (!$sender->isSurvival()) {
                        $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "Error: " . TextFormat::RESET . TextFormat::DARK_RED . "Please switch back to survival mode.");
                        return false;
                    }

                    if (isset($args[0]) && strtolower($args[0]) == "amount") {
                        if (!$sender->hasPermission("sellexp.amount")) {
                            $error_handPermission = $this->messages->get("error-nopermission-sellEXPAmount");
                            $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "Error: " . TextFormat::RESET . TextFormat::RED . $error_handPermission);
                            return false;
                        }
                        $xp = $sender->getInventory()->getXPAmount();
                        $xpId = $item->getXP();
                        if ($item->getId() === 0) {
                            $sender->sendMessage(TextFormat::RED . TextFormat::BOLD ."Error: ". TextFormat::RESET . TextFormat::DARK_RED . "You do not have any EXP.");
                            return false;
                        }
                        if ($this->sell->get($XPAmount) == null) {
                            $sender->sendMessage(TextFormat::RED . TextFormat::BOLD ."Error: ". TextFormat::RESET . TextFormat::GREEN . $XPAmount->getName() . TextFormat::DARK_RED ." cannot be sold.");
                            return false;
                        }
                        EconomyAPI::getInstance()->addMoney($sender, $this->sell->get($XPAmount) * $xp->getCount());
                        $sender->getInventory()->removeEXP($XPAmount);
                        $price = $this->sell->get($xp->getInventory()) * $xp->getCount();
                        $sender->sendMessage(TextFormat::GREEN . TextFormat::BOLD . "(!) " . TextFormat::RESET . TextFormat::GREEN . "$" . $price . " has been added to your account.");
                        $sender->sendMessage(TextFormat::GREEN . "Sold for " . TextFormat::RED . "$" . $price . TextFormat::GREEN . " (" . $xp->getCount() . " " . $xp->getName() . " at $" . $this->sell->get($XPAmount) . " each).");
                    } else if (isset($args[0]) && strtolower($args[0]) == "all") {
                        if (!$sender->hasPermission("sellexp.all")) {
                            $error_allPermission = $this->messages->get("error-nopermission-sellEXPAll");
                            $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "Error: " . TextFormat::RESET . TextFormat::RED . $error_allPermission);
                            return false;
                        }
                        $items = $sender->getInventory()->getContents();
                        foreach ($items as $item) {
                            if ($this->sell->get($item->getId()) !== null && $this->sell->get($item->getId()) > 0) {
                                $price = $this->sell->get($item->getId()) * $item->getCount();
                                EconomyAPI::getInstance()->addMoney($sender, $price);
                                $sender->sendMessage(TextFormat::GREEN . TextFormat::BOLD . "(!) " . TextFormat::RESET . TextFormat::GREEN . "Sold for " . TextFormat::RED . "$" . $price . TextFormat::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $this->sell->get($item->getId()) . " each).");
                                $sender->getInventory()->remove($item);
                            }
                        }
                    } else if (isset($args[0]) && strtolower($args[0]) == "about") {
                        $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "(!) " . TextFormat::RESET . TextFormat::GRAY . "This server uses the plugin, SellEXP, by VMPE Development Team");
                    } else {
                        $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "(!) " . TextFormat::RESET . TextFormat::DARK_RED . "Sell Online Market");
                        $sender->sendMessage(TextFormat::RED . "- " . TextFormat::DARK_RED . "/sellexp amount " . TextFormat::GRAY . "- Sell the item that's in your hand.");
                        $sender->sendMessage(TextFormat::RED . "- " . TextFormat::DARK_RED . "/sellexp all " . TextFormat::GRAY . "- Sell every possible thing in inventory.");
                        return true;
                    }
                } else {
                    $error_permission = $this->messages->get("error-permission");
                    $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "Error: " . TextFormat::RESET . TextFormat::RED . $error_permission);
                }
                break;
        }
        return true;
    }

}

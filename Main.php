<?php
namespace himpq\economyapi;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\{CommandSender,Command};
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use himpq\economyapi\tips;
class main extends PluginBase implements Listener{
/*
 Team:WPluginTeam
 QQ:2042432192
 Plugin-name: WEconomyAPI
 new Economy plugin.language is PHP!
 All Rights Reserved－保留所有权利
 */
 
private static $instance = null;
private $admin = array();
public function isAdmin(Player $player){ return in_array($player,$this->admin);}
public static function getInstance(){
return self::$instance;
}
public function onLoad(){
$instance = $this;
$this->getServer()->getScheduler()->scheduleRepeatingTask(new tips([$this,"tips"]));
}
public function onEnable(){
@mkdir($this->getDataFolder());
$this->money = new Config($this->getDataFolder()."PlayerMoney.yml",Config::YAML,array());
$this->config = new Config($this->getDataFolder()."Config.yml",Config::YAML,array(
"#" => "WEconomyAPI作者Himpq",
"##" => "配置: true=开启 false=关闭",
"###" => "玩家进服初始金币",
"Default-Money" => 50,
"####" => "使用权限",
"#####" => "填法: admin: [xxx,xxx,xxx,xxxx]",
"admin" => array(),
"Start-WFeedback-API" => false));
$this->getLogger()->info("[WEconomyAPI] 管理员列表".implode(",",$this->config->get("admin")));
$this->getLogger()->info("[WEconomyAPI] 注意搭配插件:WFeedback");
}
public function save(){
$this->config->save();
$this->money->save();
}
public function tips(){
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->sendTip(".                                                       §3[§e你的金钱:{$this->lookMoney($p->getName())}§3]");
}
}
public function onDisable(){
$this->save();
$this->getLogger()->info("[WEconomyAPI] 保存配置完毕，正在卸载插件.....");
}
public function onJoin(PlayerJoinEvent $event){
$p = $event->getPlayer();
$n = $p->getName();
if($p->isAdmin()){
$event->setJoinMessage("§7[WEconomyAPI] §b管理员{$n}加入了游戏");
}else{
$event->setJoinMessage("§7[WEconomyAPI] §b玩家{$n}加入了游戏");
}
if($this->money->get($n) !== null){
$this->newAccount($n);
$p->sendMessage("§7[WEconomyAPI] §b增加经济账户!");
}else
$p->sendMessage("§7[WEconomyAPI] §b注册了！");
}
public function addMoney($account,$money,$default){
$this->money->set($account,$money + $default);
$this->money->save();
}
public function delMoney($account,$money,$default){
$this->money->set($account,$money - $default);
$this->money->save();
}
public function lookMoney($account){
$moneys = $this->money->get($account);
return $moneys;
}
public function setMoney($account,$money,$default){
$this->money->set($account,$money+0);
$this->money->save();
}
public function reload(){
$this->getServer()->reload();
}
public function newAccount($name){
$this->money->set($n,$this->confit->get("Defautl-Money"));
$this->save();
}


public function onCommand(CommandSender $sender,Command $command,$label,array $args){
switch($command->getName()){
case "we":
if(!$sender->isOP()){
$sender->sendMessage("§7[WEconomyAPI] §b玩家帮助".
"\n§7/paymoney [玩家] [金币] 支付给一个玩家金币".
"\n§7/we 帮助");
return true;
}
$sender->sendMessage("§7[WEconomyAPI] §b管理员帮助".
"\n§7/paymoney [玩家] [金钱] 支付给一个玩家金钱".
"\n§7/addmoney [玩家] [金钱] 增加玩家的金钱".
"\n§7/delmoney [玩家] [金钱] 减少玩家的金钱".
"\n§7/setmoney [玩家] [金钱] 设置玩家的金钱".
"\n§7/lookmoney [玩家] 查看玩家的金钱".
"\n§7--------------§bWEconomyAPI§7-----------------".
"\n§7[WEconomyAPI] §b一下是您安装了WRand插件才可以使用的功能".
"\n§7/rand 玩家抽奖-默认次数为3，可以抽到10-100元之间的金币(随机)".
"\n§7/oprand [玩家] [次数] §b设置玩家的抽奖次数".
"\n§7--------------§bWEconomyAPI§7------------------".
"\n§7WEconomyAPI适配插件: WFeedback,WRand,xxx".
"\n§7欢迎在§lhttps://pl.kkmo.net/mct/source/177.htm§b下载插件！".
"\n§7建议尽量不要把默认金币数量设为0，会查看不了自己的金币".
"\n§7--------------§bWEconomyAPI后语§7--------------".
"\n§7感谢您对WEconomyAPI的支持，快捷，方便的经济插件。");
return true;
break;
case "paymoney":
if(!isset($args[1])){
return false;
}
if(!is_numeric($args[1])){
return false;
}
if($this->money->get($sender->getName()) < $args[1]){
$sender->sendMessage("§7[WEconomyAPI] §c你的钱不够");
return true;
}
if($this->money->get($args[0]) == ""){
$sender->sendMessage("§7[WEconomyAPI] §c未找到该用户/这个用户的金钱为0");
return true;
}
$this->delMoney($sender->getName(),$args[1],$this->money->get($sender->getName()));
$this->addMoney($args[0],$args[1],$this->money->get($args[0]));
$sender->sendMessage("§7[WEconomyAPI] §b成功支付给{$args[0]}元给{$args[1]}!");
return true;
break;
case "setmoney":
if(!$sender->isOP()){
$sender->sendMessage("§7[WEconomyAPI] §c你没有权限使用这个命令");
return true;
}
if(!isset($args[1])){
return false;
}
if(!is_numeric($args[1])){
return false;
}
$this->setMoney($args[0],$args[1],$this->money->get($args[0]));
$sender->sendMessage("§7[WEconomyAPI] §b成功设置{$args[0]}的金钱为{$args[1]}");
return true;
break;
case "lookmoney":
if(!$sender->isOP()){
$sender->sendMessage("§7[WEconomyAPI] §c你没有权限使用这个命令");
return true;
}
if(!isset($args[0])){
return false;
}
if($this->money->get($args[0]) == ""){
$sender->sendMessage("§7[WEconomyAPI] §b未找到该账户/这个账户的金钱为0，无法支付");
return true;
}
$lookm = $this->lookMoney($args[0]);
$sender->sendMessage("§7[WEconomyAPI] §b这个账户的金钱为 $lookm");
return true;
break;
case "addmoney":
if(!isset($args[1])){
$sender->sendMessage("§7[WEconomyAPI] §c用法:/addmoney [玩家] [金币]");
return true;
}
if(!$sender->isOP()){
$sender->sendMessage("§7[WEconomyAPI] §c你没有权限使用这个命令");
return true;
}
if(!isset($args[1])){
return false;
}
if(!is_numeric($args[1])){
return false;
}
if($this->money->get($args[0]) == ""){
$sender->sendMessage("§7[WEconomyAPI] §c没找到这个账户:{$args[0]}!/这个账户的金钱为0，无法支付！");
return true;
}
$defaulT = $this->money->get($args[0]);
$this->addMoney($args[0],$args[1],$defaulT);
$sender->sendMessage("§7[WEconomyAPI] §b成功往{$args[0]}里的账户添加{$args[1]}块钱！");
return true;
break;
case "delmoney":
if(!$sender->isOP()){
$sender->sendMessage("§7[WEconomyAPI] §b你没有权限使用这个命令");
return true;
}
if(!isset($args[1])){
return false;
}
if(!is_numeric($args[1])){
return false;
}
if($this->money->get($args[0]) == ""){
$sender->sendMessage("§7[WEconomyAPI] §b无法找到该账户/这个账户的金钱为0，无法完成支付！");
}
$defauLT = $this->money->get($args[0]);
$this->delMoney($args[0],$args[1],$defauLT);
$sender->sendMessage("§7[WEconomyAPI] §b已删除{$args[0]}的钱{$args[1]}");
return true;
break;
}
}
}

















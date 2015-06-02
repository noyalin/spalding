<?php
$installer = $this;

$installer->startSetup();

Mage::log("Running installer weixinpay");

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('weixin_appset')}` ;
CREATE TABLE `{$this->getTable('weixin_appset')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(18) NOT NULL,
  `appsecret` varchar(32) NOT NULL,
  `access_token` varchar(800)  NULL,
  `create_time` varchar(80)  NULL,
  `ticket` varchar(500)  NULL,
  `ticket_time` varchar(80)  NULL,
  `web_access_token` varchar(800)  NULL,
  `refresh_token` varchar(800)  NULL,
  `web_createtime` int(12)  NULL,
  `refresh_token_createtime` int(12)  NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ");

$installer->run("
Insert into `{$this->getTable('weixin_appset')}`  (`id`, `appid`, `appsecret`, `access_token`, `create_time`, `ticket`, `ticket_time`, `web_access_token`, `refresh_token`, `web_createtime`, `refresh_token_createtime`) VALUES (NULL, 'wx79873079dca36474', 'ba74acc7f680e7bbe62203815df1df41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
");


$installer->endSetup();

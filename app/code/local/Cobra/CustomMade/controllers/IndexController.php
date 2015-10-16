<?php

class Cobra_CustomMade_IndexController extends Mage_Core_Controller_Front_Action
{

    public function allAction()
    {
//        $session = self::getSession();
        Mage::getSingleton('core/session')->setTestMode(1);
//        $session->setTestMode(1);
//        self::setSession($session);

        echo "allAction Ok";
    }

    public function textAction()
    {
//        $session = self::getSession();
        Mage::getSingleton('core/session')->setTestMode(0);
//        $session->setTestMode(0);
//        self::setSession($session);

        echo "textAction Ok";
    }

    public function clearSessionAction()
    {
        Mage::getSingleton('core/session')->setCustomerId(null);
        Mage::getSingleton('core/session')->getCurrentSku(null);

        echo "Clear OK";
    }

    public function completeAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $sku = $params['sku'];
        $session = self::getSession($sku);

        $pos = $params['position'];
        $type = $params['type'];
        $content1 = null;
        $content2 = null;
        $content3 = null;
        $content4 = null;

        if ($pos == 1) {
            if ($type == 1) {
                $url_array = Mage::helper('custommade/imagehandler')->createImages($sku, $pos, $params['originalImg'], $params['cut_pos']);
                if ($url_array) {
                    $content1 = $url_array['effect'];
                } else {
                    $content1 = "error";
                }
            } elseif ($type == 2) {
                $content1 = $params['text1'];
                $content2 = $params['text3'];
                $content3 = $params['size'];
                $content4 = $params['font'];
            } else {
                $type = 3;
                $content1 = null;
                $content2 = null;
                $content3 = null;
                $content4 = null;
            }
            $session->setTypeP1($type);
            $session->setContent1P1($content1);
            $session->setContent2P1($content2);
            $session->setContent3P1($content3);
            $session->setContent4P1($content4);

        } elseif ($pos == 2) {
            if ($type == 1) {
                $url_array = Mage::helper('custommade/imagehandler')->createImages($sku, $pos, $params['originalImg'], $params['cut_pos']);
                if ($url_array) {
                    $content1 = $url_array['effect'];
                }
            } elseif ($type == 2) {
                $content1 = $params['text2'];
                $content2 = $params['text4'];
                $content3 = $params['size'];
                $content4 = $params['font'];
            } else {
                $type = 3;
                $content1 = null;
                $content2 = null;
                $content3 = null;
                $content4 = null;
            }
            $session->setTypeP2($type);
            $session->setContent1P2($content1);
            $session->setContent2P2($content2);
            $session->setContent3P2($content3);
            $session->setContent4P2($content4);

        }

        $session->setPos($pos);
        self::setSession($session);

        echo self::getCustomMadeSession($pos, $sku);
    }

    public function resetAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $sku = $params['sku'];
        $session = self::getSession($sku);

        $pos = $params['position'];
        if ($pos == 1) {
            $session->setTypeP1(null);
            $session->setContent1P1(null);
            $session->setContent2P1(null);
            $session->setContent3P1(null);
            $session->setContent4P1(null);
        } elseif ($pos == 2) {
            $session->setTypeP2(null);
            $session->setContent1P2(null);
            $session->setContent2P2(null);
            $session->setContent3P2(null);
            $session->setContent4P2(null);
        }
        $session->setPos($pos);
        self::setSession($session);
    }

    public function previewAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $sku = $params['sku'];
        $session = self::getSession($sku);
        $status = $session->getCustomStatus();
        if ($status == 1) {
            $session->setCustomStatus(0);
        } else {
            $session->setCustomStatus(1);
        }
        self::setSession($session);
    }

    public function checkAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $position = $params['position'];
        $sku = $params['sku'];
        echo self::getCustomMadeSession($position, $sku);

    }

    public function agreeAction()
    {
        Mage::getSingleton('core/session')->setCustomermadeAgree(1);
    }

    private function getCustomMadeSession($position, $sku)
    {
        $session = self::getSession($sku);
        $res = array();;
        if ($position == 1) {
            $res['type'] = $session->getTypeP1();
            $res['content1'] = $session->getContent1P1();
            $res['content2'] = $session->getContent2P1();
            $res['content3'] = $session->getContent3P1();
            $res['content4'] = $session->getContent4P1();
        } elseif ($position == 2) {
            $res['type'] = $session->getTypeP2();
            $res['content1'] = $session->getContent1P2();
            $res['content2'] = $session->getContent2P2();
            $res['content3'] = $session->getContent3P2();
            $res['content4'] = $session->getContent4P2();
        }
        $resultString = json_encode($res);
        return $resultString;
    }

    private function getSession($sku)
    {
        $customerId = Mage::getSingleton('core/session')->getCustomerId();
//        $currentSku = Mage::getSingleton('core/session')->getCurrentSku();
//        $session = Mage::getModel('custommade/session')->getSession($customerId, $currentSku);
        $session = Mage::getModel('custommade/session')->getSession($customerId, $sku);
        return $session;
    }

    private function setSession($session)
    {
        Mage::getModel('custommade/session')->setSession($session);
    }
}
<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/AdminBase for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace AdminBase\Controller;

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminBaseController extends AbstractActionController
{
    public function indexAction(){
        $this->redirect()->toUrl("admin/user");
    }

    public function menuAction()
    {
        return array();
    }

    public function pushMsgAction(){
        $response = $this->getResponse();

        // This is our new stuff
        $context = new \ZMQContext();
        /** @var $socket \ZMQSocket */
        $socket = $context->getSocket( \ZMQ::SOCKET_PUSH , 'event:broadcast');
        $socket->connect("tcp://127.0.0.1:5555");
        $something = $socket->send(json_encode(array(
            "code" => "1",
            "message" => "hello world",
        )));


        return $response->setContent(Json::encode(array(
            "state" => true,
        )));
    }

    public function fooAction(){
        $this->setRenderer();
        $this->layout('layout/Home');
        return array();
    }
    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->appendFile($baseUrl . '/js/admin/autobahn.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/admin/test.js');
    }

    public function getMenu()
    {
        return "helloworld";
    }
}

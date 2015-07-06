<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\CommonFunctions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function errorAction(){
        $vm = new ViewModel();
        $vm->setTemplate('default');
        return $vm;
    }

    public function sitemapAction(){
        $this->getResponse()->getHeaders()->addHeaders(array('Content-type' => 'text/xml'));
        $this->layout('sitemap/sitemapxml');
    }


    public function testAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        $datetime = $request->getQuery('time');
        if($datetime){
            $expTime = CommonFunctions::getExpectedDeliveredTime($datetime);
            $response->setContent($expTime);
        }
        return $response;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-9-22
 * Time: 下午7:00
 */

namespace AdminMgr\Controller;

use Application\Entity\Seller;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use AdminBase\Model\AdminMenu;
use Application\Model\ExcelGenerator;
use Zend\View\Model\ViewModel;
use Application\Model\Logger;
use Exception;

class StatisticsController extends AbstractActionController{

    protected $_orderTable;
    protected $_orderDetailTable;
    protected $_workerTable;
    protected $_sellerTable;
    protected $_userTable;

    public function indexAction(){
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('数据统计', $basePath);
            $sellers = $this->getSellerTable()->fetchAll();
            $arr = array(
                "menu" => $menu,
                "sellers" => $sellers,
            );

            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "footer",
            )));
            $vm =  new ViewModel($arr);
            $this->setRenderer();
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/datepicker3.css');
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/jqplot/jquery.jqplot.css');

        $renderer->headScript()->appendFile($baseUrl . '/js/admin/statistics.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/bootstrap-datepicker.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jquery.jqplot.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.canvasAxisLabelRenderer.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.canvasTextRenderer.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.dateAxisRenderer.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.highlighter.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.barRenderer.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.categoryAxisRenderer.min.js');
        $renderer->headScript()->appendFile($baseUrl . '/js/jqplot/jqplot.pointLabels.min.js');
    }

    public function exportSellerReportFormAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = '';
        do{
            $sellerId = $request->getQuery('s');
            $startTime = $request->getQuery('st');
            $endTime = $request->getQuery('et');
//            $fileName = $request->getQuery('fn');

            if($sellerId == null || $startTime == null || $endTime == null){
                $code = 10001;
                $message = '参数不全';
                break;
            }
//            $fileName = $fileName?$fileName:(time());
            $seller = $this->getSellerTable()->getSeller($sellerId);
            if($seller){
                $fileName = $seller->getName().'-'.$startTime.'至'.$endTime;
            }else{
                $fileName = "全部商户".'-'.$startTime.'至'.$endTime;
            }


            $orderDetails = $this->getOrderDetailTable()->getOrderDetailWithOrderInfo($sellerId, $startTime, $endTime);

            $headers = array(
                "订单号",
                "用户",
                "下单时间",
                "商家",
                "产品名",
                "主分类",
                "子分类",
                "单价",
                "单位",
                "数量",
                "小计",
                "收货人",
                "大学",
                "宿舍区",
                "宿舍楼",
                "宿舍号",
//                "备注",
            );

            $excelGen = new ExcelGenerator();
            $excelGen->generateExcel($headers, $orderDetails, $fileName);


        }while(false);

        if($code != 0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }

        return $response;
    }

    public function updateAnalysisChartAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = '';
        do{
            $type = $request->getQuery('type');
            $startTime = $request->getQuery('st');
            $endTime = $request->getQuery('et');

            $sellerId = $request->getQuery('sid');
            $mcId = $request->getQuery('mcid');
            $cId = $request->getQuery('cid');
            $limit = $request->getQuery('limit');

            if($type == null || $startTime == null || $endTime == null){
                $code = 10001;
                $message = '参数不全';
                break;
            }

            $chartData = array();
            switch($type){
                case 'user':
                    $chartData = $this->getUserAnalysisData($startTime, $endTime);
                    break;
                case 'goods':
                    $chartData = $this->getGoodsAnalysisData($sellerId, $mcId, $cId, $startTime, $endTime, $limit);
                    break;
                case 'order':
                    $chartData = $this->getOrderAnalysisDate($startTime, $endTime);
                    break;
                default:
                    break;
            }

            $response->setContent(Json::encode(array(
                "state" => true,
                "chartData" => $chartData
            )));

        }while(false);

        if($code != 0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }

        return $response;
    }

    protected function getUserAnalysisData($startTime, $endTime){
        $data = array();
        $select = new Select('t_user');
        $select->columns(array(
            'dt' => new Expression('date(registerTime)'),
            'cnt' => new Expression('count(*)'),
        ));
        $where = new Where();
        $where->between('registerTime', $startTime, $endTime);
        $select->where($where);
        $select->group('dt');
        $select->order('dt asc');
        $rows = $this->getUserTable()->selectWith($select);
        foreach($rows as $row){
            $data[$row->dt] = intval($row->cnt);
        }

        return $data;
    }

    protected function getGoodsAnalysisData($sellerId, $mcId, $cId, $startTime, $endTime, $limit){
        $data = array();
        $select = new Select('t_order_detail');
        $select->columns(array(
            'Name' => 'name',
            'Count' => new Expression('sum(t_order_detail.count)'),
            'Total' => new Expression('sum(t_order_detail.total)'),
        ));
        $select->join(array('o' => 't_order'),
            "o.id = t_order_detail.orderId",
            array(),
            Select::JOIN_LEFT)

            ->join(array('g' => 't_goods'),
            'g.id = t_order_detail.goodsId',
            array(),
            Select::JOIN_LEFT)

            ->join(array('mc' => 't_goods_mainClass'),
            'g.mainClassId = mc.Id',
            array(
                'MainClassName' => 'name',
            ),
            Select::JOIN_LEFT)

            ->join(array('c' => 't_goods_class'),
            'g.classId = c.id',
            array(
                'ClassName' => 'name',
            ),
            Select::JOIN_LEFT);

        $where = new Where();
        $where->equalTo('o.state', 9, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->greaterThan('o.orderTime', $startTime, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->lessThan('o.orderTime', $endTime, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);

        if($sellerId && $sellerId != '0'){
            $where->equalTo('t_order_detail.sellerId', $sellerId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        }
        if($mcId && $mcId != '0'){
            $where->equalTo('mc.id', $mcId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        }
        if($cId && $cId != '0'){
            $where->equalTo('c.id', $cId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        }

        $select->where($where);

        $select->group('t_order_detail.name');
        $select->order('Count desc');

        if($limit && is_numeric($limit)){
            $select->limit(intval($limit));
        }else{
            $select->limit(10);
        }

        $rows = $this->getOrderDetailTable()->selectWith($select);

        $goodsCountData = array();
        foreach($rows as $row){
            $goodsCountData[] = array(
                "Name" => $row->Name,
                "MainClassName" => $row->MainClassName,
                "ClassName" => $row->ClassName,
                "Count" => $row->Count,
                "Total" => $row->Total,
            );
        }
        $data['goodsCount'] = $goodsCountData;

        return $data;
    }

    protected function getOrderAnalysisDate($startTime, $endTime){
        $data = array();

        //get order count data
        $orderCountData = array();
        $select = new Select('t_order');
        $select->columns(array(
            'dt' => new Expression('date(orderTime)'),
            'cnt' => new Expression('count(*)'),
        ));
        $where = new Where();
        $where->between('orderTime', $startTime, $endTime);
//        $where->equalTo('state', 9, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $select->where($where);
        $select->group('dt');
        $select->order('dt asc');
        $rows = $this->getOrderTable()->selectWith($select);
        foreach($rows as $row){
            $orderCountData[$row->dt] = intval($row->cnt);
        }
        $data['orderCount'] = $orderCountData;

        //get order total data (finish state)
        $orderTotalData = array();
        $select = new Select('t_order');
        $select->columns(array(
            'dt' => new Expression('date(orderTime)'),
            'total' => new Expression('sum(total)'),
        ));
        $where = new Where();
        $where->between('orderTime', $startTime, $endTime);
        $where->equalTo('state', 9, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $select->where($where);
        $select->group('dt');
        $select->order('dt asc');
        $rows = $this->getOrderTable()->selectWith($select);
        foreach($rows as $row){
            $orderTotalData[$row->dt] = intval($row->total);
        }
        $data['orderTotal'] = $orderTotalData;

        //get domain3 count data (finish state)
        $domain3CountData = array();
        $select = new Select('t_order');
        $select->columns(array(
            'domain3' => 'domain3',
            'cnt' => new Expression('count(*)'),
        ));
        $where = new Where();
        $where->between('orderTime', $startTime, $endTime);
        $where->equalTo('state', 9, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->notEqualTo('domain3', '', Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->notEqualTo('domain3', '请选择', Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $select->where($where);
        $select->group('domain3');
        $select->order('cnt desc');
        $rows = $this->getOrderTable()->selectWith($select);
        foreach($rows as $row){
            $domain3CountData[$row->domain3] = intval($row->cnt);
        }
        $data['domain3Count'] = $domain3CountData;

        //get domain3 total data (finish state)
        $domain3TotalData = array();
        $select = new Select('t_order');
        $select->columns(array(
            'domain3' => 'domain3',
            'total' => new Expression('sum(total)'),
        ));
        $where = new Where();
        $where->between('orderTime', $startTime, $endTime);
        $where->equalTo('state', 9, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->notEqualTo('domain3', '', Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->notEqualTo('domain3', '请选择', Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $select->where($where);
        $select->group('domain3');
        $select->order('total desc');
        $rows = $this->getOrderTable()->selectWith($select);
        foreach($rows as $row){
            $domain3TotalData[$row->domain3] = intval($row->total);
        }
        $data['domain3Total'] = $domain3TotalData;

        return $data;
    }

    public function fooAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();




        $headers = array(
            "header1",
            "header2",
            "header3",
        );
        $contents = array(

        );
        for($j = 0; $j<10; $j++){
            $contents[] = array(
                "content1",
                "content2",
                "content3",
            );
        }

        $excelGen = new ExcelGenerator();
//        $excelGen->generateExcel($headers, $contents);


        return $response;
    }

    /**
     * @return \Home\Model\OrderTable
     */
    public function getOrderTable()
    {
        if (!$this->_orderTable) {
            $t = $this->getServiceLocator();
            $this->_orderTable = $t->get('Home\Model\OrderTable');
        }
        return $this->_orderTable;
    }

    /**
     * @return \Home\Model\OrderDetailTable
     */
    public function getOrderDetailTable()
    {
        if (!$this->_orderDetailTable) {
            $t = $this->getServiceLocator();
            $this->_orderDetailTable = $t->get('Home\Model\OrderDetailTable');
        }
        return $this->_orderDetailTable;
    }

    /**
     * @return \Worker\Model\WorkerTable
     */
    public function  getWorkerTable(){
        if(!$this->_workerTable){
            $t = $this->getServiceLocator();
            $this->_workerTable = $t->get('Worker\Model\WorkerTable');
        }
        return $this->_workerTable;
    }

    /**
     * @return \Seller\Model\SellerTable
     */
    public function getSellerTable()
    {
        if (!$this->_sellerTable) {
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
    }

    /**
     * @return \User\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->_userTable) {
            $t = $this->getServiceLocator();
            $this->_userTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_userTable;
    }

} 
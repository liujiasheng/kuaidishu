<?php

/**
 * Description of StickyNotesTable
 *
 * @author Arian Khosravi <arian@bigemployee.com>, <@ArianKhosravi>
 */
// module/Authenticate/src/Authenticate/Model/AdminTable.php

namespace Authenticate\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
//key name as follow

class SellerTable extends AbstractTableGateway {

    protected $table = 't_seller';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getSellerByUsername($username)
    {
        $row = $this->select(array('Username'=>$username))->current();
        if(!$row){
            return false;
        }
        return $seller = new \Application\Entity\Seller(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Name'=>$row->Name,
            'Email'=>$row->Email,
            'Address'=>$row->Address,
            'Phone'=>$row->Phone,
            'ContactPhone'=>$row->ContactPhone,
            'LoginIP'=>$row->LoginIP,
            'LoginTime'=>$row->LoginTime,
            'State'=>$row->State,
        ));
    }

    /**
     * @param $id
     * @return \Application\Entity\Seller|bool
     */
    public function getSellerById($id)
    {
        $row = $this->select(array("ID"=>$id))->current();
        if(!$row)
            return false;
        $entity = new \Application\Entity\Seller(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Name'=>$row->Name,
            'Email'=>$row->Email,
            'Address'=>$row->Address,
            'Phone'=>$row->Phone,
            'ContactPhone'=>$row->ContactPhone,
            'Logo'=>$row->Logo,
            'Comment'=>$row->Comment,
            'LoginTime'=>$row->LoginTime,
            'LoginIP'=>$row->LoginIP,
            'State'=>$row->State,
        ));
        return $entity;
    }


}
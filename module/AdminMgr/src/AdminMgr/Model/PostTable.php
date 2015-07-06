<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-23
 * Time: 下午9:32
 */

namespace AdminMgr\Model;


use Application\Entity\Post;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\ArrayObject;

class PostTable extends AbstractTableGateway{

    protected $table = "t_post";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getPostById($postId)
    {
        $row = $this->select(array('ID'=>$postId))->current();
        if(!$row){
            return false;
        }
        return new Post(array(
            'ID'=>$row->ID,
            'Date'=>$row->Date,
            'Type'=>$row->Type,
            'Title'=>$row->Title,
            'Source'=>$row->Source,
            'Summary'=>$row->Summary,
            'Remark'=>$row->Remark,
            'SurfacePlot'=>$row->SurfacePlot,
            'Content'=>$row->Content,
            'Author'=>$row->Author,
        ));
    }

    public function getSourceList()
    {
        $select = new Select($this->table);
        $select->columns(array(new Expression("distinct(Source) as Source")));
        $rs = $this->selectWith($select);
        if(!$rs){
            return false;
        }
        $rs1 = array();
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;

    }

    public function getTypeList()
    {
        $select = new Select($this->table);
        $select->columns(array(new Expression("distinct(Type) as Type")));
        $rs = $this->selectWith($select);
        if(!$rs){
            return false;
        }
        $rs1 = array();
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }

    public function getPostContentById($postId)
    {
        $rs = $this->select(array("ID"=>$postId))->current();
        if(!$rs){
            return false;
        }
        return $rs->getArrayCopy();
    }

    public function getPostList($where, $start, $limit)
    {
        $select = new Select($this->table);
        $select->where($where)->offset($start)->limit($limit)->order("ID desc");
        $rs = $this->selectWith($select);
        if(!$rs){
            return false;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach ($rs as $row) {
            array_push($rs1,$row->getArrayCopy());
        }

        return $rs1;
    }

    public function getPostListCount($where)
    {
        $select = new Select($this->table);
        $select->where($where);
        $rs = $this->selectWith($select)->count();
        return $rs;
    }
} 
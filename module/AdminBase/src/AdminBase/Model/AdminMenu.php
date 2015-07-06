<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:39
 * To change this template use File | Settings | File Templates.
 */
namespace AdminBase\Model;

class AdminMenu
{
    protected function getMenuArray($basePath){
        return array(
            '用户管理' => $basePath.'/admin/user',
            '员工管理' => $basePath.'/admin/worker',
            '商家管理' => $basePath.'/admin/seller',
            '商品分类管理' => $basePath.'/admin/goodsClass',
            '商品管理' => $basePath.'/admin/goods',
            '订单管理' => $basePath.'/admin/order',
            '数据统计' => $basePath.'/admin/statistics',
            '系统设置' => '#',
            '文章管理' => $basePath.'/admin/post',
            '订单指派管理' => $basePath.'/admin/orderAssign'
        );
    }
    public function getMenu($select = null, $basePath = "")
    {
        $menuHtml = '<div class="list-group">';
        $menuArr = $this->getMenuArray($basePath);
        $menuNames = array_keys($menuArr);
        $menuValues = array_values($menuArr);
        for($i=0; $i<count($menuArr); $i++)
        {
            $menuHtml .= '<a href="'.$menuValues[$i].'" class="list-group-item';
            if($select == $menuNames[$i]) $menuHtml .= ' active';
            $menuHtml .= '">'.$menuNames[$i].'</a>';
        }
        $menuHtml .= '</div>';
        return $menuHtml;
    }
}

//<a href="#" class="list-group-item active">用户管理</a>
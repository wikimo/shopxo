<?php
namespace app\admin\controller;

use app\service\OrderService;
use app\service\PaymentService;
use app\service\ExpressService;

/**
 * 订单管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct()
    {
        // 调用父类前置方法
        parent::__construct();

        // 登录校验
        $this->Is_Login();

        // 权限校验
        $this->Is_Power();
    }

    /**
     * 订单列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
    public function Index()
    {
        // 参数
        $params = input();
        $params['admin'] = $this->admin;
        $params['user_type'] = 'admin';

        // 分页
        $number = 10;

        // 条件
        $where = OrderService::OrderListWhere($params);

        // 获取总数
        $total = OrderService::OrderTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  url('admin/order/index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = OrderService::OrderList($data_params);
        $this->assign('data_list', $data['data']);

        // 状态
        $this->assign('common_order_admin_status', lang('common_order_admin_status'));

        // 支付状态
        $this->assign('common_order_pay_status', lang('common_order_pay_status'));

        // 快递公司
        $this->assign('express_list', ExpressService::ExpressList());

        // 发起支付 - 支付方式
        $pay_where = [
            'where' => ['is_enable'=>1, 'is_open_user'=>1, 'payment'=>config('under_line_list')],
        ];
        $this->assign('buy_payment_list', PaymentService::BuyPaymentList($pay_where));

        // 支付方式
        $this->assign('payment_list', PaymentService::PaymentList());

        // 评价状态
        $this->assign('common_comments_status_list', lang('common_comments_status_list'));

        // 参数
        $this->assign('params', $params);
        return $this->fetch();
    }

    /**
     * [Delete 订单删除]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Delete()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 删除操作
        $params = input();
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $params['user_type'] = 'admin';
        $ret = OrderService::OrderDelete($params);
        return json($ret);
    }

    /**
     * [Cancel 订单取消]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Cancel()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 取消操作
        $params = input();
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $ret = OrderService::OrderCancel($params);
        return json($ret);
    }

    /**
     * [Delivery 订单发货]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Delivery()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 发货操作
        $params = input();
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $ret = OrderService::OrderDelivery($params);
        return json($ret);
    }

    /**
     * [Collect 订单收货]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Collect()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 收货操作
        $params = input();
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $ret = OrderService::OrderCollect($params);
        return json($ret);
    }

    /**
     * [Confirm 订单确认]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Confirm()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 订单确认
        $params = input();
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $ret = OrderService::OrderConfirm($params);
        return json($ret);
    }

    /**
     * 订单支付
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Pay()
    {
        $params = input();
        $params['user'] = $this->admin;
        $params['user']['user_name_view'] = '管理员'.'-'.$this->admin['username'];
        $ret = OrderService::AdminPay($params);
        return json($ret);
    }
}
?>
<?php


namespace app\api\controller;

use app\admin\model\bespeak\Calendar;
use app\common\controller\Api;

/**
 * 预约接口
 */
class Bespeak extends Api
{

    protected $noNeedLogin = ['*'];

    /**
     * 接口说明
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 号源信息
     * @throws \think\exception\DbException
     * @ApiParams   (name="date", type="integer", required=true, description="日期")
     * @ApiParams   (name="time", type="integer", required=true, description="只取时段")
     * @ApiParams   (name="kind", type="integer", required=true, description="只取类型")

     */
    public function datelist()
    {
        $date = $this->request->request('date',2019);
        $time = $this->request->request('time',0);
        $kind = $this->request->request('kind',0);
        $Y = 0;$m = 0;$d = 0;
        if (strlen($date) == 8){
            $Y = substr($date,0,4);
            $m = substr($date,4,2);
            $d = substr($date,6,2);
        }elseif(strlen($date) == 6){
            $Y = substr($date,0,4);
            $m = substr($date,4,2);
        }elseif(strlen($date) == 4){
            $Y = substr($date,0,4);
        }else{
            $this->error('参数错误');
        }

        $rows = Calendar::getList($Y,$m,$d,$time,$kind);
        $list = array();
        foreach ($rows as $k => $v) {
            $list[$k]['year'] = $v['y'];
            $list[$k]['month'] = $v['m'];
            $list[$k]['day'] = $v['d'];
            $list[$k]['title'] = $v['title'];
            $list[$k]['total'] = $v['total'];
            $list[$k]['ilave'] = $v['ilave'];
        }
        $this->success('', ['date' => $list]);
    }


}
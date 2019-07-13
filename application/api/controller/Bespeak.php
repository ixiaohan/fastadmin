<?php


namespace app\api\controller;

use app\admin\model\bespeak\Booklist;
use app\admin\model\bespeak\Calendar;
use app\admin\model\bespeak\Planlist;
use app\common\controller\Api;
use think\Db;

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
     * 可约日期
     * @throws \think\exception\DbException
     * @ApiParams   (name="date", type="integer", required=true, description="日期")
     * @ApiParams   (name="time", type="integer", required=true, description="只取时段")
     * @ApiParams   (name="kind", type="integer", required=true, description="只取类型")
     */
    public function datelist()
    {
        $date = $this->request->request('date', 201907);

        $Y = 0;
        $m = 0;
        $d = 0;
        if (strlen($date) == 8) {
            $Y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);
        } elseif (strlen($date) == 6) {
            $Y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
        } elseif (strlen($date) == 4) {
            $Y = substr($date, 0, 4);
        } else {
            $this->error('参数错误');
        }

        $kindid = $this->request->request('kindid', 1);
        $timeid = $this->request->request('timeid', 0);

        $rows = Calendar::getInitlist($Y, $m, $d, $kindid, $timeid);


        $dates = array();
        $ables = array();
        foreach ($rows as $k => $v) {
            $mktime = mktime(0, 0, 0, $v['m'], $v['d'], $v['y']);
            if ($kindid == 2) { // 团检
                /**
                 * 获取指定可选日期 enableDate
                 * user_id tuan_id task_id
                 */
                $taskid = 1;
                $planlist = Planlist::getTaskgear($taskid);
                $iable = 0;
                foreach ($planlist as $i => $p) {
                    // $mktime > time() 当天的不能预约
                    if ($mktime >= time() && $mktime >= $p['kaitime'] && $mktime < $p['endtime']) {
                        array_push($ables, "{$v['y']}-{$v['m']}-{$v['d']}");

                        $date = array();
                        $date['year'] = $v['y'];
                        $date['month'] = $v['m'];
                        $date['day'] = $v['d'];
                        $date['title'] = $v['title'];
                        $date['total'] = $v['total'];
                        $date['ilave'] = $v['ilave'];
                        // 余额需要累加
                        $iable += $p['able'];
                        $date['todoText'] = '余' . $iable;
                        array_push($dates, $date);
                    }
                }
            } else {
                $mktime > time() && array_push($ables, "{$v['y']}-{$v['m']}-{$v['d']}");
                $dates[$k]['year'] = $v['y'];
                $dates[$k]['month'] = $v['m'];
                $dates[$k]['day'] = $v['d'];
                $dates[$k]['title'] = $v['title'];
                $dates[$k]['total'] = $v['total'];
                $dates[$k]['ilave'] = $v['ilave'];
                $dates[$k]['todoText'] = '余' . $v['ilave'];
            }

        }
        $this->success('', ['dates' => $dates, 'ables' => $ables]);
    }


    /**
     * 获取指定日期的可预约时间区段
     *
     */
    public function timelist()
    {
        $Y = $this->request->request('Y', 2019);
        $m = $this->request->request('m', 7);
        $d = $this->request->request('d', 15);

        $rows = Calendar::getTimelist($Y, $m, $d, 2);
        $list = array();
        foreach ($rows as $k => $v) {
            $list[$k]['id'] = $v['id'];
            $list[$k]['slot'] = date('H:i', $v['begtime']) . '~' . date('H:i', $v['endtime']);

            $lave = Calendar::getTimegear($Y, $m, $d, 0, $v['id']);
//            $list[$k]['lave'] = $lave['ilave'] . '/' . $lave['total'];
            $list[$k]['lave'] = " 剩余 {$lave['ilave']}";
        }
        $this->success('', ['times' => $list, 'rows' => $rows]);

    }

    /**
     * 计划预定
     * @throws \think\Exception
     */
    public function planbook()
    {
        $Y = $this->request->request('Y', 2019);
        $m = $this->request->request('m', 7);
        $d = $this->request->request('d', 15);

        //user_id pati_id tuan_id task_id

        $kindid = $this->request->request('kindit', 0);
        $timeid = $this->request->request('timeid', 0);
        //beitime endtime

        Booklist::create([
            'y' => $Y, 'm' => $m, 'd' => $d,
            'user_id' => 2,
            'task_id' => 2,
            'tuan_id' => 2,
            'bespeak_kindinit_id' => $kindid,
            'bespeak_timeslot_id' => $timeid,
        ]);
        Db::table('ht_bespeak_calendar')->where(['y' => $Y, 'm' => $m, 'd' => $d, 'bespeak_timeslot_id' => $timeid])->setDec('ilave');

        $rows = Calendar::getTimelist($Y, $m, $d);
        $list = array();
        foreach ($rows as $k => $v) {
            $list[$k]['id'] = $v['id'];
            $list[$k]['slot'] = date('H:i', $v['begtime']) . '~' . date('H:i', $v['endtime']);

            $lave = Calendar::getTimegear($Y, $m, $d, 0, $v['id']);
            $list[$k]['lave'] = " 剩余 {$lave['ilave']}";
        }
        $this->success('', ['times' => $list, 'rows' => $rows]);
    }


}
<?php


namespace app\api\controller;


use app\admin\model\Area;
use app\common\controller\Api;
use think\Db;

class Hospital extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function areas(){
        $plist = Area::where(['level'=>1])->field('id,name as text,lat,lng')->select();
        foreach ($plist as $k=>$v){
            $clist = Area::where(['pid'=>$v['id']])->field('id,name as text,lat,lng')->select();
            $plist[$k]['children'] = $clist;

        }
        $this->success('',$plist);
    }


    /**
     * 实时数据采集
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collect()
    {
        $data = json_decode($_POST['data'], true);

        $poids = [];
        foreach ($data as $item) {
            $has = \app\admin\model\healthman\Hospital::where(['poid'=>$item['id']])->count('id');
            if ($has) {
            } else {
                \app\admin\model\healthman\Hospital::create([
                    'poid'      => $item['id'],
                    'tel'       => $item['tel'],

                    'name'      => $item['title'],
                    'title'     => $item['title'],
                    'category'  => $item['category'],
                    'address'   => $item['address'],

                    'lat'       => $item['location']['lat'],
                    'lng'       => $item['location']['lng'],

                    'adcode'    => $item['ad_info']['adcode'],
                    'province'  =>$item['ad_info']['province'],
                    'city'      =>$item['ad_info']['city'],
                    'district'  =>$item['ad_info']['district']
                ]);
            }
            $poids[] = $item['id'];
        }
        // 返回处理过的数据
        $list = \app\admin\model\healthman\Hospital::where('poid','IN',$poids)->select();
        $this->success(\app\admin\model\healthman\Hospital::getLastSql(),$list);
    }

}
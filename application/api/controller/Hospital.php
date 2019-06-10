<?php


namespace app\api\controller;


use app\admin\model\Area;
use app\admin\model\healthman\Hospview;
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

            // 处理脏的数据
            if(preg_match('/体检科|体检中心|健康管理中心/',$item['title']) == 0)
                continue;

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
        foreach ($list as $item){

            // 处理脏的数据
            $item['visible'] = preg_match('/体检科|体检中心|健康管理中心/',$item['title']);

            // 处理用户表态
            $hospcare = self::hospcare($item['poid']);
            if($hospcare['ibeen'] > $hospcare['never']){
                $item['caretext'] = $hospcare['ibeen'] . ' 的用户去过';
            }elseif($hospcare['ibeen'] < $hospcare['never']){
                $item['caretext'] = $hospcare['never'] . ' 的用户没去过';
            }else{
                $item['caretext'] = 0;
            }
        }
        $this->success('success',$list);
    }

    /**
     * 用户表态数据
     * @param $poid
     * @return array
     * @throws \think\Exception
     */
    private function hospcare($poid){
        $sumer = Hospview::where(['poid'=>$poid])->where('declare','>',0)->count('id') + 2;
        $ibeen = round((Hospview::where(['poid'=>$poid,'declare'=>1])->count('id') +1)/ $sumer,3) *100;
        $never = round((Hospview::where(['poid'=>$poid,'declare'=>2])->count('id') +1)/ $sumer,3) *100;
        $scale = $ibeen;

        return ['ibeen'=>$ibeen.'%','never'=>$never.'%','scale'=>$scale];
    }
}
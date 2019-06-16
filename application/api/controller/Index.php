<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Http;

/**
 * 首页接口
 */
class Index extends Api
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


    /**
     * 医院采集
     * @ApiParams   (name="city", type="string", required=true, description="城市")
     * @ApiParams   (name="page", type="string", required=true, description="页面")
     */
    public function search()
    {
        $city = $this->request->request('city','南京');
        $page = $this->request->request('page',1);
        $url = 'https://apis.map.qq.com/ws/place/v1/search';
        $res = Http::get($url, [
            'key' => 'O3WBZ-UTYR6-QDTS6-EUQYN-D5MCS-KYBVG',
            'filter' => 'category=医疗保健附属',
            'page_size' => 20,
            'page_index' => $page,
            'boundary' => "region({$city},0)",
            'keyword' => '健康管理中心',
        ]);
        $list = json_decode($res, true);
        foreach ($list['data'] as $item) {
            $has = \app\admin\model\healthman\Hospital::where(['poid'=>$item['id']])->count('id');
            if ($has) {
            } else {
//                \app\admin\model\healthman\Hospital::create([
//                    'poid'      => $item['id'],
//                    'tel'       => $item['tel'],
//
//                    'name'      => $item['title'],
//                    'title'     => $item['title'],
//                    'category'  => $item['category'],
//                    'address'   => $item['address'],
//
//                    'lat'       => $item['location']['lat'],
//                    'lng'       => $item['location']['lng'],
//
//                    'adcode'    => $item['ad_info']['adcode'],
//                    'province'  =>$item['ad_info']['province'],
//                    'city'      =>$item['ad_info']['city'],
//                    'district'  =>$item['ad_info']['district']
//
//                ]);
            }
        }

        $this->success('success',['list'=>$list]);
    }
}

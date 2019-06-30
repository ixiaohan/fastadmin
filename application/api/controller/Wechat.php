<?php


namespace app\api\controller;

use addons\third\library\Service;
use addons\third\model\Third;
use app\admin\model\healthman\Hospview;
use app\admin\model\healthman\Hostrace;
use app\api\library\WXBizDataCrypt;
use app\common\controller\Api;
use app\common\library\Auth;
use fast\Http;

class Wechat extends Api
{

    protected $noNeedLogin = ['signin'];
    protected $noNeedRight = '*';

    protected $token = '';


    public function _initialize()
    {
        $this->token = $this->request->post('token');
//        if ($this->request->action() == 'login' && $this->token) {
//            $this->request->post(['token' => '']);
//        }
        parent::_initialize();
    }


    /**
     * 小程序登录
     *
     * @param string $code     Code码
     */
    public function signin(){
        $code = $this->request->post("code");
        if (!$code) {
            $this->error("参数不正确");
        }
        $params = [
            'appid'      => 'wx137af966be8ceeaf',
            'secret'     => '55e9551b0096b63ce30818eba40042ea',
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
        if ($result['ret']) {
            $json = (array)json_decode($result['msg'], true);
            if (isset($json['openid'])) {
                //如果有传Token
                if ($this->token) {
                    $this->auth->init($this->token);
                    //检测是否登录
                    if ($this->auth->isLogin()) {
                        $third = Third::where(['openid' => $json['openid'], 'platform' => 'wxapp'])->find();
                        if ($third && $third['user_id'] == $this->auth->id) {
                            //更新access_token
                            $third->save(['access_token'=>$json['session_key']]);

                            $this->success("登录成功", $this->auth->getUserinfo());
                            $this->success("登录成功", ['userInfo' => $this->auth->getUserinfo(),'third'=>$third]);
                        }
                    }
                }

                $platform = 'wxapp';
                $result = [
                    'openid'        => $json['openid'],
                    'access_token'  => $json['session_key'],
                    'refresh_token' => '',
                    'expires_in'    => isset($json['expires_in']) ? $json['expires_in'] : 0,
                ];
                $ret = Service::connect($platform, $result, $extend=[]);
                if ($ret) {
                    $auth = Auth::instance();
                    $info = $auth->getUserinfo();
                    $this->success("登录成功", $info);
                } else {
                    $this->error("连接失败");
                }
            } else {
                $this->error("登录失败");
            }
        }
        return;
    }

    /**
     * 小程序资料
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function signup(){
        $third = Third::where(['user_id' => $this->auth->id, 'platform' => 'wxapp'])->find();

        $encryptedData = $this->request->request('encryptedData');
        $iv = $this->request->request('iv');

        $wdc = new WXBizDataCrypt('wx137af966be8ceeaf', $third['access_token']);
        $res = $wdc->decryptData($encryptedData, $iv, $json);
        if(0==$res){
            $data = json_decode($json,true);
            $user = $this->auth->getUser();
            $user->save(['avatar'=>$data['avatarUrl'],'nickname'=>$data['nickName']]);

            $third = Third::where(['user_id' => $this->auth->id, 'openid' => $data['openId'], 'platform' => 'wxapp'])->find();
            $third->save(['openname'=>$data['nickName']]);

            /**
             * 追加表态的时候进行授权，授权通过后
             */
            $poid = $this->request->post('poid', 0);
            $declare = $this->request->post('declare', 0);

            if($poid && $declare){
                $view = Hospview::where(['user_id'=>$this->auth->id,'poid'=>$poid])->find();
                $view->declare = $declare;
                $view->save();

                $declare = self::hospcare($poid);

                $this->success('',['declare'=>$declare]);
            }else{
                $this->success('',['declare'=>[]]);
            }
        }

        return;
    }


    /**
     * 用户详情数据
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hospital(){
        $poid = $this->request->post('poid');
        $hospital = \app\admin\model\healthman\Hospital::get(['poid'=>$poid]);


        $has = Hospview::where(['user_id'=>$this->auth->id,'poid'=>$poid])->count('id');
        if($has){}else{
            Hospview::create(['poid'=>$poid,'user_id'=>$this->auth->id]);
        }

        $declare = self::hospcare($poid);

        //量级轨迹
        $hostrace = Hostrace::where(['poid'=>$poid])->order('step','desc')->select();

        $this->success('success',['declare'=>$declare,'hospital'=>$hospital,'hostrace'=>$hostrace]);

    }

    /**
     * 用户表态记录
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hospview(){
        $auth = $this->auth;

        $poid = $this->request->post('poid');
        $declare = $this->request->post('declare');

        $view = Hospview::where(['user_id'=>$this->auth->id,'poid'=>$poid])->find();
        if($view->declare){
            $this->success('已表态');
        }else{
            $view->declare = $declare;
            $view->save();
        }
        $declare = self::hospcare($poid);
        $this->success('',['declare'=>$declare]);
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

        $iview = Hospview::where(['user_id'=>$this->auth->id,'poid'=>$poid])->find();

        return ['ibeen'=>$ibeen.'%','never'=>$never.'%','scale'=>$scale,'iview'=>$iview->declare];

    }
}
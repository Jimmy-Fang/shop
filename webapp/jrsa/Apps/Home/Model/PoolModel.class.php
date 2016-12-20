<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2016/12/19
 * Time: 16:12
 */

namespace Home\Model;


use Think\Model;

class PoolModel extends Model
{
    public function getCapacityInfo(){
        $pool_rows = $this->select();
        //计算总容量
        $total_capacity = 0;
        foreach ($pool_rows as $key => $val ) {
            //总容量
            $total_capacity += $val['maxVolumes']*$val['maxVolumeBytes'];

        }

        //使用容量GB
        $use_capacity= $this->getRonganMediaData();

        if($use_capacity < 0.001){
            $use_capacity = 0.00;
        }
        //使用百分比

        if($total_capacity == 0 ){
            $use_proportion = 100;
        }else{
            $use_proportion=$use_capacity/$total_capacity*100;
            if($use_proportion >= 1 ){
                $use_proportion = 100;
            }
        }
        $total_capacity = sprintf("%.3f", $total_capacity);
        $use_capacity = sprintf("%.3f", $use_capacity);
        $use_proportion = sprintf("%.3f", $use_proportion);
        return [
            'total_capacity'=>$total_capacity,
            'use_capacity' =>$use_capacity,
            'use_proportion'=>$use_proportion
        ];

    }
    public function getRonganMediaData($id){
        $media_rows = $this->db(1,"mysql://root:@localhost:3306/rongan")->query("select * from Media");
        $use_capacity=0;
        foreach ($media_rows as $val ) {
            $use_capacity += $val['VolBytes'];
        }
        $use_capacity=$use_capacity/1024/1024/1024;

        return $use_capacity;
    }

    public function getClientInfo()
    {
        $client = M('Client');
        $client_rows= $client->select();

        $client_status_num['count']=$client->count();
        $client_status_num['open']=0;
        $client_status_num['close']=0;
        foreach ($client_rows as $key => $val){
            $port = $val['fdPort'];
            $ip = $val['address'];
            @exec("sudo nmap -sT  -p $port  -n $ip",$result);
            $result_str = implode($result);
            if(strstr($result_str,$port) && strstr($result_str,'open')){
                $client_rows[$key]['status'] = 1;
                $client_open_rows[] = $client_rows[$key];
                $client_status_num['open']+=1;
            }else{
                $client_rows[$key]['status'] = 0;
                $client_close_rows[] = $client_rows[$key];

                $client_status_num['close']+=1;

            };
        }
        return [
            'client_open_rows'=>$client_open_rows,
            'client_close_rows'=>$client_close_rows,
            'client_status_num'=>$client_status_num,
            ];
    }
}
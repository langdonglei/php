<?php

namespace app\zz\command\ding;

use app\zz\model\OrderM;
use zz\model\TimerM;
use zz\service\DingS;
use Carbon\Carbon;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use Throwable;

class Prize extends Command
{
    protected function configure()
    {
        $this->setName('ding:calc')->setDescription('订单计算佣金');
    }

    protected function execute(Input $input, Output $output)
    {
        $time_start = Carbon::now()->toDateTimeString();
        $dings      = OrderM::where([
            'status'  => OrderM::STATUS_FINISH,
            'calc_is' => 0,
        ])->select();
        $ding_ids   = [];
        foreach ($dings as $ding) {
            Db::startTrans();
            try {
                DingS::calc($ding);
                $ding->save([
                    'calc_is' => 1,
                    'calc_at' => Carbon::now()->toDateTimeString(),
                ]);
                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                throw $e;
            }
            $ding_ids[] = $ding['id'];
        }
        $time_end = Carbon::now()->toDateTimeString();
        TimerM::create([
            'type'       => TimerM::TYPE_DING_FINISH,
            'time_start' => $time_start,
            'time_end'   => $time_end,
            'count'      => count($ding_ids),
            'info'       => json_encode($ding_ids),
        ]);
        $output->write('ok');
    }
}

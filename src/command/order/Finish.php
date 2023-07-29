<?php

namespace app\zz\command\ding;

use app\zz\model\OrderM;
use zz\model\TimerM;
use Carbon\Carbon;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Finish extends Command
{
    protected function configure()
    {
        $this->setName('ding:finish')->setDescription('订单自动完成');
    }

    protected function execute(Input $input, Output $output)
    {
        $time_start = Carbon::now()->toDateTimeString();
        $dings      = OrderM::where([
            'status'  => ['in', [OrderM::STATUS_SEND, OrderM::STATUS_RECEIVE]],
            'send_at' => ['<', Carbon::now()->addDays(7)->toDateTimeString()],
        ])->select();
        $ding_ids   = [];
        foreach ($dings as $ding) {
            $ding->save([
                'status'      => OrderM::STATUS_FINISH,
                'finish_at'   => Carbon::now()->toDateTimeString(),
                'finish_text' => '自动完成'
            ]);
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

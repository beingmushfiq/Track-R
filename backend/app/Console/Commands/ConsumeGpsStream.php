<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDeviceStatus;
use App\Jobs\ProcessGpsData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ConsumeGpsStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:consume {--queue=data : Which queue to consume (data|status)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume raw GPS data or status updates from Redis and dispatch jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('queue');

        if ($type === 'status') {
            $queueName = env('QUEUE_DEVICE_STATUS', 'device:status:updates');
            $jobClass = ProcessDeviceStatus::class;
            $this->info("Starting to consume Device Status from: {$queueName}");
        } else {
            $queueName = env('QUEUE_GPS_DATA', 'gps:data:incoming');
            $jobClass = ProcessGpsData::class;
            $this->info("Starting to consume GPS Data from: {$queueName}");
        }

        $this->info("Waiting for data...");

        while (true) {
            try {
                // Blocking pop with 0 timeout (infinite wait)
                // Returns [key, value]
                $data = Redis::connection()->blpop([$queueName], 0);

                if (!empty($data) && isset($data[1])) {
                    $payload = json_decode($data[1], true);

                    if ($payload) {
                        // Dispatch the job to the standard Laravel queue 'default'
                        // This allows us to scale processing independently from consumption
                        $jobClass::dispatch($payload);

                        if ($this->output->isVerbose()) {
                            $this->info("Processed: " . ($payload['imei'] ?? 'Unknown'));
                        }
                    } else {
                        Log::warning("gps:consume: Invalid JSON received", ['raw' => $data[1]]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("gps:consume error: " . $e->getMessage());
                $this->error("Error: " . $e->getMessage());
                sleep(1); // Prevent tight loop on Redis failure
            }
        }
    }
}

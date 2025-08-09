<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class exchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Döviz Kurları Kaydet';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = 'https://www.tcmb.gov.tr/kurlar/today.xml';

        try {
            $xml = simplexml_load_file($url);
            foreach ($xml->Currency as $currencyElement) {
                $currencyCode = (string)$currencyElement->attributes()->CurrencyCode;

                if ($currencyCode === 'USD' || $currencyCode === 'EUR') {
                    $rate = (float)$currencyElement->BanknoteSelling;

                    ExchangeRate::create([
                        'currency' => $currencyCode,
                        'rate' => $rate,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'is_active' => 1,
                    ]);
                }
            }
        } catch (\Exception $e) {
        }
    }
}

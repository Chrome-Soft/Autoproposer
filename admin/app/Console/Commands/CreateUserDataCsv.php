<?php

namespace App\Console\Commands;

use App\Segment;
use App\UserData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;

class CreateUserDataCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'userdata:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a csv file from all user data';

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
     * @return mixed
     */
    public function handle()
    {
        $data = $this->getUserData();

        $path = storage_path('app/user_data.csv');
        $this->removeFile($path);
        $this->createFile($path);

        $this->writeFile($path, $data['columns'], $data['chunks']);
    }

    protected function getUserData()
    {
        $columns = ['device_is_mobile', 'device_manufacturer', 'device_product', 'browser_name', 'location_country_name', 'location_city_name', 'device_screen_width', 'device_screen_height', 'segment_id'];
        $userData = UserData::all($columns);

        $segments = Segment::all();
        foreach ($userData as $data) {
            $data->segment_id = optional($segments->where('id', $data->segment_id)->first())->sequence;
        }

        return [
            'chunks'    => $userData->chunk(1000)->toArray(),
            'columns'   => $columns
        ];
    }

    protected function removeFile($path)
    {
        try {
            Storage::delete($path);
        } catch(\Exception $ex) {}
    }

    protected function createFile($path)
    {
        try {
            Storage::putAsFile($path);
        } catch (\Exception $ex) {
            Log::error('Error while creating ' . $path);
            Log::error($ex);
        }
    }

    /**
     * @param string $path
     * @param array $columns
     * @param array $chunks
     */
    protected function writeFile(string $path, array $columns, array $chunks): void
    {
        try {
            $csv = new Csv;

            // header
            $csv->save($path, [], false, $columns);
            foreach ($chunks as $chunk) {
                $csv->save($path, $chunk, true, $columns);
            }
        } catch (\Exception $ex) {
            Log::error('Error while writing ' . $path);
            Log::error($ex);
        }
    }
}

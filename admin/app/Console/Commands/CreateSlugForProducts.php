<?php

namespace App\Console\Commands;

use App\Product;
use App\ProductAttribute;
use App\ProductProductAttribute;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ParseCsv\Csv;

class CreateSlugForProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:slug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create slug for products where empty';

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
        $products = Product::all();

        $bar = $this->getOutput()->createProgressBar($products->count());

        foreach ($products as $product) {
            $product->slug = '';
            $product->created_at = Carbon::now();
            $product->save();

            $bar->advance();
        }

        $bar->finish();
    }
}

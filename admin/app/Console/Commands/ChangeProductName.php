<?php

namespace App\Console\Commands;

use App\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Psy\Util\Str;

class ChangeProductName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:changename';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach ($products as $product) {
            $name = $this->getNewName($product->name, 'Felnőtt');
            $this->updateName($product, $name);

            $name = $this->getNewName($product->name, 'Gyerek');
            $this->updateName($product, $name);
        }
    }

    protected function getNewName($name, $searchTerm)
    {
        if (\Illuminate\Support\Str::endsWith($name, "({$searchTerm})")) {
            $pos = strpos($name, "({$searchTerm})");
            return substr($name, 0, $pos - 1);
        }

        return $name;
    }

    protected function updateName($product, $newName)
    {
        if ($product->name !== $newName) {
            $product->name = $newName;
            DB::table('products')
                ->where('id', '=', $product->id)
                ->update(['name' => $newName]);
        }
    }
}

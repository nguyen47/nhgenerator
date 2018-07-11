<?php 
namespace NguyenHoang\NhGenerator;

use Illumiate\Support\ServiceProvider;

class NhGeneratorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->commands(['NhGenerator\Console\Commands\CrudNguyenHoang']);
	}

	public function register()
	{
		
	}
}
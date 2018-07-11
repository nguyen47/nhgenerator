<?php 
namespace NguyenHoang\NhGenerator;

use Illuminate\Support\ServiceProvider;

class NhGeneratorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->commands([Console\Commands\CrudNguyenHoang::class]);
	}

	public function register()
	{
		
	}
}
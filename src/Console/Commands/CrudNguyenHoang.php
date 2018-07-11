<?php

namespace NguyenHoang\NhGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use DB;

class CrudNguyenHoang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {name : Class (singular) for example User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pet projects to create a CRUD simply !!';

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
        $name = $this->argument('name');

        $this->controller($name);
        $this->info('Created '. $name .' Controller !');

        $this->model($name);
        $this->info('Created '. $name .' Model !');

        $this->layout();
        $this->info('Create Layout for Admin');

        $this->indexView($name);
        $this->info('Created View Index !');

        $this->CreateView($name);
        $this->info('Created View Create !');

        $this->editView($name);
        $this->info('Created View Edit !');

        $this->showView($name);
        $this->info('Created View Show !');

        $this->appendRoute($name);
        $this->info('Created '. $name .' Route');
    }

    /**
     * Adding Route in route.php
     * @param  [type] $name Model's Name
     */
    protected function appendRoute($name){
        $search = 'Route::resource(\'' .str_plural(strtolower($name)) . "', '{$name}Controller');";
        $string = file_get_contents(base_path('routes/web.php'));
        $string = explode("\n", $string);
        if(!in_array($search, $string)){
            File::append(base_path('routes/web.php'), 'Route::resource(\'' . str_plural(strtolower($name)) . "', '{$name}Controller');".PHP_EOL);
        }
    }

    /**
     * Get all colums in database
     * @param  [type] $tablename Table's name
     */
    protected function getColumns($tablename) {
        $dbType = DB::getDriverName();
        $cols = DB::select("show columns from " . str_plural($tablename));
        $ret = [];
        foreach ($cols as $c) {
            $field = isset($c->Field) ? $c->Field : $c->field;
            $type = isset($c->Type) ? $c->Type : $c->type;
            $cadd = [];
            $cadd['name'] = $field;
            $cadd['type'] = $field == 'id' ? 'id' : $this->getTypeFromDBType($type);
            $cadd['display'] = ucwords(str_replace('_', ' ', $field));
            $ret[] = $cadd;
        }
        return $ret;
    }


    /**
     * Get type of each colums
     * @param  [type] $dbtype Type's database
     */
    protected function getTypeFromDBType($dbtype) {
        if(str_contains($dbtype, 'varchar')) { return 'text'; }
        if(str_contains($dbtype, 'int') || str_contains($dbtype, 'float')) { return 'number'; }
        if(str_contains($dbtype, 'date')) { return 'date'; }
        return 'unknown';
    }


    /**
     * Render Data with my format
     * @param  String $type Stub's Type
     * @param  String $data Options Data
     */
    protected function renderWithData($type, $data) {
        $template = $this->getStub($type);
        $template = $this->renderForeachs($template, $data);
        $template = $this->renderIFs($template, $data);
        $template = $this->renderVariables($template, $data);
        return $template;
    }

    /**
     * Render Foreach base on format [[foreach]]
     * @param  [type] $template Template from getStub
     * @param  [type] $data     Data of template
     */
    protected function renderForeachs($template, $data) {
        $callback = function ($matches) use($data) {
            $rep = $matches[0];
            $rep = preg_replace('/\[\[\s*foreach:\s*(.+?)\s*\]\](\r?\n)?/s', '', $rep);
            $rep = preg_replace('/\[\[\s*endforeach\s*\]\](\r?\n)?/s', '', $rep);
            $ret = '';
            if(array_key_exists($matches[1], $data) && is_array($data[$matches[1]])) {
                $parent = $data[$matches[1]];
                foreach ($parent as $i) {
                    $d = [];
                    if(is_array($i)) {
                        foreach ($i as $key => $value) {
                            $d['i.'.$key] = $value;
                        }
                    }
                    else {
                        $d['i'] = $i;
                    }
                    $rep2 = $this->renderIFs($rep, array_merge($d, $data));
                    $rep2 = $this->renderVariables($rep2, array_merge($d, $data));
                    $ret .= $rep2;
                }
                return $ret;
            }
            else {
                return $mat;    
            }
            
        };
        $template = preg_replace_callback('/\[\[\s*foreach:\s*(.+?)\s*\]\](\r?\n)?((?!endforeach).)*\[\[\s*endforeach\s*\]\](\r?\n)?/s', $callback, $template);
        return $template;
    }

    /**
     * Render variable
     * @param  [type] $template Template from getStub
     * @param  [type] $data     Data of template
     */
    protected function renderVariables($template, $data) {
        $callback = function ($matches) use($data) {
            if(array_key_exists($matches[1], $data)) {
                return $data[$matches[1]];
            }
            return $matches[0];
        };
        $template = preg_replace_callback('/\[\[\s*(.+?)\s*\]\](\r?\n)?/s', $callback, $template);
        return $template;
    }

    protected function renderIFs($template, $data) {
        $callback = function ($matches) use($data) {
            $rep = $matches[0];
            $rep = preg_replace('/\[\[\s*if:\s*(.+?)\s*([!=]=)\s*(.+?)\s*\]\](\r?\n)?/s', '', $rep);
            $rep = preg_replace('/\[\[\s*endif\s*\]\](\r?\n)?/s', '', $rep);
            $ret = '';
            $val1 = $this->getValFromExpression($matches[1], $data);
            $val2 = $this->getValFromExpression($matches[3], $data);
            if($matches[2] == '==' && $val1 == $val2) { $ret .= $rep; }
            if($matches[2] == '!=' && $val1 != $val2) { $ret .= $rep; }
            
            return $ret;
        };
        $template = preg_replace_callback('/\[\[\s*if:\s*(.+?)\s*([!=]=)\s*(.+?)\s*\]\](\r?\n)?((?!endif).)*\[\[\s*endif\s*\]\](\r?\n)?/s', $callback, $template);
        return $template;
    }

    /**
     * Get Value from Expression
     * @param  [type] $exp  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected function getValFromExpression($exp, $data) {
        if(str_contains($exp, "'")) {
            return trim($exp,"'");    
        }
        else {
            if(array_key_exists($exp, $data)) {
                return $data[$exp];
            }
            else return null;
        }
    }

    /**
     * Load template
     * @param  [type] $type Type of template. Ex: model, controller, index, ...
     * @return [type]       [description]
     */
    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    /**
     * Create Model
     * @param  [type] $name Model's Name
     */
    protected function model($name)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/{$name}.php"), $modelTemplate);
    }

    /**
     * Create Controller
     * @param  [type] $name Model's name
     * @return [type]       [description]
     */
    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }

    /**
     * Create Layout
     */
    protected function layout(){
        $layoutTemplate = $this->getStub('adminLayouts');

        if (!file_exists(base_path().'/resources/views/layouts/')) {
            mkdir(base_path().'/resources/views/layouts/');
        }

        file_put_contents(base_path().'/resources/views/layouts/masterLayout.blade.php', $layoutTemplate);
    }

    /**
     * Create Index View
     * @param  [type] $name Model's Name
     */
    protected function indexView($name){
        $options = [];
        $columns = $this->getColumns(strtolower($name));
        $options['columns'] = $columns;
        $options['first_column_nonid'] = count($columns) > 1 ? $columns[1]['name'] : '';
        $options['num_columns'] = count($columns);

        $dataRendered = $this->renderWithData('index',$options);
        
        if (!file_exists(base_path().'/resources/views/'.strtolower(str_plural($name)))) {
            mkdir(base_path().'/resources/views/'.strtolower(str_plural($name))); 
        }

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/index.blade.php', $dataRendered);

        $indexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            file_get_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/index.blade.php')
        );

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/index.blade.php', $indexTemplate);
    }

    protected function CreateView($name){
        $options = [];
        $columns = $this->getColumns(strtolower($name));
        $options['columns'] = $columns;
        $options['first_column_nonid'] = count($columns) > 1 ? $columns[1]['name'] : '';
        $options['num_columns'] = count($columns);

        $dataRendered = $this->renderWithData('create',$options);
        
        if (!file_exists(base_path().'/resources/views/'.strtolower(str_plural($name)))) {
            mkdir(base_path().'/resources/views/'.strtolower(str_plural($name))); 
        }

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/create.blade.php', $dataRendered);

        $indexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            file_get_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/create.blade.php')
        );

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/create.blade.php', $indexTemplate);
    }

    protected function editView($name){
        $options = [];
        $columns = $this->getColumns(strtolower($name));
        $options['columns'] = $columns;
        $options['first_column_nonid'] = count($columns) > 1 ? $columns[1]['name'] : '';
        $options['num_columns'] = count($columns);

        $dataRendered = $this->renderWithData('edit',$options);
        
        if (!file_exists(base_path().'/resources/views/'.strtolower(str_plural($name)))) {
            mkdir(base_path().'/resources/views/'.strtolower(str_plural($name))); 
        }

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/edit.blade.php', $dataRendered);

        $indexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            file_get_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/edit.blade.php')
        );

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/edit.blade.php', $indexTemplate);
    }  


    protected function showView($name){
        $options = [];
        $columns = $this->getColumns(strtolower($name));
        $options['columns'] = $columns;
        $options['first_column_nonid'] = count($columns) > 1 ? $columns[1]['name'] : '';
        $options['num_columns'] = count($columns);

        $dataRendered = $this->renderWithData('show',$options);
        
        if (!file_exists(base_path().'/resources/views/'.strtolower(str_plural($name)))) {
            mkdir(base_path().'/resources/views/'.strtolower(str_plural($name))); 
        }

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/show.blade.php', $dataRendered);

        $indexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            file_get_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/show.blade.php')
        );

        file_put_contents(base_path().'/resources/views/'.strtolower(str_plural($name)).'/show.blade.php', $indexTemplate);
    }    
}

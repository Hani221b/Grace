<?php

namespace Hani221b\Grace\Helpers;

use Hani221b\Grace\Helpers\FactoryHelpers\makeModelAliveHelper;
use Illuminate\Support\Pluralizer;

class MakeStubsAliveHelper
{
    /**
     * Return the Singular Capitalize Name
     * @param $class_name
     * @return string
     */
    public static function getSingularClassName($class_name)
    {
        return ucwords(Pluralizer::singular($class_name));
    }

    /**
     * Return the PLural Lower Case Name
     * @param $table_name
     * @return string
     */
    public static function getPluralLowerName($table_name)
    {
        return strtolower(Pluralizer::plural($table_name));
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public static function getSourceFilePath($namespace, $class_name, $suffix)
    {
        return base_path($namespace) . '\\' . self::getSingularClassName($class_name) . $suffix . '.php';
    }

    /**
     * Get the full path of generate migration class
     *
     * @return string
     */
    public static function getMigrationSourceFilePath($namespace, $table_name)
    {
        return base_path($namespace) . '\\' . date("Y_m_d") . "_" . $_SERVER['REQUEST_TIME']
        . "_create_" . self::getPluralLowerName($table_name) . "_table" . '.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  object  $files
     * @param  string  $path
     * @return string
     */
    public static function makeDirectory($files, $path)
    {
        if (!$files->isDirectory($path)) {
            $files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public static function getStubPath($type)
    {
        return __DIR__ . "/../Stubs/{$type}.stub";
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public static function getSourceFile($StubVariables, $type)
    {
        return self::getStubContents(MakeStubsAliveHelper::getStubPath($type), $StubVariables);
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public static function getMigrationSourceFile($StubVariables, $type)
    {
        return self::getMigrationStubContents(MakeStubsAliveHelper::getStubPath($type), $StubVariables);
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public static function getModelSourceFile($StubVariables, $type)
    {
        return self::getModelStubContents(MakeStubsAliveHelper::getStubPath($type), $StubVariables);
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public static function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {

            $contents = str_replace('{{ ' . $search . ' }}', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public static function getModelStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        $string_mutators_template = makeModelAliveHelper::appendMutatorToModel($stubVariables);

        $contents = str_replace('{{ mutatators }}', $string_mutators_template, $contents);

        foreach ($stubVariables as $search => $replace) {

            $contents = str_replace('{{ ' . $search . ' }}', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public static function getMigrationStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        $field_names = $stubVariables['field_names'];
        $field_types = $stubVariables['field_types'];

        $tabels_types_and_names = array_combine($field_names, $field_types);

        unset($stubVariables["field_names"]);
        unset($stubVariables["field_types"]);

        $template = array();

        foreach ($tabels_types_and_names as $key => $value) {
            array_push($template, "table->$value('$key')");
        }
        $tables_template = '';
        foreach ($template as $index => $tem) {
            $tables_template .= "$" . $template[$index] . ";" . "\n";
        }

        $contents = str_replace('{{ content }}', $tables_template, $contents);

        foreach ($stubVariables as $search => $replace) {

            $contents = str_replace('{{ ' . $search . ' }}', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Combine fields names and files fileds value to return files fillable array
     * @return array
     */
    public static function files_fillable_array($field_names, $files_fields)
    {
        $files_fillable_array = [];
        if($field_names !== null && $files_fields !== null){
            $files_array = array_combine($field_names, $files_fields);
            foreach ($files_array as $field_name => $file_filed) {
                if ($file_filed === '1') {
                    array_push($files_fillable_array, $field_name);
                }
            }
        }
        return implode(",", $files_fillable_array);
    }

    /**
     * Mapping the value of field names and filter files fields
     * @return string
     */

    public static function fillable_array($field_names, $files_fields)
    {
        $fillable_array = [];
        $all_fields = array_combine($field_names, $files_fields);
        foreach ($all_fields as $name => $value) {
            if ($value === "0") {
                array_push($fillable_array, $name);
            }
        }
        return "'" . str_replace(",", "', '", implode(",", $fillable_array)) . "'";
    }


    /**
     * Map the values of isFile checkbox
     * @param Illuminate\Http\Request
     * @return array
     */
    public static function isFileValues($request)
    {
        $files_fields = $request->isFile;
        if($files_fields !== null){
            foreach ($files_fields as $key => $value) {
                if ($value === '1') {
                    unset($files_fields[$key + 1]);
                }
            }
        }
        return $files_fields;
    }

    /**
     * Put the requested data inside the files have just created
     *
     * @param $files
     * @param string $path
     * @param string $contents
     * @return bool|mixed|string
     */
    public static function putFilesContent($files, $path, $contents)
    {
        if (!$files->exists($path)) {
            $files->put($path, $contents);
            // $this->info("File : {$path} created");
            return "File : {$path} created";
        } else {
            // $this->info("File : {$path} already exits");
            return "File : {$path} already exits";
        }
    }
}

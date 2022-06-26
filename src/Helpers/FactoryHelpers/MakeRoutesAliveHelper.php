<?php

namespace Hani221b\Grace\Helpers\FactoryHelpers;

use Hani221b\Grace\Helpers\MakeStubsAliveHelper;

class MakeRoutesAliveHelper 
{

    /**
     * append use controller statement at the to of the route file
     * @param @stubVariables
     * @param @controller_name
     * @return void
     */
    public static function appendUseController($stubVariables = [], $controller_name){
        $controller_namespace = $stubVariables['controller_namespace'];
        $use_controller = "use $controller_namespace\\$controller_name;";
        $filename = base_path() . '\routes\web.php';
        $line_i_am_looking_for = 1;
        $lines = file( $filename , FILE_IGNORE_NEW_LINES );
        $lines[$line_i_am_looking_for] = "\n".$use_controller;
        file_put_contents( $filename , implode( "\n", $lines ) );
    }
    /**
     * append resource routes for a certain table
     * @param $stubVariables
     * @return void
     */

    public static function appendRoutes($stubVariables = [])
    {
        $routes_file = base_path() . '\routes\web.php';
        $opened_file  = fopen($routes_file, 'a');
        $controller_name = MakeStubsAliveHelper::getSingularClassName($stubVariables['table_name']) . "Controller";
        $table_name = $stubVariables['table_name'];
        $routes_template = "
// $table_name
Route::resource('$table_name', $controller_name::class);
        ";
 
        self::appendUseController($stubVariables, $controller_name);
        fwrite($opened_file, $routes_template);
        fclose($opened_file);
    }
}
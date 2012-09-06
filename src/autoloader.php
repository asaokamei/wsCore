<?php

spl_autoload_register(
    function( $c ) {
        $path = __DIR__ . '/' . strtr( $c, '\\_', '//' ).'.php';
        @include_once $path;
    }
);


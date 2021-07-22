<?php

use Trienlace\ParseContribuyente\ParseContribuyente;

test('parse contribuyente natural', function () {
    $archivo = file_get_contents(__DIR__ . '/html/natural.html');

    $parser = new ParseContribuyente($archivo);

    $contribuyente = $parser->toArray();

    expect($contribuyente)
        ->toBeArray()
        ->rif->toEqual('V058924640')
        ->razon_social->toEqual('NICOLAS MADURO MOROS')
        ->nombre_comercial->toEqual('NICOLAS MADURO MOROS')
        ->firma_personal->toHaveCount(3)
        ->actividad_economica->toEqual('INFORMACION NO DISPONIBLE')
        ->registro_vencido->toEqual(false);
});

test('parse contribuyente juridico', function () {
    $archivo = file_get_contents(__DIR__ . '/html/juridico.html');

    $parser = new ParseContribuyente($archivo);

    $contribuyente = $parser->toArray();

    expect($contribuyente)
        ->toBeArray()
        ->rif->toEqual('J500903251')
        ->razon_social->toEqual('TRIENLACE, C.A')
        ->nombre_comercial->toEqual('TRIENLACE, C.A')
        ->firma_personal->toHaveCount(0)
        ->actividad_economica->toEqual('VENTA AL POR MAYOR DE OTROS PRODUCTOS NO ESPECIALIZADOS')
        ->registro_vencido->toEqual(false);
});


test('parse contribuyente gubernamental', function () {
    $archivo = file_get_contents(__DIR__ . '/html/gubernamental.html');

    $parser = new ParseContribuyente($archivo);

    $contribuyente = $parser->toArray();

    expect($contribuyente)
        ->toBeArray()
        ->rif->toEqual('G200003030')
        ->razon_social->toEqual('SERVICIO NACIONAL INTEGRADO DE ADMINISTRACION ADUANERA Y TRIBUTARIA')
        ->nombre_comercial->toEqual('SERVICIO NACIONAL INTEGRADO DE ADMINISTRACION ADUANERA Y TRIBUTARIA')
        ->firma_personal->toHaveCount(0)
        ->actividad_economica->toEqual('INFORMACION NO DISPONIBLE')
        ->registro_vencido->toEqual(false);
});

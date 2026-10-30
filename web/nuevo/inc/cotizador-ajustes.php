<?php
/**
 * Ajustes del cotizador. Todo lo que el cliente podría querer cambiar sin
 * tocar código está acá.
 *
 * Los datos de contacto son los que Dekarter usaba en sus cotizaciones en
 * Excel, como pidió el cliente, con dos correcciones:
 *   - el fono fijo pasa del formato viejo «02-24011152» al actual;
 *   - el celular «65781377» del encabezado del Excel se omite: tiene 8
 *     dígitos, así que le falta uno. Los celulares que se usan son los de la
 *     firma del mismo Excel. CONFIRMAR con el cliente.
 */

declare(strict_types=1);

$COT = [
    'empresa' => [
        'razon_social' => 'Maestranza Dekarter SpA',
        'bajada'       => 'Ingeniería y servicio',
        'rut'          => '76.102.503-1',
        'direccion'    => 'Dos Sur 5010, Conchalí',
        'ciudad'       => 'Santiago',
        'fono'         => '+56 2 2401 1152',
        'celulares'    => '+56 9 3903 9509 / +56 9 4271 3267',
        'email'        => 'maestranza.dekarter@gmail.com',
        'web'          => 'dekarter.cl',
    ],

    // Firma al pie de cada cotización
    'firma' => [
        'nombre' => 'Administración Dekarter',
        'email'  => 'maestranza.dekarter@gmail.com',
        'fono'   => 'Cel. +56 9 3903 9509 / +56 9 4271 3267',
    ],

    // Correlativo: la última cotización hecha en Excel fue la 3461-26.
    // El número sigue corriendo; el sufijo es el año de emisión (26, 27…).
    'numero_inicial' => 3462,

    'iva' => 0.19,

    // Valores que se proponen al crear una cotización nueva
    'por_defecto' => [
        'validez'  => '10 días',
        'pago'     => '30 días',
        'guia'     => 'N/A',
        'entrega'  => '20 días hábiles',
    ],

    // Libreta de clientes inicial, tomada de la hoja «Clientes» del Excel.
    // Después crece sola: cada cliente nuevo de una cotización queda guardado.
    'clientes_iniciales' => [
        ['rut' => '79.538.350-6', 'razon' => 'Constructora Gardilcic Ltda.',
         'direccion' => 'Américo Vespucio 2880, piso 12', 'atencion' => 'Aldo Ripetti', 'giro' => 'Maquinarias'],
        ['rut' => '79.963.260-8', 'razon' => 'Minera Las Cenizas S.A.',
         'direccion' => 'Avenida Humeres 1501', 'atencion' => 'Roberto Gómez',
         'giro' => 'Explotación y exploración minera'],
    ],

    'max_fotos'   => 24,
    'max_items'   => 60,
];

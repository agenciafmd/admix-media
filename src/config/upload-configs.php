<?php

/*
 * Arquivo de exemplo para configuração dos uploads
 * Cada pacote que possuir upload, deve conter o seu,
 * respeitando o nome da model
 * */

//return [
//    'customer' => [ //nome da model em minusculo
//        'label' => 'imagem', //label do campo
//        'multiple' => false, //se permite o upload multiplo
//        'faker_dir' => false, #database_path('faker/customer/image'),
//        'image' => [ //nome do campo
//            'sources' => [
//                [
//                    'conversion' => 'min-width-1366',
//                    'media' => '(min-width: 1366px)',
//                    'width' => 938,
//                    'height' => 680,
//                    'optimize' => app()->environment(['local', 'testing']) ? false : true,
//                    'quality' => app()->environment(['local', 'testing']) ? 75 : 100,
//                ],
//                [
//                    'conversion' => 'min-width-1280',
//                    'media' => '(min-width: 1280px)',
//                    'width' => 776,
//                    'height' => 565,
//                    'optimize' => app()->environment(['local', 'testing']) ? false : true,
//                    'quality' => app()->environment(['local', 'testing']) ? 75 : 100,
//                ],
//            ],
//        ],
//        'images' => [
//        'label' => 'imagens', //label do campo
//        'faker_dir' => false, #database_path('faker/customer/images'),
//        'multiple' => true, //se permite o upload multiplo
//            'sources' => [
//                [
//                    'conversion' => 'min-width-1366',
//                    'media' => '(min-width: 1366px)',
//                    'width' => 400,
//                    'height' => 400,
//                    'optimize' => app()->environment(['local', 'testing']) ? false : true,
//                    'quality' => app()->environment(['local', 'testing']) ? 75 : 100,
//                ],
//                [
//                    'conversion' => 'min-width-1280',
//                    'media' => '(min-width: 1280px)',
//                    'width' => 300,
//                    'height' => 300,
//                    'optimize' => app()->environment(['local', 'testing']) ? false : true,
//                    'quality' => app()->environment(['local', 'testing']) ? 75 : 100,
//                ],
//            ],
//        ],
//    ],
//];

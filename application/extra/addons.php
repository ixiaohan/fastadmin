<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'testhook' => 
    array (
      0 => 'bespeak',
      1 => 'hospital',
    ),
    'user_sidenav_after' => 
    array (
      0 => 'bespeak',
      1 => 'signin',
    ),
    'upload_config_init' => 
    array (
      0 => 'ucloud',
    ),
    'upload_delete' => 
    array (
      0 => 'ucloud',
    ),
  ),
  'route' => 
  array (
    '/example$' => 'example/index/index',
    '/example/d/[:name]' => 'example/demo/index',
    '/example/d1/[:name]' => 'example/demo/demo1',
    '/example/d2/[:name]' => 'example/demo/demo2',
    '/third$' => 'third/index/index',
    '/third/connect/[:platform]' => 'third/index/connect',
    '/third/callback/[:platform]' => 'third/index/callback',
  ),
);
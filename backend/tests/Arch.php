<?php

arch('controllers have the controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('requests extend form request')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('resources have the resource suffix')
    ->expect('App\Http\Resources')
    ->toHaveSuffix('Resource');

arch('base resource extends json resource')
    ->expect('App\Http\Resources\BaseResource')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');

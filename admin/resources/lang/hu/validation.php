<?php

return [
    'custom' => [
        'name' => [
            'required'  => 'Név megadása kötelező',
            'unique'    => 'Már létezik ilyen szegmens'
        ],
        'groups' => [
            'required'  => 'Legalább egy csoport kötelező'
        ],
        'url'   => [
            'required'  => 'URL megadása kötelező'
        ],
        'groups.*.criterias.required'               => 'Kérlek válassz egy kritériumot',
        'groups.*.criterias.*.criteria.required'    => 'Kérlek válassz egy kritériumot',
        'groups.*.criterias.*.criteria.exists'      => 'Kérlek válassz egy kritériumot',
        'groups.*.criterias.*.relation.required'    => 'Kérlek válassz egy relációt',
        'groups.*.criterias.*.relation.exists'      => 'Kérlek válassz egy relációt',
        'groups.*.criterias.*.value.required_if'    => 'Kérlek adj meg egy értéket'
    ]
];
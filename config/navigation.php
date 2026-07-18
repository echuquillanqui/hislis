<?php

return [
    [
        'title' => 'Dashboard',
        'icon' => 'fa-solid fa-chart-line',
        'active' => ['home', 'dashboard.management.*'],
        'children' => [
            ['title' => 'Resumen general', 'route' => 'home', 'permission' => null, 'active' => ['home'], 'icon' => 'fa-solid fa-house'],
            ['title' => 'Dashboard gerencial', 'route' => 'dashboard.management.index', 'permission' => 'dashboard.management.view', 'active' => ['dashboard.management.*'], 'icon' => 'fa-solid fa-chart-pie'],
        ],
    ],
    [
        'title' => 'Laboratorio',
        'icon' => 'fa-solid fa-microscope',
        'active' => ['attentions.*', 'triage.*'],
        'children' => [
            ['title' => 'Monitor global', 'route' => 'attentions.index', 'permission' => null, 'active' => ['attentions.*'], 'icon' => 'fa-solid fa-desktop'],
            ['title' => 'Procesamiento / triaje', 'route' => 'triage.index', 'permission' => null, 'active' => ['triage.*'], 'icon' => 'fa-solid fa-vial-circle-check'],
        ],
    ],
    [
        'title' => 'Pacientes y clientes',
        'icon' => 'fa-solid fa-hospital-user',
        'active' => ['patients.*', 'vouchers.*'],
        'children' => [
            ['title' => 'Pacientes', 'route' => 'patients.index', 'permission' => null, 'active' => ['patients.*'], 'icon' => 'fa-solid fa-user-plus'],
            ['title' => 'Órdenes / vouchers', 'route' => 'vouchers.index', 'permission' => null, 'active' => ['vouchers.*'], 'icon' => 'fa-solid fa-file-invoice-dollar'],
            ['title' => 'Nueva orden', 'route' => 'vouchers.create', 'permission' => null, 'active' => ['vouchers.create'], 'icon' => 'fa-solid fa-plus-circle'],
        ],
    ],
    [
        'title' => 'Catálogo de laboratorio',
        'icon' => 'fa-solid fa-flask-vial',
        'active' => ['areas.*', 'specialty_labs.*', 'lab_exams.*', 'bundles.*', 'services.*'],
        'children' => [
            ['title' => 'Áreas', 'route' => 'areas.index', 'permission' => 'areas.manage', 'active' => ['areas.*'], 'icon' => 'fa-solid fa-layer-group'],
            ['title' => 'Especialidades', 'route' => 'specialty_labs.index', 'permission' => 'catalogs.manage', 'active' => ['specialty_labs.*'], 'icon' => 'fa-solid fa-microscope'],
            ['title' => 'Exámenes', 'route' => 'lab_exams.index', 'permission' => 'catalogs.manage', 'active' => ['lab_exams.*'], 'icon' => 'fa-solid fa-list-check'],
            ['title' => 'Perfiles / paquetes', 'route' => 'bundles.index', 'permission' => null, 'active' => ['bundles.*'], 'icon' => 'fa-solid fa-layer-group'],
            ['title' => 'Asignación de plantillas', 'route' => 'services.index', 'permission' => null, 'active' => ['services.*'], 'icon' => 'fa-solid fa-concierge-bell'],
        ],
    ],
    [
        'title' => 'Plantillas',
        'icon' => 'fa-solid fa-file-medical',
        'active' => ['templates.*'],
        'children' => [
            ['title' => 'Plantillas de exámenes', 'route' => 'templates.index', 'permission' => null, 'active' => ['templates.index', 'templates.edit', 'templates.preview'], 'icon' => 'fa-solid fa-file-medical'],
            ['title' => 'Crear plantilla', 'route' => 'templates.create', 'permission' => 'templates.create', 'active' => ['templates.create'], 'icon' => 'fa-solid fa-file-circle-plus'],
        ],
    ],
    [
        'title' => 'Inventario',
        'icon' => 'fa-solid fa-boxes-stacked',
        'active' => ['inventory.*', 'monthly-inventory-counts.*'],
        'children' => [
            ['title' => 'Resumen de inventario', 'route' => 'inventory.index', 'permission' => 'inventory.manage', 'active' => ['inventory.*'], 'icon' => 'fa-solid fa-warehouse'],
            ['title' => 'Productos y materiales', 'route' => 'inventory.index', 'fragment' => 'productos', 'permission' => 'inventory.manage', 'active' => ['inventory.*'], 'icon' => 'fa-solid fa-box-open'],
            ['title' => 'Lotes', 'route' => 'inventory.index', 'fragment' => 'lotes', 'permission' => 'inventory.manage', 'active' => ['inventory.*'], 'icon' => 'fa-solid fa-vials'],
            ['title' => 'Kardex', 'route' => 'inventory.index', 'fragment' => 'kardex', 'permission' => 'inventory.manage', 'active' => ['inventory.*'], 'icon' => 'fa-solid fa-clipboard-list'],
            ['title' => 'Cierres mensuales', 'route' => 'monthly-inventory-counts.index', 'permission' => 'inventory.manage', 'active' => ['monthly-inventory-counts.*'], 'icon' => 'fa-solid fa-calendar-check'],
        ],
    ],
    [
        'title' => 'Administración',
        'icon' => 'fa-solid fa-gears',
        'active' => ['users.*', 'roles.*', 'settings.*'],
        'children' => [
            ['title' => 'Usuarios', 'route' => 'users.index', 'permission' => 'users.manage', 'active' => ['users.*'], 'icon' => 'fa-solid fa-users'],
            ['title' => 'Roles y permisos', 'route' => 'roles.index', 'permission' => 'roles.manage', 'active' => ['roles.*'], 'icon' => 'fa-solid fa-shield-halved'],
            ['title' => 'Configuración general', 'route' => 'settings.index', 'permission' => null, 'active' => ['settings.*'], 'icon' => 'fa-solid fa-gears'],
        ],
    ],
];

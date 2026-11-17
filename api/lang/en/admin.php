<?php

return [
    'resources' => [
        'users' => [
            'label' => 'Users',
            'singular' => 'User',
        ],
        'categories' => [
            'label' => 'Categories',
            'singular' => 'Category',
        ],
        'products' => [
            'label' => 'Products',
            'singular' => 'Product',
        ],
    ],
    'fields' => [
        'name' => 'Name',
        'email' => 'Email address',
        'phone' => 'Phone',
        'password' => 'Password',
        'roles' => 'Roles',
        'email_verified_at' => 'Email verified at',
        'created_at' => 'Created at',
        'updated_at' => 'Last updated at',
        'slug' => 'Slug',
        'parent' => 'Parent category',
        'description' => 'Description',
        'arabic' => 'Arabic',
        'english' => 'English',
        'name_ar' => 'Name (Arabic)',
        'name_en' => 'Name (English)',
        'image' => 'Image',
        'sort_order' => 'Sort order',
        'is_active' => 'Active',
        'is_rental_only' => 'Rental only',
        'sku' => 'SKU',
        'category' => 'Category',
        'short_description' => 'Short description',
        'price_baisa' => 'Price (baisa)',
        'price_help' => '1 OMR = 1000 baisa. Leave empty if not for sale.',
        'price' => 'Price',
        'weight_grams' => 'Weight (grams)',
        'is_rentable' => 'Rentable',
        'is_featured' => 'Featured',
        'primary_image' => 'Image',
    ],
    'sections' => [
        'basic' => 'Basic information',
        'pricing' => 'Pricing',
        'flags' => 'Flags',
    ],
    'actions' => [
        'duplicate' => 'Duplicate',
        'duplicated' => 'Product duplicated as an inactive draft',
    ],
];

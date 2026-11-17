<?php

return [
    'resources' => [
        'users' => [
            'label' => 'المستخدمون',
            'singular' => 'مستخدم',
        ],
        'categories' => [
            'label' => 'التصنيفات',
            'singular' => 'تصنيف',
        ],
        'products' => [
            'label' => 'المنتجات',
            'singular' => 'منتج',
        ],
    ],
    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'password' => 'كلمة المرور',
        'roles' => 'الأدوار',
        'email_verified_at' => 'تاريخ تأكيد البريد الإلكتروني',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ آخر تحديث',
        'slug' => 'المعرّف في الرابط',
        'parent' => 'التصنيف الأب',
        'description' => 'الوصف',
        'arabic' => 'العربية',
        'english' => 'الإنجليزية',
        'name_ar' => 'الاسم (عربي)',
        'name_en' => 'الاسم (إنجليزي)',
        'image' => 'الصورة',
        'sort_order' => 'ترتيب العرض',
        'is_active' => 'نشط',
        'is_rental_only' => 'للإيجار فقط',
        'sku' => 'رمز المنتج',
        'category' => 'التصنيف',
        'short_description' => 'وصف مختصر',
        'price_baisa' => 'السعر (بيسة)',
        'price_help' => '1 ريال عماني = 1000 بيسة. اتركه فارغاً إذا لم يكن للبيع.',
        'price' => 'السعر',
        'weight_grams' => 'الوزن (غرام)',
        'is_rentable' => 'متاح للإيجار',
        'is_featured' => 'مميز',
        'primary_image' => 'الصورة',
    ],
    'sections' => [
        'basic' => 'المعلومات الأساسية',
        'pricing' => 'التسعير',
        'flags' => 'الخيارات',
    ],
    'actions' => [
        'duplicate' => 'نسخ',
        'duplicated' => 'تم نسخ المنتج كمسودة غير نشطة',
    ],
];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'date' => 'يجب أن يكون :attribute تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون :attribute تاريخًا مساويًا لـ :date.',
    'date_format' => 'يجب أن يتطابق :attribute مع التنسيق :format.',
    'decimal' => 'يجب أن يحتوي :attribute على :decimal أماكن عشرية.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يحتوي :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي :attribute على بين :min و :max رقمًا.',
    'dimensions' => 'يحتوي :attribute على أبعاد صورة غير صالحة.',
    'distinct' => 'يحتوي :attribute على قيمة مكررة.',
    'doesnt_end_with' => 'يجب ألا ينتهي :attribute بأحد القيم التالية: :values.',
    'doesnt_start_with' => 'يجب ألا يبدأ :attribute بأحد القيم التالية: :values.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالحًا.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'exists' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'extensions' => 'يجب أن يحتوي :attribute على إحدى الامتدادات التالية: :values.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي :attribute على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عنصرًا.',
        'file' => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'string' => 'يجب أن يكون :attribute أكبر من :value حرفًا.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي :attribute على :value عنصرًا أو أكثر.',
        'file' => 'يجب أن يكون :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'string' => 'يجب أن يكون :attribute أكبر من أو يساوي :value حرفًا.',
    ],
    'hex_color' => 'يجب أن يكون :attribute لونًا سداسي عشري صالحًا.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'in_array' => 'يجب أن يكون :attribute موجودًا في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صالحًا.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صالحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صالحًا.',
    'json' => 'يجب أن يكون :attribute نص JSON صالحًا.',
    'lowercase' => 'يجب أن يكون :attribute بحروف صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عنصرًا.',
        'file' => 'يجب أن يكون :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'string' => 'يجب أن يكون :attribute أقل من :value حرفًا.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :value عنصرًا.',
        'file' => 'يجب أن يكون :attribute أقل من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'string' => 'يجب أن يكون :attribute أقل من أو يساوي :value حرفًا.',
    ],
    'mac_address' => 'يجب أن يكون :attribute عنوان MAC صالحًا.',
    'max' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصرًا.',
        'file' => 'يجب ألا يكون :attribute أكبر من :max كيلوبايت.',
        'numeric' => 'يجب ألا يكون :attribute أكبر من :max.',
        'string' => 'يجب ألا يكون :attribute أكبر من :max حرفًا.',
    ],
    'max_digits' => 'يجب ألا يحتوي :attribute على أكثر من :max أرقام.',
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عنصرًا.',
        'file' => 'يجب أن يكون :attribute على الأقل :min كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'string' => 'يجب أن يكون :attribute على الأقل :min حرفًا.',
    ],
    'min_digits' => 'يجب أن يحتوي :attribute على الأقل على :min أرقام.',
    'missing' => 'يجب أن يكون :attribute مفقودًا.',
    'missing_if' => 'يجب أن يكون :attribute مفقودًا عندما يكون :other هو :value.',
    'missing_unless' => 'يجب أن يكون :attribute مفقودًا ما لم يكن :other هو :value.',
    'missing_with' => 'يجب أن يكون :attribute مفقودًا عندما يكون :values موجودًا.',
    'missing_with_all' => 'يجب أن يكون :attribute مفقودًا عندما تكون :values موجودة.',
    'multiple_of' => 'يجب أن يكون :attribute مضاعفًا لـ :value.',
    'not_in' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'not_regex' => 'تنسيق :attribute غير صالح.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'تم العثور على :attribute في تسريب بيانات. يرجى اختيار كلمة مرور مختلفة.',
    ],
    'present' => 'يجب أن يكون :attribute موجودًا.',
    'present_if' => 'يجب أن يكون :attribute موجودًا عندما يكون :other هو :value.',
    'present_unless' => 'يجب أن يكون :attribute موجودًا ما لم يكن :other هو :value.',
    'present_with' => 'يجب أن يكون :attribute موجودًا عندما يكون :values موجودًا.',
    'present_with_all' => 'يجب أن يكون :attribute موجودًا عندما تكون :values موجودة.',
    'prohibited' => 'يُحظر استخدام :attribute.',
    'prohibited_if' => 'يُحظر استخدام :attribute عندما يكون :other هو :value.',
    'prohibited_unless' => 'يُحظر استخدام :attribute ما لم يكن :other في :values.',
    'prohibits' => ':attribute يمنع :other من الوجود.',
    'regex' => 'تنسيق :attribute غير صالح.',
    'required' => ':attribute مطلوب.',
    'required_array_keys' => 'يجب أن يحتوي :attribute على مفاتيح: :values.',
    'required_if' => ':attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => ':attribute مطلوب عندما يتم قبول :other.',
    'required_unless' => ':attribute مطلوب ما لم يكن :other في :values.',
    'required_with' => ':attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all' => ':attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => ':attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => ':attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي :attribute على :size عنصرًا.',
        'file' => 'يجب أن يكون :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute :size.',
        'string' => 'يجب أن يحتوي :attribute على :size حرفًا.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'timezone' => 'يجب أن يكون :attribute منطقة زمنية صالحة.',
    'unique' => 'تم بالفعل استخدام :attribute.',
    'uploaded' => 'فشل تحميل :attribute.',
    'uppercase' => 'يجب أن يكون :attribute بحروف كبيرة.',
    'url' => 'يجب أن يكون :attribute رابط URL صالحًا.',
    'ulid' => 'يجب أن يكون :attribute معرف ULID صالحًا.',
    'uuid' => 'يجب أن يكون :attribute معرف UUID صالحًا.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // Basic Information
        'name' => 'الاسم',
        'name-en' => 'الاسم بالإنجليزية',
        'name-ar' => 'الاسم بالعربية',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'role' => 'الدور',

        // Descriptions and Addresses
        'description-en' => 'الوصف بالإنجليزية',
        'description-ar' => 'الوصف بالعربية',
        'address-en' => 'العنوان بالإنجليزية',
        'address-ar' => 'العنوان بالعربية',

        // Contact and Location Information
        'phone-number' => 'رقم الهاتف',
        'longitude' => 'خط الطول',
        'latitude' => 'خط العرض',

        // Business and Website Information
        'google-map-url' => 'رابط خرائط جوجل',
        'website' => 'الموقع الإلكتروني',
        'business_status' => 'حالة العمل',
        'price-level' => 'مستوى السعر',

        // Images and Media
        'image' => 'الصورة',
        'main-image' => 'الصورة الرئيسية',
        'gallery-images' => 'صور المعرض',

        // Ratings and User Feedback
        'rating' => 'التقييم',
        'total-user-rating' => 'إجمالي تقييم المستخدم',

        // Categories and Tags
        'sub_category_id' => 'الفئة الفرعية',
        'tags-id' => 'العلامات',
        'region-id' => 'المنطقة',

        // Miscellaneous
        'subject' => 'الموضوع',
        'message' => 'الرسالة',

        'description'                               => 'الوصف',
        'days'                                      => 'أيام الخطة',
        'days.*.activities'                         => 'الأنشطة',
        'days.*.activities.*.name'                  => 'اسم النشاط',
        'days.*.activities.*.start_time'            => 'وقت بدء النشاط',
        'days.*.activities.*.end_time'              => 'وقت انتهاء النشاط',
        'days.*.activities.*.place_slug'            => 'مكان النشاط',
        'days.*.activities.*.note'                  => 'ملاحظة النشاط',
    ],

    'msg' => [
        // Admin Actions
        "admin-updated-successfully" => 'تم تحديث المشرف بنجاح!',
        'admin-deleted-successfully' => 'تم حذف المشرف بنجاح!',
        'admin-created-successfully' => 'تم إنشاء المشرف بنجاح',

        // General Messages
        'success' => 'نجاح',
        'error' => 'خطأ',
        'delete' => 'حذف',

        // Category Validation Messages
        /** start category translation validation here */
        'english-name-required' => 'الاسم بالإنجليزية مطلوب.',
        'english-name-min-characters' => 'يجب أن يتكون الاسم بالإنجليزية من :min أحرف على الأقل.',
        'arabic-name-required' => 'الاسم بالعربية مطلوب.',
        'arabic-name-min-characters' => 'يجب أن يتكون الاسم بالعربية من :min أحرف على الأقل.',
        'priority-required' => 'الأولوية مطلوبة.',
        'image-required' => 'الصورة مطلوبة.',
        'image-invalid' => 'الصورة غير صالحة',
        'validation.msg.image-mime' => 'يجب أن يكون :attribute ملف من النوع: :mime_types.',
        /** end category translation validation here */

        // Other Validation Messages
        'name-required' => 'الاسم مطلوب.',
        'email-required' => 'البريد الإلكتروني مطلوب.',
        'email-already-exists' => 'البريد الإلكتروني موجود بالفعل.',
        'email-should-be-email-format' => 'يجب أن يكون البريد الإلكتروني بتنسيق البريد الإلكتروني.',
        'password-confirmation-required' => 'تأكيد كلمة المرور مطلوب.',
        'role-required' => 'الدور مطلوب.',

        // Category CRUD Messages
        'category-created-successfully!' => 'تم إنشاء الفئة بنجاح!',
        'categories-updated-successfully!' => 'تم تحديث الفئات بنجاح!',
        'categories-deleted-successfully!' => 'تم حذف الفئات بنجاح!',

        // Description and Address Validation Messages
        'english-description-required' => 'الوصف بالإنجليزية مطلوب',
        'english-description-min-characters' => 'يجب أن يحتوي الوصف بالإنجليزية على الحد الأدنى من الحروف',
        'arabic-description-required' => 'الوصف بالعربية مطلوب',
        'arabic-description-min-characters' => 'يجب أن يحتوي الوصف بالعربية على الحد الأدنى من الحروف',
        'english-address-required' => 'العنوان بالإنجليزية مطلوب',
        'english-address-min-characters' => 'يجب أن يحتوي العنوان بالإنجليزية على الحد الأدنى من الحروف',
        'arabic-address-required' => 'العنوان بالعربية مطلوب',
        'arabic-address-min-characters' => 'يجب أن يحتوي العنوان بالعربية على الحد الأدنى من الحروف',

        // Location and Link Validation Messages
        'google-map-url-required' => 'رابط خرائط جوجل مطلوب',
        'invalid-url' => 'رابط خرائط جوجل غير صالح',
        'phone-number-required' => 'رقم الهاتف مطلوب',
        'longitude-required' => 'خط الطول مطلوب',
        'invalid-longitude' => 'خط الطول غير صالح',
        'latitude-required' => 'خط العرض مطلوب',
        'invalid-latitude' => 'خط العرض غير صالح',

        // Business and Website Validation Messages
        'price-level-required' => 'مستوى السعر مطلوب',
        'website-required' => 'الموقع الإلكتروني مطلوب',
        'invalid-website-url' => 'رابط الموقع الإلكتروني غير صالح',

        // Rating Validation Messages
        'rating-required' => 'التقييم مطلوب',
        'invalid-rating' => 'التقييم غير صالح',
        'total-user-rating-required' => 'إجمالي تقييم المستخدم مطلوب',
        'invalid-total-user-rating' => 'إجمالي تقييم المستخدم غير صالح',

        // Subcategory and Region Validation Messages
        'subcategory-required' => 'الفئة الفرعية مطلوبة',
        'invalid-subcategory' => 'الفئة الفرعية غير صالحة',
        'region-required' => 'المنطقة مطلوبة',
        'invalid-region' => 'المنطقة غير صالحة',

        // Tags Validation Messages
        'business-status-required' => 'حالة العمل مطلوبة',
        'tags-required' => 'العلامات مطلوبة',
        'invalid-tags' => 'العلامات غير صالحة',
        'invalid-tag' => 'علامة غير صالحة',

        // Image Validation Messages
        'main-image-required' => 'الصورة الرئيسية مطلوبة',
        'invalid-image' => 'الصورة الرئيسية غير صالحة',
        'invalid-image-format' => 'تنسيق الصورة الرئيسية غير صالح',
        'invalid-gallery-image' => 'صورة المعرض غير صالحة',
        'invalid-gallery-image-format' => 'تنسيق صورة المعرض غير صالح',

        // Email Validation Messages
        'email-invalid' => 'البريد الالكتروني غير صالح',

        // Message Validation
        'message-required' => "يجب ادخال الرسالة",

        // Question Validation
        "question-name-en-required" => "السؤال بالانجليزي مطلوب",
        "question-name-ar-required" => "السؤال بالعربي مطلوب",
        "is-first-question-required" => "هل هذا السؤال الأول؟",

        // Follow Validation
        'check-if-followers-existence' => 'المستخدم :user ليس واحدًا من متابعيك',
    ],

    'api' => [
        'relationship_not_exist' => "العلاقة :relationship غير موجودة في نموذج المستخدم",
        // Category Errors

        'subcategory-is-required' => 'التصنيف الفرعي مطلوب.',
        'subcategory-does-not-exists' => 'رقم التصنيف الفرعي غير موجود.',

        // Place Errors
        'this-place-already-in-your-visited-place' => 'هذا المكان موجود بالفعل في قائمة الأماكن التي قمت بزيارتها.',
        'this-place-not-in-your-visited-place-list' => 'هذا المكان غير موجود في قائمة الأماكن التي قمت بزيارتها لإزالته من القائمة.',
        'id-does-not-exists' => 'المعرف غير موجود.',
        'you-did-not-make-review-for-this' => 'انت لم تقم بمراجعة لهذه.',


        // Comment Errors
        'the-selected-comment-id-does-not-exists' => 'معرف التعليق المحدد غير موجود.',
        'the-comment-id-required' => 'معرف التعليق مطلوب.',
        'the-content-required' => 'المحتوى مطلوب.',
        'you_can_not_delete_the_comment' => 'لا يمكنك حذف التعليق.',
        'the-status-required' => 'الحالة بالاعجاب او عدم الاعجاب مطلوبة.',
        'comment-should-be-string' => 'التعليق يجب ان يكون نص.',

        // Review Errors
        'the-selected-review-id-does-not-exists' => 'معرف التعليق غير موجود.',
        'the-review-id-required' => 'معرف التعليق مطلوب.',

        // User Errors
        'the-following-id-is-required' => 'معرف المتبوع مطلوب.',
        'the-following-id-does-not-exists' => 'معرف المتبوع غير موجود بلائحة المستخدمين.',
        'user-id-is-required' => 'رقم معرف للمستخدم مطلوب.',
        'user-id-does-not-exists' => 'معرف المستخدم غير موجود.',

        // Conversation Errors
        'conversation-id-is-required' => 'معرف المحادثة مطلوب.',
        'conversation-id-does-not-exists' => 'معرف المحادثة غير موجود.',
        'conversation-id-must-exist' => 'المحادثة المحددة غير موجودة.',
        'conversation-id-invalid' => 'معرف المحادثة غير صالح.',

        // Guide Trip Errors
        "guide-id-is-required" => "معرف الدليل مطلوب.",
        "the-provided-id-not-guide" => 'المعرف غير مرتبط بمرشد سياحي ',
        "you-can-not-make-rating-for-yourself" => "لا تستطيع ان تقيم نفسك",
        "you-made-rating-already" => "لقد قمت بالفعل بتقييم المرشد",
        "guide-id-not-exists" => "الدليل المحدد غير موجود.",
        "guide-already-joined" => "لقد انضممت إلى هذا الدليل من قبل.",
        'guide-trip-id-required' => 'رقم معرف الرحلة السياحية مطلوب.',

        'status-is-required' => 'الحالة مطلوبة',
        'guide-trip-user-id-required' => 'رقم معرف طلب الانضمام مطلوب ',
        'guide-trip-user-id-does-not-exists' => 'رقم معرف طلب الانضمام غير موجود',
        'you_cant_make_review_for_upcoming_trip' => 'هذه الرحلة لا زالت نشطة لا يمكنك عمل مراجهة عليها الان',
        'this-trip-was-deleted' => 'هذه الرحلة تم حذفها',

        'guide-trip-id-does-not-exists' => 'رقم معرف الرحلة السياحية غير موجود.',
        'media-id-required' => 'رقم معرف صورة الرحلة السياحية مطلوب.',
        'media-id-does-not-exists' => 'رقم معرف صورة الرحلة السياحية غير موجود.',
        'this_is_not_in_favorite_list' => 'هذه غير موجودة في لائحة المفضلة',
        "guide-update-not-allowed" => "لا يُسمح لك بتحديث هذا الدليل.",
        'guide_trip_id.required' => 'معرف رحلة الدليل مطلوب.',
        'guide_trip_id.exists' => 'معرف رحلة الدليل المحدد غير صحيح.',
        'guide_trip_id.check_if_guide_trip_active_or_in_future' => 'يجب أن تكون رحلة الدليل نشطة أو مجدولة في المستقبل.',
        'guide_trip_id.check_if_guide_trip_user_exist' => 'يجب أن يكون هناك مستخدم موجود لرحلة الدليل.',
        'you_did_not_participate_in_any_of_guide_trip' => 'لم تشارك في أي من رحلات المرشد.',
        'you_already_create_rating_for_this_guide' => 'لقد قمت بإنشاء تقييم لهذا المرشد بالفعل.',
        'you_did_not_make_review_for_this_guide_to_update' => 'لم تقم بكتابة مراجعة لهذا المرشد لتحديثها.',
        'you_should_be_guide_to_create_guide_trip' => 'يجب أن تكون مرشداً لإنشاء رحلة مرشد.',
        'you_did_not_make_rating_for_this_guide' => 'لم تقم بعمل تقييم للمرشد السياحي من قبل',
        'guide-trip-id-invalid' => 'معرف رحلة الدليل المحدد غير صحيح.',
        'guide-trip-active-or-future' => 'يجب أن تكون رحلة الدليل نشطة أو مجدولة في المستقبل.',
        'guide-trip-user-joined' => 'يجب أن يكون المستخدم قد انضم إلى رحلة الدليل.',

        // Post Errors
        'post-id-does-not-exists' => 'معرف المنشور غير موجود.',
        'post-id-invalid' => 'معرف المنشور غير صالح.',
        'reply-id-is-required' => 'رقم معرف الرد مطلوب.',
        'reply-id-does-not-exists' => 'رقم معرف الرد غير موجود.',

        // Message Type Errors
        "message-type-is-required" => "نوع الرسالة مطلوب.",
        "message-type-must-be-string" => "يجب أن يكون نوع الرسالة نصًا.",
        "message-type-invalid" => "يجب أن يكون نوع الرسالة أحد الأنواع التالية: نص، صورة، صوت، أو فيديو.",
        "message-txt-is-required-if-text" => "نص الرسالة مطلوب عندما يكون نوع الرسالة نصًا.",
        "message-txt-must-be-string" => "يجب أن يكون نص الرسالة نصًا.",

        // File Upload Errors
        "file-is-required-if-media" => "الملف مطلوب عندما يكون نوع الرسالة صورة أو صوت أو فيديو.",
        "file-must-be-valid" => "يجب أن يكون الملف المرفوع صالحًا.",
        "file-invalid-type" => "يجب أن يكون الملف من الأنواع التالية: jpg، jpeg، png، gif، mp3، wav، mp4، mov، avi.",
        "file-max-size" => "يجب ألا يتجاوز حجم الملف :max كيلوبايت.",

        // Personal Info Errors
        "first-name-is-required" => "الاسم الأول مطلوب.",
        "first-name-must-be-string" => "يجب أن يكون الاسم الأول نصًا.",
        "last-name-is-required" => "اسم العائلة مطلوب.",
        "last-name-must-be-string" => "يجب أن يكون اسم العائلة نصًا.",
        "birthday-is-required" => "تاريخ الميلاد مطلوب.",
        "birthday-invalid-age" => "يجب أن يكون عمرك على الأقل :min_age سنوات.",
        "gender-is-required" => "الجنس مطلوب.",
        "gender-must-be-valid" => "يجب أن يكون الجنس إما 1 (ذكر) أو 2 (أنثى).",

        // Contact Info Errors
        "phone-number-is-required" => "رقم الهاتف مطلوب.",
        "phone-number-must-be-string" => "يجب أن يكون رقم الهاتف نصًا.",
        "description-is-required" => "الوصف مطلوب.",
        "description-must-be-string" => "يجب أن يكون الوصف نصًا.",

        // Tag Errors
        "tags-id-is-required" => "العلامات مطلوبة.",
        "tags-id-must-exist" => "واحدة أو أكثر من العلامات المحددة غير موجودة.",

        // Image Errors
        "image-is-required" => "الصورة مطلوبة.",
        "image-must-be-an-image" => "يجب أن يكون الملف صورة.",
        "images-optional" => "الصور اختيارية.",
        "images-must-be-an-image" => "يجب أن تكون كل ملف صورة.",
        "images-invalid-format" => "يجب أن تكون الصورة من نوع: jpeg، png، jpg، gif، svg، webp، bmp، tiff، ico، svgz.",

        // Professional File Errors
        "professional-file-is-required" => "الملف المهني مطلوب.",
        "professional-file-invalid-format" => "يجب أن يكون الملف المهني من أحد الأنواع التالية: pdf، jpeg، png، jpg، gif، svg، webp، bmp، tiff، ico، svgz.",

        // General Errors
        "name-is-required" => "الاسم مطلوب.",
        "subject-nullable" => "الموضوع اختياري.",
        "message-is-required" => "الرسالة مطلوبة.",

        // Date Errors
        "date-is-required" => "التاريخ مطلوب.",
        "date-invalid-format" => "يجب أن يكون التاريخ بتنسيق صالح.",

        // Following ID Errors
        "following-id-is-required" => "معرف المتابعة مطلوب.",
        "following-id-not-exists" => "المستخدم المطلوب غير موجود.",
        "follower-following-already-exists" => "أنت تتابع هذا المستخدم بالفعل.",

        // Question ID Errors
        "question-id-is-required" => "معرف السؤال مطلوب.",
        "question-id-not-exists" => "السؤال المحدد غير موجود.",

        // Answer Errors
        "answer-is-required" => "الإجابة مطلوبة.",
        "answer-invalid-value" => "يجب أن تكون الإجابة إما 'نعم', 'لا', أو 'لا أعرف'.",

        // Rating Errors
        "rating-must-be-numeric" => "يجب أن يكون التقييم قيمة رقمية.",
        "rating-min-value" => "يجب أن يكون التقييم على الأقل 1.",
        "rating-max-value" => "يجب ألا يزيد التقييم عن 5.",

        // Name Errors (English and Arabic)
        "name-en-required" => "الاسم بالإنجليزية مطلوب.",
        "name-en-string" => "الاسم بالإنجليزية يجب أن يكون نصًا.",
        "name-en-max" => "الاسم بالإنجليزية لا يمكن أن يتجاوز 255 حرفًا.",
        "name-en-check" => "الاسم بالإنجليزية يجب أن يتجاوز تحقق الدليل.",
        "name-ar-required" => "الاسم بالعربية مطلوب.",
        "name-ar-string" => "الاسم بالعربية يجب أن يكون نصًا.",
        "name-ar-max" => "الاسم بالعربية لا يمكن أن يتجاوز 255 حرفًا.",

        // Description Errors (English and Arabic)
        "description-en-required" => "الوصف بالإنجليزية مطلوب.",
        "description-en-string" => "الوصف بالإنجليزية يجب أن يكون نصًا.",
        "description-ar-required" => "الوصف بالعربية مطلوب.",
        "description-ar-string" => "الوصف بالعربية يجب أن يكون نصًا.",

        // Price Errors
        "main-price-required" => "السعر الرئيسي مطلوب.",
        "main-price-numeric" => "السعر الرئيسي يجب أن يكون رقمًا.",
        "main-price-min" => "السعر الرئيسي يجب أن يكون على الأقل 0.",

        // Date and Time Errors
        "start-datetime-required" => "تاريخ ووقت البدء مطلوب.",
        "start-datetime-date" => "تاريخ ووقت البدء يجب أن يكون تاريخًا صحيحًا.",
        "start-datetime-format" => "تاريخ ووقت البدء يجب أن يكون بتنسيق Y-m-d H:i:s.",
        "start-datetime-future" => "تاريخ ووقت البدء يجب أن يكون في المستقبل.",
        "end-datetime-required" => "تاريخ ووقت الانتهاء مطلوب.",
        "end-datetime-date" => "تاريخ ووقت الانتهاء يجب أن يكون تاريخًا صحيحًا.",
        "end-datetime-format" => "تاريخ ووقت الانتهاء يجب أن يكون بتنسيق Y-m-d H:i:s.",
        "end-datetime-future" => "تاريخ ووقت الانتهاء يجب أن يكون في المستقبل.",
        "end-datetime-after-start" => "تاريخ ووقت الانتهاء يجب أن يكون بعد تاريخ ووقت البدء.",

        // Max Attendance Errors
        "max-attendance-required" => "الحد الأقصى للحضور مطلوب.",
        "max-attendance-integer" => "الحد الأقصى للحضور يجب أن يكون عددًا صحيحًا.",
        "max-attendance-min" => "الحد الأقصى للحضور يجب أن يكون على الأقل 1.",

        // Gallery Errors
        "gallery-file" => "كل عنصر في المعرض يجب أن يكون ملفًا.",
        "gallery-mimes" => "كل عنصر في المعرض يجب أن يكون ملفًا من نوع: jpeg، png، jpg، gif، svg، webp، bmp، tiff، ico، svgz، mp4، mov، avi، mkv، flv، wmv.",

        // Activities Errors
        "activities-required" => "الأنشطة مطلوبة.",
        "activities-string" => "الأنشطة يجب أن تكون نصًا.",

        // Price Include Errors
        "price-include-required" => "سعر الشمول مطلوب.",
        "price-include-string" => "سعر الشمول يجب أن يكون نصًا.",

        // Price Age Errors
        "price-age-nullable" => "عمر السعر يجب أن يكون نصًا إذا كان موجودًا.",

        // Assembly Errors
        "assembly-required" => "التجميع مطلوب.",
        "assembly-string" => "التجميع يجب أن يكون نصًا.",

        // Required Items Errors
        "required-items-nullable" => "العناصر المطلوبة يجب أن تكون نصًا إذا كانت موجودة.",

        // Is Trail Errors
        "is-trail-nullable" => "الطريق يجب أن يكون Boolean إذا كان موجودًا.",
        "trail-nullable" => "الطريق يجب أن يكون نصًا إذا كان موجودًا.",

        // Subscribers Errors
        'subscribers.required' => 'المشتركين مطلوبون.',
        'subscribers.array' => 'يجب أن يكون المشتركين مصفوفة.',
        'subscribers.*.first_name.required' => 'اسم المشترك الأول مطلوب.',
        'subscribers.*.first_name.string' => 'يجب أن يكون الاسم الأول سلسلة نصية.',
        'subscribers.*.first_name.max' => 'قد لا يكون الاسم الأول أكبر من 255 حرفًا.',
        'subscribers.*.last_name.required' => 'اسم العائلة لكل مشترك مطلوب.',
        'subscribers.*.last_name.string' => 'يجب أن يكون الاسم الأخير سلسلة نصية.',
        'subscribers.*.last_name.max' => 'قد لا يكون الاسم الأخير أكبر من 255 حرفًا.',
        'subscribers.*.age.required' => 'العمر لكل مشترك مطلوب.',
        'subscribers.*.age.integer' => 'يجب أن يكون العمر عدد صحيح.',
        'subscribers.*.age.min' => 'يجب أن يكون العمر على الأقل 0.',
        'subscribers.*.phone_number.required' => 'رقم الهاتف لكل مشترك مطلوب.',
        'subscribers.*.phone_number.string' => 'يجب أن يكون رقم الهاتف سلسلة نصية.',
        'subscribers.*.phone_number.max' => 'قد لا يكون رقم الهاتف أكبر من 20 حرفًا.',

        // Category Errors
        'categories-id-nullable' => 'حقل معرف الفئات اختياري.',
        'categories-id-array' => 'يجب أن يكون حقل معرف الفئات مصفوفة.',
        'category-exists' => 'واحدة أو أكثر من الفئات غير موجودة.',
        'category-main' => 'يجب أن تكون الفئة المحددة فئة رئيسية.',

        // Subcategory Errors
        'subcategories-id-nullable' => 'حقل معرف الفئات الفرعية اختياري.',
        'subcategories-id-array' => 'يجب أن يكون حقل معرف الفئات الفرعية مصفوفة.',
        'subcategory-exists' => 'واحدة أو أكثر من الفئات الفرعية غير موجودة.',
        'subcategory-main' => 'يجب أن تكون الفئة الفرعية المحددة فئة فرعية صحيحة.',

        // Region ID Errors
        'region-id-nullable' => 'حقل معرف المنطقة اختياري.',
        'region-id-integer' => 'يجب أن يكون معرف المنطقة عددًا صحيحًا.',
        'region-id-exists' => 'معرف المنطقة المحدد غير موجود.',

        // Cost Errors
        'min-cost-nullable' => 'حقل التكلفة الدنيا اختياري.',
        'max-cost-nullable' => 'حقل التكلفة القصوى اختياري.',

        // Feature Errors
        'features-id-nullable' => 'حقل معرف الميزات اختياري.',
        'feature-exists' => 'واحدة أو أكثر من الميزات غير موجودة.',

        // Rate Errors
        'min-rate-nullable' => 'حقل التصنيف الأدنى اختياري.',
        'max-rate-nullable' => 'حقل التصنيف الأقصى اختياري.',

        // Activity Errors
        'activity-name-string' => 'يجب أن يكون اسم النشاط نصًا.',
        'activity-name-max' => 'لا يمكن أن يكون اسم النشاط أطول من 255 حرفًا.',
        'day-total-duration' => 'لا يمكن أن تتجاوز مدة الأنشطة في يوم واحد 24 ساعة.',

        // Plan Errors
        'plan-id-required' => 'معرف الخطة مطلوب.',
        'plan-id-exists' => 'الخطة المحددة غير موجودة أو لا تخصك.',
        'name-required' => 'حقل الاسم مطلوب.',
        'name-string' => 'يجب أن يكون الاسم نصًا.',
        'name-max' => 'لا يمكن أن يكون الاسم أطول من 255 حرفًا.',
        'description-required' => 'حقل الوصف مطلوب.',
        'description-max' => 'لا يمكن أن يكون الوصف أطول من 1000 حرفًا.',
        'description-string' => 'يجب أن يكون الوصف نصًا.',
        'days-required' => 'حقل الأيام مطلوب.',
        'days-array' => 'يجب أن يكون حقل الأيام مصفوفة.',

        // Activity Errors
        'activities-array' => 'يجب أن يكون حقل الأنشطة مصفوفة.',
        'activity-name-required' => 'حقل الاسم مطلوب لكل نشاط.',
        'activity-start-time-required' => 'حقل وقت البدء مطلوب لكل نشاط.',
        'activity-start-time-format' => 'يجب أن يكون وقت البدء بالتنسيق H:i.',
        'activity-end-time-required' => 'حقل وقت الانتهاء مطلوب لكل نشاط.',
        'activity-end-time-format' => 'يجب أن يكون وقت الانتهاء بالتنسيق H:i.',
        'activity-end-time-after' => 'يجب أن يكون وقت الانتهاء بعد وقت البدء.',
        'activity-place-id-required' => 'حقل معرف المكان مطلوب لكل نشاط.',
        'activity-place-id-exists' => 'المكان المحدد غير موجود.',
        'activity-note-max' => 'لا يمكن أن يكون الملاحظة أطول من 255 حرفًا.',

        // Post Errors
        'post-id-required' => 'معرف المنشور مطلوب.',
        'post-id-exists' => 'المنشور المحدد غير موجود.',
        'post-id-can-make-comment' => 'لا يمكنك التعليق على هذا المنشور.',
        'content-required' => 'محتوى التعليق مطلوب.',
        'content-string' => 'يجب أن يكون المحتوى نصًا.',

        // Visitable Errors
        'visitable-type-required' => 'نوع العنصر القابل للزيارة مطلوب.',
        'visitable-type-in' => 'يجب أن يكون نوع العنصر القابل للزيارة أحد الخيارات التالية: مكان، خطة، رحلة، حدث، تطوع.',
        'visitable-id-required' => 'معرف العنصر القابل للزيارة مطلوب.',
        'visitable-id-exists' => 'العنصر القابل للزيارة المحدد غير موجود.',

        // Privacy Errors
        'privacy-required' => 'إعداد الخصوصية مطلوب.',
        'privacy-in' => 'يجب أن يكون إعداد الخصوصية أحد الخيارات التالية: عام، خاص، الأصدقاء فقط.',

        // Comment Errors
        'comment-id-required' => 'معرف التعليق مطلوب.',
        'comment-id-exists' => 'التعليق المحدد غير موجود.',

        // Post Ownership Errors
        'post-id-custom' => 'يجب أن يكون المنشور مملوكًا للمستخدم.',

        // Visitable ID Errors
        'visitable-id-custom' => 'الزيارة المحددة غير موجودة.',

        // Location Errors
        'latitude-required' => 'حقل خط العرض مطلوب.',
        'latitude-numeric' => 'يجب أن يكون خط العرض رقمًا.',
        'longitude-required' => 'حقل خط الطول مطلوب.',
        'longitude-numeric' => 'يجب أن يكون خط الطول رقمًا.',

        // User Profile Errors
        'gender-required' => 'حقل الجنس مطلوب.',
        'gender-in' => 'الجنس المحدد غير صحيح.',
        'birthday-required' => 'حقل تاريخ الميلاد مطلوب.',
        'birthday-min-age' => 'يجب أن يكون عمرك على الأقل 18 عامًا.',
        'tags-id-required' => 'حقل العلامات مطلوب.',
        'tags-id-exists' => 'إحدى العلامات المحددة غير موجودة.',
        'username-alpha_dash' => 'يمكن أن يحتوي اسم المستخدم فقط على حروف وأرقام وشرطات وشرطات سفلية.',
        'username-not-regex' => 'لا يجوز أن يحتوي اسم المستخدم على مسافات.',

        // Image Validation Errors
        'image-image' => 'يجب أن تكون الصورة ملف صورة صالح.',
        'place_name-required' => 'حقل اسم المكان مطلوب.',
        'address-required' => 'حقل العنوان مطلوب.',
        'images-image' => 'يجب أن تكون كل صورة ملف صورة صالح.',
        'images-mimes' => 'يجب أن تكون كل صورة بأحد التنسيقات التالية: jpeg، png، jpg، gif، svg، webp.',

        // Trip Errors
        'trip_id-required' => 'حقل معرف الرحلة مطلوب.',
        'trip_id-integer' => 'يجب أن يكون معرف الرحلة عدد صحيح.',
        'trip_id-exists' => 'معرف الرحلة المحدد غير موجود.',

        // User Trip Errors
        'user_id-required' => 'حقل معرف المستخدم مطلوب.',
        'user_id-integer' => 'يجب أن يكون معرف المستخدم عدد صحيح.',
        'user_id-exists' => 'معرف المستخدم المحدد غير موجود في جدول users_trips.',

        // Trip Type Errors
        'trip_type-required' => 'حقل نوع الرحلة مطلوب.',
        'trip_type-string' => 'يجب أن يكون نوع الرحلة نصًا.',
        'trip_type-in' => 'نوع الرحلة المحدد غير صالح.',

        // Place Errors
        'place_id-required' => 'حقل معرف المكان مطلوب.',
        'place_id-integer' => 'يجب أن يكون معرف المكان عدد صحيح.',
        'place_id-exists' => 'معرف المكان المحدد غير موجود.',

        // Cost Errors
        'cost-required' => 'حقل التكلفة مطلوب.',
        'cost-numeric' => 'يجب أن تكون التكلفة عددًا.',
        'cost-min' => 'يجب أن تكون التكلفة على الأقل 0.',

        // Age Range Errors
        'age_min-required_if' => 'حقل الحد الأدنى للعمر مطلوب عندما يكون نوع الرحلة 0 أو 1.',
        'age_min-integer' => 'يجب أن يكون الحد الأدنى للعمر عدد صحيح.',
        'age_max-required_if' => 'حقل الحد الأقصى للعمر مطلوب عندما يكون نوع الرحلة 0 أو 1.',
        'age_max-integer' => 'يجب أن يكون الحد الأقصى للعمر عدد صحيح.',

        // Date and Time Errors
        'date-required' => 'حقل التاريخ مطلوب.',
        'date-date' => 'التاريخ غير صالح.',
        'date-custom' => 'لا يمكن أن يكون تاريخ الرحلة في الماضي.',
        'time-required' => 'حقل الوقت مطلوب.',
        'time-date_format' => 'يجب أن يكون الوقت بتنسيق H:i:s.',

        // Attendance Errors
        'attendance_number-required_if' => 'حقل عدد الحضور مطلوب عندما يكون نوع الرحلة 0 أو 1.',
        'attendance_number-integer' => 'يجب أن يكون عدد الحضور عدد صحيح.',
        'attendance_number-min' => 'يجب أن يكون عدد الحضور على الأقل 1.',

        // Tag Errors
        'tags-required' => 'حقل العلامات مطلوب.',
        'tags-exists' => 'العلامة المحددة غير موجودة.',
        'tags-nullable' => 'يمكن أن يكون حقل العلامات فارغًا.',

        // User Errors for Trip Type 2
        'users-required_if' => 'حقل المستخدمين مطلوب عندما يكون نوع الرحلة 2.',

        // Age Validation Errors
        'age_max-gte' => 'يجب أن يكون الحد الأقصى للعمر أكبر من أو يساوي الحد الأدنى للعمر.',

        // Gender Nullable Error
        'gender-nullable' => 'يمكن أن يكون حقل الجنس فارغًا.',

        // Date and Time Conditional Validation Errors
        'date-required_if' => 'حقل التاريخ مطلوب عندما يكون الوقت موجودًا.',
        'time-required_if' => 'حقل الوقت مطلوب عندما يكون التاريخ موجودًا.',

        // Email Errors
        'email-required' => 'حقل البريد الإلكتروني مطلوب.',
        'email-string' => 'يجب أن يكون البريد الإلكتروني نصًا.',
        'email-email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صحيح.',
        'email-lowercase' => 'يجب أن يكون البريد الإلكتروني بحروف صغيرة.',

        // Password Errors
        'password-required' => 'حقل كلمة المرور مطلوب.',
        'password-string' => 'يجب أن تكون كلمة المرور نصًا.',

        // Geolocation Errors
        'lng-required' => 'حقل الطول (الطول الجغرافي) مطلوب.',
        'lat-required' => 'حقل العرض (العرض الجغرافي) مطلوب.',

        // Category and Subcategory Errors
        'categories-id-invalid' => 'الفئة المحددة غير موجودة أو ليست فئة رئيسية.',
        'subcategories-id-invalid' => 'الفئة الفرعية المحددة غير موجودة أو ليست فئة فرعية صالحة.',

        // Custom Category Errors
        'the-selected-subcategory-it-is-main-category' => 'الفئة الفرعية المختارة هي الفئة الرئيسية',
        'the-subcategories-should-be-array' => 'يجب أن تكون الفئات الفرعية في شكل مصفوفة',
        'the-category-should-be-array' => 'يجب أن تكون الفئات في شكل مصفوفة',

        // Trip and Journey Errors
        'check-update-message-trip' => 'تم تحديث الرحلة، يرجى التحقق من تفاصيل الرحلة.',
        'this-user-has-joined-a-trip-on-the-same-date-as-your-trip' => 'هذا المستخدم قد انضم إلى رحلة في نفس تاريخ رحلتك، لذا لا يمكنه الانضمام إلى رحلتك.',
        'you-are-not-attendance-in-this' => 'أنت لست حاضرًا في هذه الرحلة.',
        'you-cant-make-review-for-upcoming-trip' => 'لا يمكنك كتابة مراجعة لرحلة قادمة.',
        'trip-registration-closed' => 'لا يمكنك التسجيل في هذه الرحلة بعد الآن.',
        'trip-has-started' => 'بدأت الرحلة في :start_datetime.',
        'trip-conflict' => 'هناك تعارض مع رحلاتك الحالية.',
        'already-in-trip' => 'أنت بالفعل في هذه الرحلة.',
        'not-owner-of-trip' => 'أنت لست مالك هذه الرحلة.',
        'cannot-update-trip-started-at' => 'لا يمكنك تحديث هذه الرحلة لأنها بدأت في :date.',
        'trip-not-found' => 'لم يتم العثور على الرحلة المحددة.',
        'this-trip-inactive' => 'هذه الرحلة غير نشطة.',
        'you-should-join-trip-first' => 'يجب أن تنضم إلى الرحلة أولاً.',
        'you-do-not-have-request-to-delete' => 'ليس لديك طلب لحذف هذه الرحلة.',
        'this-comment-did-not-belong-to-you' => 'هذا التعليق لا ينتمي اليك.',
        'this-reply-did-not-belong-to-you' => 'هذه الرد لا تعود إليك.',
        'you-can-not-delete-the-reply' => 'لا يمكنك حذف هذه الرد.',
        'comment-not-found' => 'لم يتم العثور على التعليق المحدد.',
        'reply-not-found' => 'لم يتم العثور على الرد المحدد.',
        'you-should-verify-email-first' => 'عليك ان تقوم بالتحقق من البريد الالكتروني',

        // User and Profile Errors
        'wrong-email' => 'البريد الإلكتروني المدخل غير صحيح.',
        'this-is-not-in-favorite-list-to-delete' => 'هذا العنصر غير موجود في قائمة المفضلة الخاصة بك ولا يمكن حذفه.',
        'you-already-make-this-as-favorite' => 'لقد قمت بالفعل بإضافتها إلى المفضلة.',
        'you-already-make-review-for-this' => 'لقد قمت بالفعل بكتابة مراجعة لهذا.',
        'you-do-not-have-review' => 'ليس لديك مراجعة لهذا.',
        'you-already-make-request-to-this-user-wait-for-accept-id' => 'لقد قمت بالفعل بإرسال طلب لهذا المستخدم، انتظر القبول.',
        'you-already-follow-this-user' => 'أنت تتابع هذا المستخدم بالفعل.',
        'you-can-not-follow-yourself' => 'لا يمكنك متابعة نفسك.',
        'you-are-not-follower-for-this-user' => 'أنت لست متابعًا لهذا المستخدم.',
        'you-can-not-unfollow-discover-jordan-profile' => 'لا تستطيع الغاء متابعة صفحة اكتشف الاردن.',
        'this-user-already-follow-you' => 'هذا المستخدم يتابعك بالفعل.',
        'you-can-not-make-request-to-yourself' => 'لا يمكنك تقديم طلب لنفسك.',
        'check-if-followers-existence' => 'المستخدم :user ليس متابعًا.',
        'you-are-not-following-this-user' => 'أنت لا تتابع هذا المستخدم.',
        'you-cannot-make-review-for-upcoming-event' => 'لا يمكنك كتابة تقييم لهذا الحدث القادم.',
        'you-cannot-make-review-for-upcoming-trip' => 'لا يمكنك كتابة مراجعة لرحلة قادمة.',
        'this_place_not_in_your_visited_place_list' => 'هذا المكان ليس في قائمة الاماكن التي زرتها',

        // Media, Post, and Tag Errors
        'this-place-not-in-your-visited-place-list' => 'هذا المكان ليس في قائمة الأماكن التي زرتها.',
        'you-are-not-the-owner-of-this-post' => 'أنت لست مالك هذا المنشور.',
        'you-are-not-authorized-to-delete-this-media' => 'لا تستطيح حذف هذه الوسائط.',
        'this-post-is-private' => 'هذه المشاركة خاصة.',
        'this-comment-did-not-belong-to-you' => 'هذا التعليق لا ينتمي اليك.',
        'you-did-not-make-this-to-interest-to-delete-interest' => 'لم تقم بإضافة هذا كشيء مثير للاهتمام لحذفه.',
        'you-already-make-this-as-interest' => 'لقد قمت بالفعل بوضع علامة على هذا كشيء مثير للاهتمام.',
        'you-can\'t-make-this-as-interest-because-it-in-the-past' => 'لا يمكنك وضع علامة على هذا كشيء مثير للاهتمام لأنه في الماضي.',
        'this-email-in-black-list' => 'هذا البريد الإلكتروني موجود في القائمة السوداء.',
        'you-should-verify-email-first' => 'عليك ان تقوم بالتحقق من البريد الالكتروني.',

        // Validation Errors
        'invalid-json-format' => 'البيانات المقدمة ليست بتنسيق JSON صالح.',
        'you-deactivated-by-admin-wait-to-unlock-the-block' => 'لقد تم حظرك من قبل المشرف انتظر حتى يتم الغاء حظرك',
        'invalid-credentials' => 'بيانات المستخدم غير صحيحة.',
        'you-should-verify-email-first' => 'عليك ان تقوم بالتحقق من البريد الالكتروني.',
        'you-cannot-delete-this-interest' => 'لا يمكنك حذف هذا الاهتمام.',
        'categories-should-be-array' => 'التصنيفات الرئيسية يجب ان تكون مصفوفة.',
        'something-went-wrong' => 'هناك خطأ قد حدث.',
        'you_are_not_attendance_in_this' => 'انت غير مشترك بهذه الرحلة',

        // Additional Errors
        'wait-for-admin-to-accept-your-application' => 'انتظر حتى يقوم المسؤول على الموافقة على طلبك',

        // Auth Validation
        "username-or-email-is-required"                             => "الاسم المستخدم او البريد الالكتروني مطلوب.",
        "username-or-email-must-be-string"                          => "الاسم المستخدم او البريد الالكتروني يجب ان يكون نصا.",
        "username-or-email-max"                                     => "الاسم المستخدم او البريد الالكتروني يجب ان يكون اقل من :max حروف.",
        "password-is-required"                                      => "كلمة المرور مطلوبة.",
        "password-confirmation-mismatch"                            => "تأكيد كلمة المرور غير متطابق.",
        "password-must-comply-with-rules"                           => "كلمة المرور يجب ان تتوافق مع القواعد.",
        'old_password-is-required'                                  => "كلمة المرور القديمة مطلوبة",
        "device-token-is-required"                                  => "رمز الجهاز مطلوب.",
        "device-token-max"                                          => "يجب ألا يتجاوز رمز الجهاز :max حرفًا.",
        "username-is-required"                                      => "الاسم المستخدم مطلوب.",
        "username-must-be-string"                                   => "الاسم المستخدم يجب ان يكون نصا.",
        "username-must-be-alpha-dash"                               => "يمكن أن يحتوي اسم المستخدم على أحرف وأرقام وشرطات فقط.",
        "username-min"                                              => "يجب ألا يقل اسم المستخدم عن :min أحرف.",
        "username-max"                                              => "يجب ألا يتجاوز اسم المستخدم :max أحرف.",
        "username-regex"                                            => "يجب أن يبدأ اسم المستخدم بحرف ويحتوي فقط على أحرف وأرقام وشرطات.",
        "username-no-whitespace"                                    => "يجب ألا يحتوي اسم المستخدم على مسافات.",
        "username-unique"                                           => "اسم المستخدم مُستخدم بالفعل.",
        "email-is-required"                                         => "البريد الإلكتروني مطلوب.",
        "email-must-be-string"                                      => "يجب أن يكون البريد الإلكتروني نصًا.",
        "email-must-be-lowercase"                                   => "يجب أن يكون البريد الإلكتروني بأحرف صغيرة.",
        "email-max"                                                 => "يجب ألا يتجاوز البريد الإلكتروني :max أحرف.",
        "email-unique"                                              => "البريد الإلكتروني مُستخدم بالفعل.",
        "email-in-blacklist"                                        => "البريد الإلكتروني في القائمة السوداء.",
        "email-invalid-format"                                      => "يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.",
        "email-max-length"                                          => "يجب ألا يزيد البريد الإلكتروني عن :max أحرف.",
        'email-valid'                                               => 'البريد الالكتروني صالح',
        "email-must-be-valid"                                       => "يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.",
        "token-is-required"                                         => "الرمز مطلوب.",
        'the-provided-old-password-is-incorrect'                    => 'كلمة المرور القديمة غير صحيحة.',

        // Trip Validation
        'trip_type_required'                                        => 'حقل نوع الرحلة مطلوب.',
        'trip_type_integer'                                         => 'يجب أن يكون نوع الرحلة عدداً صحيحاً.',
        'trip_type_in'                                              => 'يجب أن يكون نوع الرحلة أحد القيم التالية: 0, 1, أو 2.',
        'place_slug_required'                                       => 'حقل معرف المكان مطلوب.',
        'place_slug_string'                                         => 'يجب أن يكون معرف المكان نصاً.',
        'place_slug_exists'                                         => 'المكان المحدد غير موجود.',
        'name_required'                                             => 'حقل الاسم مطلوب.',
        'name_string'                                               => 'يجب أن يكون الاسم نصاً.',
        'name_max'                                                  => 'يجب ألا يتجاوز الاسم 255 حرفاً.',
        'description_required'                                      => 'حقل الوصف مطلوب.',
        'description_string'                                        => 'يجب أن يكون الوصف نصاً.',
        'cost_required'                                             => 'حقل التكلفة مطلوب.',
        'cost_numeric'                                              => 'يجب أن تكون التكلفة قيمة رقمية.',
        'cost_min'                                                  => 'يجب ألا تقل التكلفة عن 0.',
        'age_min_required_if'                                       => 'الحد الأدنى للعمر مطلوب لهذا النوع من الرحلات.',
        'age_min_integer'                                           => 'يجب أن يكون الحد الأدنى للعمر عدداً صحيحاً.',
        'age_max_required_if'                                       => 'الحد الأقصى للعمر مطلوب لهذا النوع من الرحلات.',
        'age_max_integer'                                           => 'يجب أن يكون الحد الأقصى للعمر عدداً صحيحاً.',
        'gender_required'                                           => 'حقل الجنس مطلوب.',
        'date_required'                                             => 'حقل التاريخ مطلوب.',
        'date_date'                                                 => 'يجب أن يكون التاريخ صالحاً.',
        'date_custom'                                               => 'لا يمكن أن يكون التاريخ المحدد في الماضي.',
        'time_required'                                             => 'حقل الوقت مطلوب.',
        'time_date_format'                                          => 'يجب أن يكون الوقت بصيغة H:i:s.',
        'attendance_number_required_if'                             => 'عدد الحضور مطلوب لهذا النوع من الرحلات.',
        'attendance_number_integer'                                 => 'يجب أن يكون عدد الحضور عدداً صحيحاً.',
        'attendance_number_min'                                     => 'يجب ألا يقل عدد الحضور عن 1.',
        'tags_required'                                             => 'يجب اختيار علامة واحدة على الأقل.',
        'tags_exists'                                               => 'واحدة أو أكثر من العلامات المحددة غير موجودة.',
        'users_required_if'                                         => 'المستخدمون مطلوبون لهذا النوع من الرحلات.',
        'time-should-not-be-in-the-past'                            => 'يجب ألا يكون الوقت في الماضي.',
        'select_at_least_three_tags'                                => 'يرجى اختيار على الاقل ثلاث علامات.',
        'tag_does_not_exist'                                        => 'العلامة :tag غير موجودة.',
        'check_if_followers_existence'                              => 'المستخدم :user لا يتابعك.',
        'user_does_not_exist'                                       => 'المستخدم ":user" غير موجود.',
        'date-cannot-be-in-the-past'                                => 'لا يمكن ان يكون التاريخ في الماضي.',
        'this_user_has_joined_a_trip_on_the_same_date_as_your_trip' => 'هذا المستخدم قام بالانضمام لرحلة في نفس التاريخ بالنسبة لرحلتك ، لذلك لا يمكنه الانضمام لرحلتك.',
        'this-user-has-already-joined-this-trip'                    => 'هذا المستخدم قد انضم بالفعل إلى هذه الرحلة.',
        'this-user-has-been-rejected-from-this-trip'                => 'هذا المستخدم تم رفضه من هذه الرحلة.',
        'this-user-has-left-this-trip'                              => 'هذا المستخدم قد غادر من الرحلة',
        'trip_slug_required'                                        => 'معرف الرحلة مطلوب.',
        'trip_slug_exists'                                          => 'الرحلة المحددة غير موجودة.',
        'you-should-enter-your-birthday-first'                      => 'يجب عليك إدخال تاريخ ميلادك أولاً.',
        'this-trip-has-exceeded-the-required-number'                => 'لقد تجاوزت هذه الرحلة العدد المطلوب. يمكنك العودة إلى الصفحة الرئيسية والبحث عن رحلة أخرى.',
        'this-journey-has-already-moved-on'                         => 'هذه الرحلة قد انطلقت بالفعل. يمكنك العودة إلى الصفحة الرئيسية والبحث عن رحلة أخرى.',
        'age-or-sex-not-acceptable'                                 => 'لا يُسمح لك بالانضمام إلى هذه الرحلة لأن عمرك أو جنسك غير مقبول.',
        'join-request-cancelled-by-owner'                           => 'تم إلغاء طلب الانضمام الخاص بك بواسطة صاحب الرحلة، لذا لا يمكنك الانضمام إلى هذه الرحلة مرة أخرى.',
        'already-joined-this-trip'                                  => 'لقد انضممت بالفعل إلى هذه الرحلة.',
        'creator-cannot-join-trip'                                  => 'أنت منشئ هذه الرحلة، لذا لا يمكنك الانضمام إليها.',
        'already-joined-another-trip-on-same-date'                  => 'لقد انضممت بالفعل إلى رحلة أخرى في نفس التاريخ.',
        'you-are-owner-of-trip'                                     => 'أنت مالك هذه الرحلة، لذا لا يمكنك إلغاء الانضمام.',
        'you-didnt-join-trip'                                       => 'لم تنضم إلى هذه الرحلة لإلغائها.',
        'trip-already-canceled'                                     => 'لقد قام مالك الرحلة بإلغاء انضمامك بالفعل.',
        'you-left-trip'                                             => 'لقد قامت بالغادر من هذه الرحلة.',
        'you-are-not-owner-of-trip'                                 => 'لست منشئ هذه الرحلة.',
        'age_max_gte'                                               => 'يجب أن يكون الحد الأقصى للعمر أكبر من أو يساوي الحد الأدنى للعمر.',
        'gender_nullable'                                           => 'حقل الجنس اختياري.',
        'date_required_if'                                          => 'التاريخ مطلوب عند تحديد الوقت.',
        'date_after_or_equal'                                       => 'يجب أن يكون التاريخ في المستقبل.',
        'time_required_if'                                          => 'الوقت مطلوب عند تحديد التاريخ.',
        'tags_nullable'                                             => 'العلامات اختيارية.',
        'date_invalid'                                              => 'يجب أن يكون التاريخ تاريخًا صالحًا.',
        'date_must_be_future'                                       => 'يجب أن يكون التاريخ في المستقبل.',
        'cant-make-trip-in-the-same-date-time'                      => 'لا يمكنك إنشاء رحلة في نفس التاريخ والوقت.',
        'cant-make-trip-in-this-date-you-already-on-trip'           => 'لا يمكنك إنشاء رحلة في هذا التاريخ لأنك بالفعل في رحلة أخرى.',
        'status_required'                                           => 'حقل الحالة مطلوب.',
        'status_invalid'                                            => 'يجب أن تكون الحالة إما "قبول" أو "إلغاء".',
        'trip_slug_string'                                          => 'يجب أن يكون معرف الرحلة نصًا.',
        'the-user-is-not-a-member-of-this-trip'                     => 'المستخدم ليس عضوًا في هذه الرحلة.',
        'user_slug_required'                                        => 'معرف المستخدم مطلوب.',
        'user_slug_exists'                                          => 'المستخدم المحدد غير موجود.',

        // Plan Validation
        'plan_slug_plan_error_main'                                 => 'معرف الخطة مطلوب أو أن الخطة غير موجودة.',
        'name_plan_error_main'                                      => 'اسم الخطة مطلوب أو يجب أن يكون نصًا بطول أقصى 255 حرفًا.',
        'description_plan_error_main'                               => 'وصف الخطة مطلوب أو يجب أن يكون نصًا بطول أقصى 1000 حرف.',
        'days_plan_error'                                           => 'يجب تضمين يوم واحد على الأقل في الخطة.',
        'activities_plan_error'                                     => 'الأنشطة لليوم :day مطلوبة أو يجب أن تكون مصفوفة.',
        'name_plan_error'                                           => 'اسم الخطة لليوم :day، النشاط :activity مطلوب أو يجب أن يكون نصًا بطول أقصى 255 حرفًا.',
        'start_time_plan_error'                                     => 'وقت البدء لليوم :day، النشاط :activity مطلوب أو يجب أن يكون بالتنسيق H:i.',
        'end_time_plan_error'                                       => 'وقت الانتهاء لليوم :day، النشاط :activity مطلوب أو يجب أن يكون بالتنسيق H:i.',
        'place_slug_plan_error'                                     => 'المكان المحدد لليوم :day، النشاط :activity مطلوب أو غير موجود.',
        'start_time_custom_plan_error'                              => 'يجب أن يكون وقت البدء لليوم :day، النشاط :activity بترتيب متسلسل.',
        'end_time_custom_plan_error'                                => 'يجب أن يكون وقت الانتهاء لليوم :day، النشاط :activity بعد وقت البدء.',
        'plan-slug-invalid'                                         => 'معرف الخطة غير صالح.',
        'plan-slug-does-not-exists'                                 => 'الخطة غير موجودة.',
        'you_are_not_the_owner_of_this_plan'                        => 'انت لست صاحب هذه الخطة.',

        // Category Validation
        'the-category-does-not-exists'                              => 'الفئة غير موجودة',
        'the-selected-category-does-not-main-category'              => 'الفئة المختارة ليست الفئة الرئيسية',
        'the-categories-should-be-array'                            => 'الفئات يجب أن تكون مصفوفة.',
        'the-selected-category-id-does-not-exists'                  => 'الفئة المحددة غير موجودة.',
        'the-category-id-required'                                  => 'معرّف الفئة مطلوب.',
        'invalid-category-id-not-main-category'                     => 'معرف الفئة غير صالح، فهي ليست الفئة الرئيسية.',

        // Place Validation
        'the-selected-place-is-not-active'                          => 'المكان المحدد غير نشط.',
        'place-id-invalid'                                          => 'معرّف المكان غير صالح.',
        'place-id-does-not-exists'                                  => 'معرف المكان غير موجود.',


    ],

];

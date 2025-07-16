<?php

$image_size         = (int) (setting('_general.max_image_size') ?? '5');
$image_file_ext     = setting('_general.allowed_image_extensions') ?? 'jpg,png';

return [
    // Basic details
    'course_bundle'                                     => 'حزمة الدورات',
    'create_bundle_desc'                                => 'قدّم تجارب تعليمية مميزة من خلال تجميع الدورات في حزم.',
    'bundle_title'                                      => 'عنوان الحزمة',
    'bundle_title_placeholder'                          => 'أضف عنوانًا',
    'select_courses'                                    => 'اختر الدورات',
    'bundle_thumbnail'                                  => 'صورة الحزمة المصغّرة',
    'bundle_short_description'                          => 'وصف مختصر',
    'bundle_short_description_placeholder'              => 'اكتب وصفًا مختصرًا',
    'per_bundle'                                        => '/للحزمة',

    // Tooltip and Placeholder Texts
    'select_courses_placeholder'                        => 'اختر الدورات',
    'tooltip_time_limit'                                => 'اختر الدورات التي ترغب في تضمينها ضمن هذه الحزمة لضمان وصول المتعلّمين إلى المحتوى الصحيح بعد الشراء.',

    // File Upload Texts
    'drop_file_here'                                    => 'قم بإفلات الملف هنا',
    'upload_file_text'                                  => 'اسحب الملف هنا أو <i>اضغط هنا</i> للرفع',
    'upload_file_format'                                => implode(', ', explode(',', $image_file_ext)).' (بحد أقصى 800x400px) الحجم: '.$image_size.'MB',

    // Pricing Section
    'pricing'                                           => 'التسعير',
    'pricing_desc'                                      => 'حدد سعر بيع الحزمة، وأضف خصومات اختيارية لجذب المزيد من المتعلمين.',
    'regular_price'                                     => 'السعر الأساسي',
    'sale_price'                                        => 'سعر الحزمة بعد الخصم',
    'allow_discount'                                    => 'السماح بالخصم',
    'optional'                                          => '(اختياري)',

    // Discount Section
    'choose_discount_amount'                            => 'اختر قيمة الخصم',
    'discount_description'                              => 'حدد نسبة الخصم لتعديل السعر النهائي للحزمة وجذب المزيد من المتعلمين.',
    'original_price'                                    => 'السعر قبل الخصم',

    // Discount Table Headers
    'discount_amount'                                   => 'قيمة الخصم',
    'discount_price'                                    => 'السعر بعد الخصم',
    'purchase_price'                                    => 'سعر الشراء',

    // Bundle Description Section
    'bundle_description'                                => 'وصف الحزمة',
    'bundle_description_desc'                           => 'تفاصيل إضافية تعزز من فهمك.',
    'bundle_description_placeholder'                    => 'أضف وصفًا',

    // Action Buttons
    'back'                                              => 'رجوع',
    'save'                                              => 'حفظ',
    'show'                                              => 'عرض',
    'listing_per_page'                                  => 'عدد العناصر في الصفحة',

    // messages
    'off'                                               => '% خصم',
    'of'                                                => 'من',
    'hrs'                                               => 'ساعات',
    'mins'                                              => 'دقائق',
    'min'                                               => 'دقيقة',
    'hr'                                                => 'ساعة',
    'active_students'                                   => 'طلاب نشطون',
    'active_student'                                    => 'طالب نشط',
    'publish_bundle_confirmation_title'                 => 'تأكيد نشر حزمة الدورات!',
    'publish_bundle_confirmation_desc'                  => 'حزمة الدورات جاهزة! راجع التفاصيل وأكّد النشر.',
    'publish'                                           => 'نشر',
    'view_course'                                       => 'عرض الدورة',
    'add_to_cart'                                       => 'أضف إلى السلة',
    'you_may_also_like'                                 => 'قد يعجبك أيضًا',
    'description'                                       => 'الوصف',
    'native'                                            => 'اللغة الأصلية',
    'i_can_speak'                                       => 'أتحدث',
    'more'                                              => 'المزيد',
    'reviews'                                           => 'المراجعات',
    'lessons'                                           => 'الدروس',
    'articles'                                          => 'المقالات',
    'videos'                                            => 'الفيديوهات',
    'duration'                                          => 'المدة',
    'created_at'                                        => 'تاريخ الإنشاء',
    'include_courses'                                   => 'الدورات المتضمنة',
    'create_bundle'                                     => 'تم إنشاء حزمة الدورات بنجاح',
    'create_bundle_error'                               => 'فشل في إنشاء الحزمة',
    'success_title'                                     => 'نجاح',
    'error_title'                                       => 'خطأ',
    'draft'                                             => 'مسودة',
    'published'                                         => 'منشورة',
    'archived'                                          => 'مؤرشفة',
    'Course'                                            => 'دورة',
    'Courses'                                           => 'دورات',
    'course_bundle'                                     => 'حزمة دورات',
    'search'                                            => 'بحث',
    'home'                                              => 'الرئيسية',
    'course_bundles'                                    => 'حزم الدورات',
    'search_by_keywords'                                => 'ابحث باستخدام الكلمات المفتاحية',

    // bundle listing
    'created_at'                                        => 'تاريخ الإنشاء',
    'published_bundles'                                 => 'الحزم المنشورة',
    'course_bundles_desc'                               => 'اجعل التعلم أسهل وأكثر فاعلية من خلال تجميع الدورات في حزم تعليمية مميزة.',
    'create_a_new_bundle'                               => 'إنشاء حزمة جديدة',
    'search_by_keyword'                                 => 'ابحث باستخدام كلمة مفتاحية',

    'bundle_status_draft'                               => 'مسودة',
    'bundle_status_published'                           => 'منشورة',
    'bundle_status_archived'                            => 'مؤرشفة',
    'all'                                               => 'الكل',

    // empty view
        'no_bundles_found'                                  => 'لم يتم العثور على نتائج!',
    'all_bundles'                                       => 'كل الحزم',
    'short_description'                                 => 'وصف مختصر',
    'all_bundles'                                       => 'كل الحزم',
    'update_bundle'                                     => 'تم تحديث حزمة الدورات بنجاح!',
    'purchased_bundles'                                 => 'تم شراء هذه الحزمة',
    'final_price'                                       => 'السعر النهائي',
    'manage_bundles'                                    => 'إدارة الحزم',
    'empty_view_description'                            => 'عذرًا، لم نتمكن من العثور على أي حزم دورات تطابق بحثك.',
    'no_courses_bundles'                                => 'لا توجد حزم دورات بعد!',
    'no_courses_bundles_desc'                           => 'نظم دوراتك في حزم لتقدّم قيمة أكبر للطلاب. ابدأ بإنشاء أول حزمة الآن!',
    'view_bundle'                                       => 'عرض الحزمة',
    'edit_bundle'                                       => 'تعديل الحزمة',
    'publish_bundle'                                    => 'نشر الحزمة',
    'archive_bundle'                                    => 'أرشفة الحزمة',
    'delete_bundle'                                     => 'حذف الحزمة',

    'bundle_status_success'                             => 'تم :status الحزمة بنجاح!',
    'bundle_deleted_successfully'                       => 'تم حذف الحزمة بنجاح!',
    'bundle_delete_error'                               => 'تعذّر حذف الحزمة!',

    'bundle_detail'                                     => 'تفاصيل الحزمة',
    'only_student_can_add_to_cart'                      => 'يمكن فقط للطلاب إضافة الحزمة إلى السلة',
    'only_student_can_get_bundle'                       => 'يمكن فقط للطلاب الحصول على الحزمة',

    // settings
    'coursebundle_settings'                             => 'إعدادات حزمة الدورات',
    'commission_settings'                               => 'إعدادات العمولة',
    'commission_settings_desc'                          => 'قم بإعداد نسبة العمولة الخاصة بحزم الدورات. القيمة تكون كنسبة مئوية (%).',
    'commission_settings_placeholder'                   => 'أدخل نسبة العمولة',
    'clear_course_bundle_amount_after_days'             => 'تصفية مبلغ الحزمة بعد عدد أيام',
    'clear_course_bundle_amount_after_days_desc'        => 'حدد عدد الأيام التي سيتم بعدها تصفية مبلغ الحزمة.',
    'clear_course_bundle_amount_after_days_placeholder' => 'أدخل عدد الأيام',
    'course_bundle_banner_image'                        => 'صورة بانر حزمة الدورات',
    'course_bundle_banner_image_desc'                   => 'قم برفع صورة بانر تظهر في صفحة تفاصيل الحزمة.',
    'course_bundle_heading'                             => 'عنوان حزمة الدورات',
    'course_bundle_heading_desc'                        => 'أدخل عنوانًا يُعرض في صفحة البحث عن الحزم.',
    'course_bundle_heading_placeholder'                 => 'أدخل العنوان',
    'course_bundle_description'                         => 'وصف حزمة الدورات',
    'course_bundle_description_desc'                    => 'أدخل وصفًا يظهر في صفحة البحث عن الحزم.',
    'course_bundle_description_placeholder'             => 'أدخل الوصف',
    'free'                                              => 'مجاني',
    'get_bundle'                                        => 'احصل على الحزمة',
    'bundle_not_found'                                  => 'لم يتم العثور على الحزمة',
];
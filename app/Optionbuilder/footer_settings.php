<?php

return [
    'section' => [
        'id'     => '_footer',
        'label'  => __('settings.footer_settings'),
        'icon'   => '',
    ], 'fields' => [
        [
            'id'            => 'copy_right_ar',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.copy_right_ar'),
            'placeholder'   => __('settings.enter_copy_right_ar'),
        ],
        [
            'id'            => 'copy_right_en',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.copy_right_en'),
            'placeholder'   => __('settings.enter_copy_right_en'),
        ],
        [
            'id'            => 'terms_conditions_ar',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.terms_conditions_ar'),
            'placeholder'   => __('settings.enter_terms_conditions_ar'),
        ],
        [
            'id'            => 'terms_conditions_en',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.terms_conditions_en'),
            'placeholder'   => __('settings.enter_terms_conditions_en'),
        ],
        [
            'id'            => 'terms_conditions_url',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.terms_conditions_url'),
            'placeholder'   => __('settings.enter_terms_conditions_url'),
        ],
        [
            'id'            => 'privacy_policy_ar',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.privacy_policy_ar'),
            'placeholder'   => __('settings.enter_privacy_policy_ar'),
        ],
        [
            'id'            => 'privacy_policy_en',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.privacy_policy_en'),
            'placeholder'   => __('settings.enter_privacy_policy_en'),
        ],
        [
            'id'            => 'privacy_policy_url',
            'type'          => 'text',
            'tab_id'        => 'footer_tab',
             'tab_title'     => __('settings.footer'),
            'label_title'   => __('settings.privacy_policy_url'),
            'placeholder'   => __('settings.enter_privacy_policy_url'),
        ],
    ]
];

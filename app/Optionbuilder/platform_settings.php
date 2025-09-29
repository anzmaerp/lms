<?php

return [
    'section' => [
        'id'     => '_social',
        'label'  => __('settings.social_settings'),
        'icon'   => '',
    ],
    'fields' => [
        [
          'id'            => 'platforms',
          'type'          => 'select',
          'class'         => '',
          'multi'         => true,
          'label_title'   => __('settings.social_platforms'),
          'options'       => [
                'Facebook'     => __('general.facebook'),
                'X/Twitter'     => __('general.x_twitter'),
                'LinkedIn'     => __('general.linkedIn'),
                'Instagram'     => __('general.instagram'),
                'Pinterest'     => __('general.pinterest'),
                'YouTube'     => __('general.youtube'),
                'TikTok'      => __('general.tiktok'),
                'WhatsApp'      => __('general.whatsApp'),
          ],
          'placeholder'   => __('settings.select_from_list'),   
      ]
    ]
];

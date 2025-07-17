<?php

use Illuminate\Support\Str;

if (!function_exists('bundleMenuOptions')) {
    function bundleMenuOptions($role)
    {
        switch ($role) {
            case 'tutor':
                return [ 
                    [
                    'tutorSortOrder' => 4,
                    'route' => 'coursebundles.tutor.bundles',   
                    'onActiveRoute' => ['coursebundles.tutor.bundles', 'coursebundles.tutor.edit-course-bundle','coursebundles.tutor.create-course-bundle'],
                    'title' => __('sidebar.coursebundles'),
                    'icon'  => '<i class="am-icon-layer-04"></i>',
                    'accessibility' => ['tutor'],
                    ] 
                ];
                break;
            case 'student':
                return [
                    [
                        'studentSortOrder' => 4,
                        'route' => 'coursebundles.course-bundles',   
                        'onActiveRoute' => [],
                        'title' => __('sidebar.find_coursebundles'),
                        'icon'  => '<i class="am-icon-certificate">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.5 3.96429C2.5 1.78814 4.23508 0 6.40478 0H17.5952C19.7649 0 21.5 1.78815 21.5 3.96429V18.8214C21.5 19.0468 21.4006 19.2489 21.2433 19.3864C21.2263 19.4019 21.2083 19.4169 21.1893 19.4313L21.1893 19.4313L21.049 19.5377C20.0672 20.2825 20.0669 21.7897 21.0484 22.5349C21.6452 22.9879 21.3683 24 20.5566 24H6.20458C4.14603 24 2.50319 22.2999 2.51013 20.2372L2.5144 18.9684C2.50495 18.9209 2.5 18.8717 2.5 18.8214V3.96429ZM4.01238 19.5714L4.01012 20.2422C4.00589 21.5012 5.00286 22.5 6.20458 22.5H19.1401C18.703 21.5789 18.7032 20.4923 19.1409 19.5714H4.01238ZM20 18.0714H4V3.96429C4 2.59002 5.08981 1.5 6.40478 1.5H17.5952C18.9102 1.5 20 2.59002 20 3.96429V18.0714ZM11.6403 5.04127C9.28792 5.04127 7.35712 6.98815 7.35712 9.41939C7.35712 11.8506 9.28792 13.7975 11.6403 13.7975C13.9927 13.7975 15.9235 11.8506 15.9235 9.41939C15.9235 6.98815 13.9927 5.04127 11.6403 5.04127ZM5.85712 9.41939C5.85712 6.18627 8.4332 3.54127 11.6403 3.54127C14.8474 3.54127 17.4235 6.18627 17.4235 9.41939C17.4235 10.7695 16.9743 12.017 16.2175 13.0123L17.9277 14.7548C18.2179 15.0504 18.2135 15.5252 17.9178 15.8154C17.6222 16.1055 17.1474 16.1011 16.8572 15.8055L15.1645 14.0808C14.1905 14.8431 12.9696 15.2975 11.6403 15.2975C8.4332 15.2975 5.85712 12.6525 5.85712 9.41939Z" fill="#585858"/>
                                        </svg>
                                    </i>',
                        'accessibility' => ['student'],
                    ] 
                ];
                break;
            case 'admin':
                return [
                    [
                        'title' =>  __('coursebundles::bundles.manage_bundles'),
                        'icon'  => 'icon-layers',
                        'permission' => 'edit course bundles,create course bundles,manage course bundles',
                        'routes' => [
                            [
                                'route' => 'coursebundles.admin.course-bundles-list',
                                'title' => __('coursebundles::bundles.all_bundles'),
                            ],
                            [
                                'route' => 'coursebundles.admin.create-course-bundle',
                                'title' => __('coursebundles::bundles.all_bundles'),
                            ],
                            [
                                'route' => 'coursebundles.admin.edit-course-bundle',
                                'title' => __('coursebundles::bundles.all_bundles'),
                            ]
                        ],
                    ]
                ];
                break;
            default:
                return [];
        }
    }
}

if (!function_exists('setMediaPath')) {

    function setMediaPath($questionMedia = null)
    {
        $questionMediaPath  = null;
        if (!empty($questionMedia) && $questionMedia instanceof \Illuminate\Http\UploadedFile) {
            $mediaName = Str::random(30) . '.' . $questionMedia->getClientOriginalExtension();
            $questionMediaPath = $questionMedia->storeAs('course_bundles', $mediaName, getStorageDisk());
        } elseif (is_string($questionMedia)) {
            $questionMediaPath = $questionMedia;
        }
        return  $questionMediaPath;
    }
}

<?php

use Illuminate\Support\Str;

if (!function_exists('assignmentMenuOptions')) {
    function assignmentMenuOptions($role)
    {
        $route = 'assignments.' . auth()->user()->role . '.';

        switch ($role) {
            case 'tutor':
                return [
                    [
                        'tutorSortOrder' => 4,
                        'onActiveRoute' => [
                            $route . 'assignments-list',
                            $route . 'create-assignment',
                            $route . 'update-assignment',
                            $route . 'submissions-assignments-list',
                        ],
                        'route' => 'assignments.tutor.assignments-list',
                        'title' => __('assignments::assignments.assignments'),
                        'icon'  => '<svg width="24" height="25" viewBox="0 0 24 25" fill="none">
                                        <path d="M13.5845 2.96484H8.75391C6.54477 2.96484 4.75391 4.74937 4.75391 6.95069V18.9082C4.75391 21.1095 6.54477 22.8941 8.75391 22.8941M13.5845 2.96484V6.61854C13.5845 7.7192 14.48 8.61146 15.5845 8.61146L19.2524 8.61233M13.5845 2.96484L19.2524 8.61233M19.2524 8.61233V10.5887M8.00565 9.71772H11.0056M8.00565 13.5303H13.0056M16.304 15.503L12.708 20.0637C12.4856 20.3456 12.3436 20.6821 12.2971 21.0377L12.0451 22.9648L13.8793 22.0988C14.1589 21.9667 14.404 21.7718 14.5952 21.5294L18.2403 16.9065M16.304 15.503L18.2403 16.9065M16.304 15.503L17.1243 14.3793C17.4492 13.9343 18.0746 13.836 18.5212 14.1597L18.8402 14.3909C19.2868 14.7147 19.3855 15.3379 19.0606 15.7829L18.2403 16.9065" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>',
                        'accessibility' => ['tutor'],
                        'disableNavigate' => true,
                    ]
                ];
                break;
            case 'student':
                return [
                    [
                        'studentSortOrder' => 4,
                        'onActiveRoute' => [
                            $route . 'student-assignments',

                        ],
                        'route' => 'assignments.student.student-assignments',
                        'title' => __('assignments::assignments.assignments'),
                        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M13.3332 17.9167V16.5333C13.3332 15.4132 13.3332 14.8531 13.5512 14.4253C13.7429 14.049 14.0489 13.743 14.4252 13.5513C14.853 13.3333 15.4131 13.3333 16.5332 13.3333H17.9165M5.83317 5.83332H12.4998M5.83317 9.16666H10.8332M5.83317 12.5H7.49984M13.0116 18.3333H6.4665C4.78635 18.3333 3.94627 18.3333 3.30453 18.0063C2.74005 17.7187 2.2811 17.2598 1.99348 16.6953C1.6665 16.0536 1.6665 15.2135 1.6665 13.5333V6.46666C1.6665 4.7865 1.6665 3.94642 1.99348 3.30468C2.2811 2.7402 2.74005 2.28126 3.30453 1.99364C3.94627 1.66666 4.78635 1.66666 6.4665 1.66666H13.5332C15.2133 1.66666 16.0534 1.66666 16.6951 1.99364C17.2596 2.28126 17.7186 2.7402 18.0062 3.30468C18.3332 3.94642 18.3332 4.7865 18.3332 6.46666V13.0118C18.3332 13.7455 18.3332 14.1124 18.2503 14.4577C18.1768 14.7638 18.0556 15.0564 17.8911 15.3248C17.7056 15.6276 17.4461 15.887 16.9273 16.4059L16.4057 16.9274C15.8869 17.4463 15.6274 17.7057 15.3247 17.8912C15.0563 18.0557 14.7636 18.1769 14.4575 18.2504C14.1123 18.3333 13.7454 18.3333 13.0116 18.3333Z" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>',
                        'accessibility' => ['student'],
                        'disableNavigate' => true,
                    ]
                ];
                break;
            case 'admin':
                return [];
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
            $questionMediaPath = $questionMedia->storeAs('assignments', $mediaName, getStorageDisk());
        } elseif (is_string($questionMedia)) {
            $questionMediaPath = $questionMedia;
        }
        return  $questionMediaPath;
    }
}

if (!function_exists('humanFilesize')) {

    function humanFilesize($bytes, $decimals = 1) {
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sizes[$factor];
}

}

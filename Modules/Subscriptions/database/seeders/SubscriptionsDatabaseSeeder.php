<?php

namespace Modules\Subscriptions\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\NotificationTemplate;
use App\Models\User;
use Modules\Subscriptions\Models\Subscription;
use Nwidart\Modules\Facades\Module;
use Larabuild\Pagebuilder\Models\Page;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\PageBuilderService;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SubscriptionsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $pageBuilderService;
    public function __construct()
    {
        $this->pageBuilderService = new PageBuilderService();
    }

    public function run(): void
    {
        $this->setEmailTemplates();
        $this->setNotificationTemplates();
        $this->setSubscription();
   
        $pages = [
            [
                'name' => 'Student Subscriptions',
                'slug' => 'student-subscriptions',
                'title' => 'Student Subscriptions | Lernen',
                'description' => 'Student Subscriptions | Lernen',
                'settings' => null,
                'status' => 'published',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Tutor Subscriptions',
                'slug' => 'tutor-subscriptions',
                'title' => 'Tutor Subscriptions | Lernen',
                'description' => 'Tutor Subscriptions | Lernen',
                'settings' => null,
                'status' => 'published',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach($pages as $page){
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }

        $sitePages = Page::whereIn('slug', ['tutor-subscriptions', 'student-subscriptions'])->get();

        if (!empty($sitePages)) {
            foreach ($sitePages as $page) {
                $pageName = preg_replace("/[^A-zÀ-ú0-9]+/", "", str_replace(' ', '', $page->name));
                $page->settings  = $this->{'get' . $pageName . 'Settings'}($page);
                $page->save();
            }
        }
        $this->addSubscriptionMenu();
    }

    public function addSubscriptionMenu()
    {
        $subscriptionMenu = MenuItem::whereHas('menu', fn($query) => $query->whereLocation('header'))->where('label', 'Subscriptions')->first();
        $headerMenu = Menu::whereLocation('header')->select('id')->latest()->first();
        if (empty($subscriptionMenu) && !empty($headerMenu)) {
            $subscriptionMenu = MenuItem::create([
                'menu_id'   => $headerMenu->id,
                'label'     => 'Subscriptions',
                'parent_id' => null,
                'route'     => '#',
                'type'      => 'page',
                'sort'      => '5',
            ]);
        }
        if (!empty($subscriptionMenu) && !empty($headerMenu)) {
            MenuItem::firstOrCreate(
                [
                    'menu_id'   => $headerMenu->id,
                    'parent_id' => $subscriptionMenu->id,
                    'label'     => 'Student Subscriptions',
                ],
                [
                    'route'     => url('student-subscriptions'),
                    'type'      => 'page',
                    'sort'      => '3',
                    'class'     => '',
                ]
            );
            MenuItem::firstOrCreate(
                [
                    'menu_id'   => $headerMenu->id,
                    'parent_id' => $subscriptionMenu->id,
                    'label'     => 'Tutor Subscriptions',
                ],
                [
                    'route'     => url('tutor-subscriptions'),
                    'type'      => 'page',
                    'sort'      => '4',
                    'class'     => '',
                ]
            );
        }
        Artisan::call('cache:clear');
    }
    

    private function setSubscription()
    {
        Subscription::truncate();
        $subscriptions = [
            'student' => [
                [
                    'name' => 'Basic',
                    'description' => 'Gain access to essential features and tools to get started. Perfect for beginners looking to explore the platform.',
                    'price' => 499.99,
                    'period' => 'monthly',
                    'image'  => 'basic.png',   
                    'credit_limits' => [
                        'sessions' => 5,
                    ],
                    'revenue_share' => [
                        'admin_share' => 10,
                        'sessions_share' => 100,
                    ],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ],
                [
                    'name' => 'Advanced',
                    'description' => 'Unlock additional capabilities with enhanced tools and resources. Ideal for users ready to take it to the next level.',
                    'price' => 799.99,
                    'period' => '6_months',
                    'image'  => 'advanced.png',   
                    'credit_limits' => [
                        'sessions' => 8,
                    ],
                    'revenue_share' => [
                        'admin_share' => 10,
                        'sessions_share' => 100,
                    ],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ],
                [
                    'name' => 'Premium',
                    'description' => 'Experience the full power of the platform with exclusive features and priority support. Tailored for professionals and power users.',
                    'price' => 999.99,
                    'period' => 'yearly',
                    'image'  => 'premium.png',   
                    'credit_limits' => [
                        'sessions' => 10,
                    ],
                    'revenue_share' => [
                        'admin_share' => 10,
                        'sessions_share' => 100,
                    ],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ]
            ],
            'tutor' => [
                [
                    'name' => 'Basic',
                    'description' => 'Gain access to essential features and tools to get started. Perfect for beginners looking to explore the platform.',
                    'price' => 499.99,
                    'period' => 'monthly',
                    'image'  => 'basic.png',   
                    'credit_limits' => [
                        'sessions' => 5,
                    ],
                    'revenue_share' => [],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ],
                [
                    'name' => 'Advanced',
                    'description' => 'Unlock additional capabilities with enhanced tools and resources. Ideal for users ready to take it to the next level.',
                    'price' => 799.99,
                    'period' => '6_months',
                    'image'  => 'advanced.png',   
                    'credit_limits' => [
                        'sessions' => 8,
                    ],
                    'revenue_share' => [],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ],
                [
                    'name' => 'Premium',
                    'description' => 'Experience the full power of the platform with exclusive features and priority support. Tailored for professionals and power users.',
                    'price' => 999.99,
                    'period' => 'yearly',
                    'image'  => 'premium.png',   
                    'credit_limits' => [
                        'sessions' => 10,
                    ],
                    'revenue_share' => [],
                    'auto_renew' => 'yes',
                    'status' => 'active',
                    'created_by' => User::admin()->id,
                ]
            ],
        ];
        foreach ($subscriptions as $type => $subscription) {
            foreach ($subscription as $sub) {
                $sub['role_id'] = getRoleByName($type);
                if(Module::has('courses') && Module::isEnabled('courses')){
                    $sub['credit_limits']['courses'] = $sub['period'] == 'monthly' ? 10 : ($sub['period'] == '6_months' ? 30 : 50);
                    if($type == 'student'){
                        $sub['revenue_share']['admin_share'] = 10;
                        $sub['revenue_share']['courses_share'] = 50;
                        $sub['revenue_share']['sessions_share'] = 50;
                    }
                }
                if (!Storage::disk(getStorageDisk())->exists('subscriptions')) {
                    Storage::disk(getStorageDisk())->makeDirectory('subscriptions');
                }
                $sourceFile = new File(public_path('modules/subscriptions/demo-content/'.$sub['image']));
                $sub['image'] = Storage::disk(getStorageDisk())->putFileAs('subscriptions', $sourceFile, $sub['image']);
                Subscription::create($sub);
            }
        }
    }

    private function setEmailTemplates()
    {
        $emailTemplates = $this->getEmailTemplates();

        foreach ($emailTemplates as $type => $template) {
            foreach (!empty($template['roles']) ? $template['roles'] : [] as $role => $data) {
                EmailTemplate::updateOrCreate(
                    ['role' => $role, 'type' => $type],
                    [
                        'title' => $template['title'],
                        'content' => ['info' => $data['fields']['info']['desc'], 'subject' => $data['fields']['subject']['default'], 'greeting' => $data['fields']['greeting']['default'], 'content' => $data['fields']['content']['default']]
                    ]
                );
            }
        }
    }

    private function setNotificationTemplates()
    {
        $notificationTemplates = $this->getNotificationTemplates();

        foreach ($notificationTemplates as $type => $template) {

            foreach (!empty($template['roles']) ? $template['roles'] : [] as $role => $data) {
                NotificationTemplate::updateOrCreate([
                    'type' => $type,
                    'title' => $template['title'],
                    'role' => $role,
                    'content' => ['info' => $data['fields']['info']['desc'], 'subject' => $data['fields']['subject']['default'], 'content' => $data['fields']['content']['default']]
                ]);
            }
        }
    }
 
    private function getEmailTemplates()
    {
        return
            [
                'sessionBooking' => [
                    'version' => '2.0.2',
                    'title' => __('subscriptions::subscription.session_booking_title'),
                    'roles' => [
                        'student' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.subscription_purchase_student_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.subscription_purchase'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{studentName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.subscription_purchase_msg', ['userName' => '{studentName}', 'tutorName' => '{tutorName}', 'bookingDetails' => '{bookingDetails}']),
                                ],
                            ],
                        ],
                        'tutor' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.subscription_purchase_tutor_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.subscription_purchase'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{tutorName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.subscription_purchase_msg', ['userName' => '{tutorName}', 'studentName' => '{studentName}', 'bookingDetails' => '{bookingDetails}']),
                                ],
                            ],
                        ],
                    ],
                ],
                'renewSubscription' => [
                    'version' => '2.1.1',
                    'title' => __('subscriptions::subscription.renew_subscription_title'),
                    'roles' => [
                        'student' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.renew_subscription_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.renew_subscription'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{userName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.renew_subscription_content', ['subscriptionName' => '{subscriptionName}', 'subscriptionExpiry' => '{subscriptionExpiry}', 'renewalLink' => '{renewalLink}']),
                                ],
                            ],
                        ],
                        'tutor' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.renew_subscription_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.renew_subscription'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{userName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.renew_subscription_content', ['subscriptionName' => '{subscriptionName}', 'subscriptionExpiry' => '{subscriptionExpiry}', 'renewalLink' => '{renewalLink}']),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
    }

    private function getNotificationTemplates()
    {
        return
            [
                'renewSubscription' => [
                    'version' => '2.1.7',
                    'title' => __('subscriptions::subscription.renew_subscription_title'),
                    'roles' => [
                        'student' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.renew_subscription_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.renew_subscription'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{userName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.renew_subscription_content', ['subscriptionName' => '{subscriptionName}', 'subscriptionExpiry' => '{subscriptionExpiry}', 'renewalLink' => '{renewalLink}']),
                                ],
                            ],
                        ],
                        'tutor' => [
                            'fields' => [
                                'info' => [
                                    'title' => __('subscriptions::subscription.variables_used'),
                                    'icon' => 'icon-info',
                                    'desc' => __('subscriptions::subscription.renew_subscription_variables'),
                                ],
                                'subject' => [
                                    'id' => 'subject',
                                    'title' => __('subscriptions::subscription.subject'),
                                    'default' => __('subscriptions::subscription.renew_subscription'),
                                ],
                                'greeting' => [
                                    'id' => 'greeting',
                                    'title' => __('subscriptions::subscription.greeting_text'),
                                    'default' => __('subscriptions::subscription.greeting', ['userName' => '{userName}']),
                                ],
                                'content' => [
                                    'id' => 'content',
                                    'title' => __('subscriptions::subscription.email_content'),
                                    'default' => __('subscriptions::subscription.renew_subscription_content', ['subscriptionName' => '{subscriptionName}', 'subscriptionExpiry' => '{subscriptionExpiry}', 'renewalLink' => '{renewalLink}']),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
    }

    private function getStudentSubscriptionsSettings($page)
    {
        $pageData = [];
        $sections = [
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-banner']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscriptions']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-steps']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-faqs']]],
        ];

        foreach ($sections as $gridData) {
            $gridPosition = 0;
            $gridId       = $this->uniqueId();
            $data         = [];
            foreach ($gridData['data'] as $col => $colSection) {
                $sectionPosition = 0;
                foreach ($colSection as $section) {
                    $sectionId = $this->uniqueId();
                    $data[$col][] = ['id' => $sectionId, 'section_id' => $section, 'position' => $sectionPosition];
                    $parseFunction = (string)"get" . Str::ucfirst(Str::camel($section)) . "Data";
                    $pageData['section_data'][$sectionId]['settings'] = $this->$parseFunction($page);
                }
                $data;
                $pageData['section_data'][$gridId]['styles'] = array_merge($this->defaultStyles(), $gridData['styles']);
                $gridPosition++;
            }
            $pageData['grids'][] = ['grid' => $gridData['grid'], 'position' => $gridPosition, 'grid_id' => $gridId, 'data' => $data];
        }
       
        return $pageData;
    }

    private function getTutorSubscriptionsSettings($page)
    {
        $pageData = [];
        $sections = [
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-banner']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscriptions']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-steps']]],
            ['grid' => '12x1', 'styles' => ['content_width' => 'full_width'], 'data' => [['subscription-faqs']]],
        ];

        foreach ($sections as $gridData) {
            $gridPosition = 0;
            $gridId       = $this->uniqueId();
            $data         = [];
            foreach ($gridData['data'] as $col => $colSection) {
                $sectionPosition = 0;
                foreach ($colSection as $section) {
                    $sectionId = $this->uniqueId();
                    $data[$col][] = ['id' => $sectionId, 'section_id' => $section, 'position' => $sectionPosition];
                    $parseFunction = (string)"get" . Str::ucfirst(Str::camel($section)) . "Data";
                    $pageData['section_data'][$sectionId]['settings'] = $this->$parseFunction($page);
                }
                $data;
                $pageData['section_data'][$gridId]['styles'] = array_merge($this->defaultStyles(), $gridData['styles']);
                $gridPosition++;
            }
            $pageData['grids'][] = ['grid' => $gridData['grid'], 'position' => $gridPosition, 'grid_id' => $gridId, 'data' => $data];
        }
       
        return $pageData;
    }

    private function getSubscriptionBannerData($page)
    {
        return $this->pageBuilderService->getSubscriptionBannerData($page->id);
    }

    private function getSubscriptionStepsData($page)
    {
        return $this->pageBuilderService->getSubscriptionStepsData($page->id);
    }


    private function getSubscriptionsData($page)
    {
        return $this->pageBuilderService->getSubscriptionsData($page->id);
    }

    private function getLimitlessFeaturesData($page)
    {
        return $this->pageBuilderService->getLimitlessFeaturesData($page->id);
    }

    private function getContentBannerData($page)
    {
        return $this->pageBuilderService->getContentBannerData($page->id);
    }

    private function getSubscriptionFaqsData($page)
    {
        return $this->pageBuilderService->getSubscriptionFaqsData($page->id);
    }

    public function uniqueId()
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($str_result), 0, 10);
    }

    public function defaultStyles()
    {
        return [
            'content_width'     => '',
            'width-height-type' => 'px',
            'width'             => '',
            'height'            => '',
            'min-width'         => '',
            'max-width'         => '',
            'min-height'        => '',
            'max-height'        => '',
            'margin-type'       => 'px',
            'margin-top'        => '',
            'margin-right'      => '',
            'margin-bottom'     => '',
            'margin-left'       => '',
            'padding-type'      => 'px',
            'padding-top'       => '',
            'padding-right'     => '',
            'padding-bottom'    => '',
            'padding-left'      => '',
            'z-index'           => '',
            'classes'           => '',
            'background-size'   => '',
            'background-position' => '',
        ];
    }
}



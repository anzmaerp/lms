<?php

namespace Modules\CourseBundles\database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Modules\Courses\Models\Media;
use Modules\Courses\Models\Course;
use Illuminate\Support\Facades\Storage;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Models\CourseBundle;


use Illuminate\Support\Str;
use Larabuild\Optionbuilder\Facades\Settings;


class CourseBundlesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // if(!isDemoSite()) {
        //     $this->command->error('This seeder can only be used on demo site.');
        //     return;
        // }

        $disk = getStorageDisk();
        $files = Storage::disk($disk)->allFiles('course_bundles');
        Storage::disk($disk)->delete($files);

        Schema::disableForeignKeyConstraints();

        Bundle::truncate();
        CourseBundle::truncate();
        Media::where('mediable_type', Bundle::class)->delete();

        $this->seedSettings();
        $this->seeddBundles();

        Schema::enableForeignKeyConstraints();
    }
    
    private function seedSettings()
    {
        $settings = [
            '_coursebundle' => [
                'comission_setting'                      => 10,
                'clear_course_bundle_amount_after_days'  => 3,
                'course_bundle_banner_image'             => [uploadObMedia('modules/coursebundles/demo-content/banner-shape3.png')],
                'course_bundle_heading'                  => 'Exclusive Course Bundles',
                'course_bundle_description'              => 'Enhance student engagement by packaging multiple courses into a single, compelling bundle.',
            ]
        ];

        foreach ($settings as $section => $values) {
            foreach($values as $key => $value) {
                Settings::set($section, $key, $value);
            }
        }
        Artisan::call('cache:clear');
    }

    private function seeddBundles()
    {
        $description  = '<p>The Web Development Mastery Bundle is an all-in-one learning package designed to take you from a beginner to a professional web developer. Whether you want to master front-end design, back-end development, or full-stack applications, this bundle provides everything you need to build dynamic, responsive, and high-performance websites.</p>

                        <h3>Who Is This For?</h3>
                        <ul>
                            <li>Beginners looking to start their web development journey.</li>
                            <li>Developers who want to level up their front-end and back-end skills.</li>
                            <li>Professionals looking to switch careers into web development.</li>
                            <li>Freelancers & entrepreneurs who want to build and deploy their own websites or web apps.</li>
                        </ul>
                        <p>The Web Development Mastery Bundle is an all-in-one learning package designed to take you from a beginner to a professional web developer. Whether you want to master front-end design, back-end development, or full-stack applications, this bundle provides everything you need to build dynamic, responsive, and high-performance websites. The Web Development Mastery Bundle is an all-in-one learning package designed to take you from a beginner to a professional web developer.</p>';
        $bundlesData = [
            [
                'title'                 => 'Web Development Mastery',
                'short_description'     => 'Learn the latest web development technologies and build real-world projects.',
            ],
            [
                'title'                 => 'Data Science Essentials',
                'short_description'     => 'Master the fundamentals of data science and machine learning.',
            ],
            [
                'title'                 => 'Cybersecurity & Ethical Hacking',
                'short_description'     => 'Protect systems and understand ethical hacking principles.',
            ],
            [
                'title'                 => 'Digital Marketing & SEO',
                'short_description'     => 'Boost online presence and grow businesses with digital marketing strategies.',
            ],
            [
                'title'                 => 'Full Stack Development',
                'short_description'     => 'Build complete web applications with front-end and back-end technologies.',
            ],
            [
                'title'                 => 'Learn Spanish for Beginners',
                'short_description'     => 'Learn Spanish language for beginners.',
            ],

        ];

        $courses = Course::where('status', Course::STATUSES['active'])->get();

        if ($courses->isEmpty()) {
            $this->command->error('Not courses available. Please add courses.');
            return;
        }

       
        $instructor = User::where('email', 'anthony@amentotech.com')->first();

        if(empty($instructor)) {
            $this->command->error('Instructor not found.');
            return;
        }

        foreach($bundlesData as $data) {
             $bundle = Bundle::create([
                'instructor_id'             => $instructor->id,
                'title'                     => $data['title'],
                'short_description'         => $data['short_description'],
                'description'               => $description,
                'price'                     => rand(50, 200),
                'discount_percentage'       => rand(0, 1) > 0 ? rand(10, 50) : 0,
                'status'                    => 'published',
            ]);

            $coursesIds = $courses->random(rand(3,5))->pluck('id')->toArray();
            $bundle->courses()->sync($coursesIds);

            $image = $this->addImage($bundle->id);

            Media::create([
                'mediable_id' => $bundle->id,
                'mediable_type' => Bundle::class,
                'type' => 'thumbnail',
                'path' => $image,
            ]);
            
            
        }

        $this->command->info('Course Bundles created successfully.');
        return;
    }

    private function addImage($bundleId)
    {
        $assetNumber = $bundleId;
        $imageName = Str::random(30) . '.png';

        if (!Storage::disk(getStorageDisk())->exists('course_bundles')) {
            Storage::disk(getStorageDisk())->makeDirectory('course_bundles');
        }

        Storage::disk(getStorageDisk())->putFileAs('course_bundles', public_path('modules/coursebundles/demo-content/bundle-' . $assetNumber . '.png'), $imageName);

        return 'course_bundles/' . $imageName;
    }
}



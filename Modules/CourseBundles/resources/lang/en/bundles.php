<?php

$image_size         = (int) (setting('_general.max_image_size') ?? '5');
$image_file_ext     = setting('_general.allowed_image_extensions') ?? 'jpg,png';

return [
    // Basic details
    'course_bundle'                                     => 'Course Bundle',
    'create_bundle_desc'                                => 'Offer curated learning experiences with bundled courses.',
    'bundle_title'                                      => 'Bundle Title',
    'bundle_title_placeholder'                          => 'Add title',
    'select_courses'                                    => 'Select Courses',
    'bundle_thumbnail'                                  => 'Bundle Thumbnail',
    'bundle_short_description'                          => 'Short description',
    'bundle_short_description_placeholder'              => 'Write short description',
    'per_bundle'                                        => '/bundle',

    // Tooltip and Placeholder Texts
    'select_courses_placeholder'                        => 'Select Courses',
    'tooltip_time_limit'                                => 'Select the courses you want to include in this bundle. This ensures that learners receive access to the right courses as part of their purchase.',

    // File Upload Texts
    'drop_file_here'                                    => 'Drop File Here',
    'upload_file_text'                                  => 'Drop a file here or <i>click here</i> to upload',
    'upload_file_format'                                => implode(', ', explode(',', $image_file_ext)) . ' (max. 800x400px) size:' . $image_size . 'MB',

    // Pricing Section
    'pricing'                                           => 'Pricing',
    'pricing_desc'                                      => 'Set the course bundle\'s sale price, and optional discounts to attract more learners.',
    'regular_price'                                     => 'Regular Price',
    'sale_price'                                        => 'Bundle Sale Price',
    'allow_discount'                                    => 'Allow Discount',
    'optional'                                          => '(Optional)',

    // Discount Section
    'choose_discount_amount'                            => 'Choose discount amount',
    'discount_description'                              => 'Select a discount percentage to adjust the final purchase price for this bundle to attract more learners.',
    'original_price'                                    => 'Original price',

    // Discount Table Headers
    'discount_amount'                                   => 'Discount amount',
    'discount_price'                                    => 'Discount price',
    'purchase_price'                                    => 'Purchase price',

    // Bundle Description Section
    'bundle_description'                                => 'Bundle Description',
    'bundle_description_desc'                           => 'Extra details to enhance your insights.',
    'bundle_description_placeholder'                    => 'Add description',

    // Action Buttons
    'back'                                              => 'Back',
    'save'                                              => 'Save',
    'show'                                              => 'Show',
    'listing_per_page'                                  => 'listings per page',

    // messages
    'off'                                               => '% OFF',
    'of'                                                => 'of',
    'hrs'                                               => 'hrs',
    'mins'                                              => 'mins',
    'min'                                               => 'min',
    'hr'                                                => 'hr',
    'active_students'                                   => 'Active students',
    'active_student'                                    => 'Active student',
    'publish_bundle_confirmation_title'                 => 'Publish Course Bundle Confirmation!',
    'publish_bundle_confirmation_desc'                  => 'Your course bundle is ready! Check the details and confirm to publish.',
    'publish'                                           => 'Publish',
    'view_course'                                       => 'View Course',
    'add_to_cart'                                       => 'Add to Cart',
    'you_may_also_like'                                 => 'You may also like',
    'description'                                       => 'Description',
    'native'                                            => 'Native',
    'i_can_speak'                                       => 'I can speak',
    'more'                                              => 'more',
    'reviews'                                           => 'Reviews',
    'lessons'                                           => 'Lessons',
    'articles'                                          => 'Articles',
    'videos'                                            => 'videos',
    'duration'                                          => 'Duration',
    'created_at'                                        => 'Created at',
    'include_courses'                                   => 'Include Courses',
    'create_bundle'                                     => 'Course Bundle Created Successfully',
    'create_bundle_error'                               => 'Course Bundle Creation Failed',
    'success_title'                                     => 'Success',
    'error_title'                                       => 'Error',
    'draft'                                             => 'Draft',
    'published'                                         => 'Published',
    'archived'                                          => 'Archived',
    'Course'                                            => 'Course',
    'Courses'                                           => 'Courses',
    'course_bundle'                                     => 'Course Bundle',
    'search'                                            => 'Search',
    'home'                                              => 'Home',
    'course_bundles'                                    => 'Course Bundles',
    'search_by_keywords'                                => 'Search by keywords',

    // bundle listing
    'created_at'                                        => 'Created at',
    'published_bundles'                                 => 'Published Bundles',
    'course_bundles_desc'                               => 'Make learning more accessible and rewarding by bundling courses into thoughtfully curated packages.',
    'create_a_new_bundle'                               => 'Create a New Bundle',
    'search_by_keyword'                                 => 'Search by keyword',

    'bundle_status_draft'                               => 'Draft',
    'bundle_status_published'                           => 'Published',
    'bundle_status_archived'                            => 'Archived',
    'all'                                               => 'All',

    // empty view
    'no_bundles_found'                                  => 'No results found!',
    'all_bundles'                                       => 'All bundles',
    'short_description'                                 => 'Short Description',
    'all_bundles'                                       => 'All bundles',
    'update_bundle'                                     => 'Course Bundle updated successfully!',
    'purchased_bundles'                                 => 'This bundle has been Purchased',
    'final_price'                                       => 'Final Price',
    'manage_bundles'                                    => 'Manage Bundles',
    'empty_view_description'                            => 'Sorry, we couldn\'t find any course bundles matching your search.',
    'no_courses_bundles'                                => 'No Course Bundles Yet!',
    'no_courses_bundles_desc'                           => 'Organize your courses into bundles to offer students more value. Start by creating your first bundle now!',
    'view_bundle'                                       => 'View bundle',
    'edit_bundle'                                       => 'Edit bundle',
    'publish_bundle'                                    => 'Publish bundle',
    'archive_bundle'                                    => 'Archive bundle',
    'delete_bundle'                                     => 'Delete bundle',

    'bundle_status_success'                             => 'Bundle :status successfully!',
    'bundle_deleted_successfully'                       => 'Bundle deleted successfully!',
    'bundle_delete_error'                               => 'Bundle couldn\'t be deleted!',

    'bundle_detail'                                     => 'Bundle Detail',
    'only_student_can_add_to_cart'                      => 'Only students can add to cart',
    'only_student_can_get_bundle'                      => 'Only students can get bundle',


    // settings
    'coursebundle_settings'                             => 'Course Bundle Settings',
    'commission_settings'                               => 'Commission settings',
    'commission_settings_desc'                          => 'Configure commission settings for course bundles. The value will be in percentage (%).',
    'commission_settings_placeholder'                   => 'Enter commission',
    'clear_course_bundle_amount_after_days'             => 'Clear course bundle amount after days',
    'clear_course_bundle_amount_after_days_desc'        => 'Configure the number of days after which the course bundle amount will be cleared.',
    'clear_course_bundle_amount_after_days_placeholder' => 'Enter days',
    'course_bundle_banner_image'                        => 'Course bundle banner image',
    'course_bundle_banner_image_desc'                   => 'Upload a banner image for the course bundle. This will be displayed on the bundle details page.',
    'course_bundle_heading'                             => 'Course bundle heading',
    'course_bundle_heading_desc'                        => 'Enter a heading for the course bundle. This will be displayed on the search bundle page.',
    'course_bundle_heading_placeholder'                 => 'Enter heading',
    'course_bundle_description'                         => 'Course bundle description',
    'course_bundle_description_desc'                    => 'Enter a description for the course bundle. This will be displayed on the search bundle page.',
    'course_bundle_description_placeholder'             => 'Enter description',
    'free'                                              => 'Free',
    'get_bundle'                                        => 'Get Bundle',
    'bundle_not_found'                                  => 'Bundle not found',
    'select_instructor' => 'Select Instructor',
    'choose_instructor' => 'Choose Instructor',
    'created_by' => 'Created By'

];

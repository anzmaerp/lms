<main class="tb-main am-dispute-system am-bundle-system">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4>{{ __('coursebundles::bundles.all_bundles') . ' (' . $bundles->total() . ')' }}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="filters.keyword"
                                        autocomplete="off" placeholder="{{ __('courses::courses.search_by_keyword') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    @if (!$bundles->isEmpty())
                        <table class="tb-table">
                            <thead>
                                <tr>
                                    <th>{{ __('courses::courses.id') }}</th>
                                    <th>{{ __('courses::courses.title') }}</th>
                                    <th>{{ __('coursebundles::bundles.short_description') }}</th>
                                    <th>{{ __('coursebundles::bundles.final_price') }}</th>
                                    <th>{{ __('courses::courses.instructor') }}</th>
                                    <th>{{ __('courses::courses.status') }}</th>
                                    <th>{{ __('courses::courses.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bundles as $bundle)
                                    <tr>
                                        <td data-label="{{ __('courses::courses.id') }}">
                                            <span>{{ $bundle?->id }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.title') }}">
                                            <span>{{ $bundle?->title }} ({{ __('courses::courses.courses') }} {{ $bundle?->courses_count }})</span>
                                        </td>
                                        <td data-label="{{ __('coursebundles::bundles.short_description') }}">
                                            <span>{{ $bundle?->short_description }}</span>
                                        </td>
                                        <td data-label="{{ __('coursebundles::bundles.final_price') }}">
                                            <span>{{ formatAmount($bundle?->final_price) }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.instructor') }}">
                                            <span>{{ $bundle->instructors?->profile?->full_name }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.status') }}">
                                            <div class="am-status-tag">
                                                <em class='tk-project-tag tk-active'>{{ __('coursebundles::bundles.' . $bundle?->status) }}</em>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('courses::courses.actions') }}">
                                            <ul class="tb-action-icon">
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('courses::courses.view_details') }}</span>
                                                        <a href="{{ route('coursebundles.bundle-details', ['slug' => $bundle->slug]) }}" target="_blank">
                                                            <i class="icon-eye"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('courses::courses.remove_course') }}</span>
                                                         <a href="javascript:void(0);" @click="$wire.dispatch('showConfirm', { id : {{ $bundle->id }}, action : 'delete-course-bundle' })"  class="tb-delete"><i class="icon-trash-2"></i></a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $bundles->links('pagination.custom') }}
                    @else
                        <x-no-record :image="asset('images/empty.png')" :title="__('courses::courses.no_records_found')" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>


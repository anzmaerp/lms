<div class="tb-dbholder tb-packege-setting">
    <div class="tb-dbbox tb-dbboxtitle">
        <h5>
            {{ $edit ? __('general.update_page') : __('general.add_new_page') }}    
        </h5>
    </div>
    <div class="tb-dbbox" id="add_edit">
        <form class="tb-themeform" id="page_form">
            @csrf
            <fieldset>
                <div class="tb-themeform__wrap">

                    <div class="form-group">
                        <label class="tb-label">{{ __('general.page_name') }}</label>
                        <input type="text" class="form-control @error('name') tk-invalid @enderror" name="name"
                            value="{{$page->name??null}}" id="name"
                            placeholder="{{ __('general.page_name') }}">
                        @if ($edit)
                        <input type="hidden" name="id" id="id" value="{{$page->id??null}}" />
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="tb-label">{{ __('general.page_title') }}</label>
                        <input type="text" class="form-control @error('title') tk-invalid @enderror" name="title"
                            id="title" value="{{$page->title??null}}"
                            placeholder="{{ __('general.page_title') }}">
                    </div>

                    <div class="form-group">
                        <label class="tb-label">{{ __('general.page_description') }}</label>
                        <textarea class="form-control" name="description" id="description"
                            placeholder="{{ __('general.page_description') }}">{{$page->description??null}}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="tb-label">{{ __('general.page_slug') }}</label>
                        <input type="text" class="form-control @error('slug') tk-invalid @enderror" name="slug"
                            id="slug" value="{{$page->slug??null}}"
                            placeholder="{{ __('general.page_slug') }}">
                    </div>

                    @if($edit)
                    <div class="form-group">
                        <label class="tb-label">{{ __('general.status') }}:</label>
                        <div class="tb-email-status">
                            <span> {{__('general.page_status')}} </span>
                            <div class="tb-switchbtn">
                                <label for="tb-pagestatus" class="tb-textdes"><span
                                        id="tb-textdes">{{(($page->status??null)=='published') ?
                                        __('general.active') :
                                        __('general.deactive')}}</span></label>

                                <input type="checkbox" class="tb-checkaction" name="status" id="status" {{(($page->status??null)== 'published')?'checked': ''}} />
                            </div>
                        </div>
                    </div>

                    @endif
                    <div class="form-group tb-dbtnarea">
                        <button class="pb-btn" type="submit" id="form_submit_btn">{{
                            $edit?__('general.update_page'):__('general.add_new_page')}}</button>

                        @if ($edit)
                        <button class="pb-btn goBack" type="button"
                            id="form_submit_btn">{{__('general.back')}}</button>
                        @endif

                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
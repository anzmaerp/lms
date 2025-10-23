<?php

namespace Modules\Courses\Livewire\Pages\Tutor\CourseCreation\Components\ManageCourseContent\Components;

use App\Traits\PrepareForValidation;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Courses\Http\Requests\CurriculumRequest;
use Modules\Courses\Services\CourseService;
use Modules\Courses\Services\CurriculumService;

class Curriculum extends Component
{
    use WithFileUploads, PrepareForValidation;

    public $section;
    public $key;
    public $title;
    public $description;
    public $addCurriculumState = false;
    public $isLoading = false;
    public $type = 'video';
    public $duration;
    public $yt_link;
    public $vm_link;
    public $url;
    public $mediaType = 'video';
    public $curriculumVideo;
    public $curriculumPdf;
    public $allowVideoFileExt = ['mp4'];
    public $allowPdfFileExt = ['pdf'];
    public $videoFileSize = 2048;
    public $pdfFileSize = 2048;
    public $activeCurriculumItem = null;
    public $article_content;
    public $editCurriculumData = null;
    public $isCurriculumEditing = false;
    public $isDeletingCurriculum = false;
    public $content_length;
    public $curriculumId;

    public function mount($section)
    {
        $this->section = $section;
        $file_ext = !empty(setting('_general.allowed_video_extensions')) ? setting('_general.allowed_video_extensions') : 'mp4';
        $this->allowVideoFileExt = explode(',', $file_ext);
        $this->videoFileSize = !empty(setting('_general.max_video_size')) ? setting('_general.max_video_size') * 1024 : 20 * 1024;
        $this->pdfFileSize = !empty(setting('_general.max_pdf_size')) ? setting('_general.max_pdf_size') * 1024 : 10 * 1024;
    }

    public function render()
    {
        $curriculumItems = (new CurriculumService())->getAllCurriculums($this->section->id);

        return view('courses::livewire.tutor.course-creation.components.manage-course-content.components.curriculum', [
            'curriculumItems' => $curriculumItems,
        ]);
    }

    public function updateCurriculumType($type)
    {
        $this->activeCurriculumItem['type'] = $type;
        $this->dispatch('initEditor', target: '#curriculum_des_' . $this->section->id, content: $this->description);
    }

    public function updateCurriculumState($state = false)
    {
        $this->addCurriculumState = $state;
        $this->resetErrorBag();
        if ($state) {
            $this->updateActiveCurriculumItem(null);
            $this->dispatch('initEditor', target: '#curriculum_des_' . $this->section->id, content: $this->description);
        }
    }

    public function updateActiveCurriculumItem($curriculumItem = null)
    {
        $this->activeCurriculumItem = $curriculumItem;
        $this->curriculumVideo = null;
        $this->curriculumPdf = null;
        $this->yt_link = null;
        $this->vm_link = null;
        $this->url = null;
        if ($curriculumItem != null) {
            if ($curriculumItem['type'] === 'yt_link') {
                $this->yt_link = $curriculumItem['media_path'];
            } elseif ($curriculumItem['type'] === 'vm_link') {
                $this->vm_link = $curriculumItem['media_path'];
            } elseif ($curriculumItem['type'] === 'url') {
                $this->url = $curriculumItem['media_path'];
            }
            $this->updateCurriculumState(false);
        }
    }

    public function addCurriculum()
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $validatedData = $this->validate((new CurriculumRequest())->rules() + [
            'curriculumVideo' => 'nullable|file|mimes:' . implode(',', $this->allowVideoFileExt) . '|max:' . $this->videoFileSize,
            'curriculumPdf' => 'nullable|file|mimes:pdf|max:' . $this->pdfFileSize,
            'url' => 'nullable|url',
        ]);

        $validatedData['section_id'] = $this->section->id;
        $validatedData['article_content'] = $this->article_content;
        $validatedData['media_path'] = null;
        $validatedData['thumbnail'] = null;
        $validatedData['type'] = $this->type;
        // Convert content_length from minutes to seconds if provided
        $validatedData['content_length'] = $this->content_length ? (int) $this->content_length * 60 : null;

        if ($this->curriculumVideo) {
            $fileName = time() . '_' . $this->curriculumVideo->getClientOriginalName();
            $filePath = $this->curriculumVideo->storeAs('curriculum_videos', $fileName, getStorageDisk());
            if ($filePath) {
                $validatedData['media_path'] = $filePath;
                $validatedData['type'] = 'video';
            }
        } elseif ($this->curriculumPdf) {
            $fileName = time() . '_' . $this->curriculumPdf->getClientOriginalName();
            $filePath = $this->curriculumPdf->storeAs('curriculum_videos', $fileName, getStorageDisk());
            if ($filePath) {
                $validatedData['media_path'] = $filePath;
                $validatedData['type'] = 'pdf';
            }
        } elseif ($this->url) {
            $validatedData['media_path'] = $this->url;
            $validatedData['type'] = 'url';
        }

        $curriculum = (new CurriculumService())->createCurriculum($validatedData);
        $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_created_successfully'), message: __('courses::courses.curriculum_created_successfully'));
        $this->addCurriculumState = false;
        $this->updateActiveCurriculumItem($curriculum->toArray());
        $this->resetErrorBag();
        $this->reset(['title', 'description', 'type', 'curriculumVideo', 'curriculumPdf', 'url', 'isCurriculumEditing']);
    }

    public function updatedActiveCurriculumItem($value, $key)
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        if (!empty($this->activeCurriculumItem['id']) && $key == 'is_preview') {
            $isUpdated = (new CurriculumService())->updateCurriculum($this->activeCurriculumItem['id'], ['is_preview' => $value]);
            if (!$isUpdated) {
                $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.curriculum_not_found'), message: __('courses::courses.curriculum_not_found'));
            } else {
                $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
            }
        }
    }

    public function rules(): array
    {
        return (new CurriculumRequest())->rules() + [
            'url' => 'nullable|url',
            'content_length' => 'nullable|numeric|min:0',  // Validate content_length as a non-negative number
        ];
    }

    public function messages(): array
    {
        return (new CurriculumRequest())->messages() + [
            'url.url' => 'Please enter a valid URL.',
            'url.required' => 'Please enter a URL.',
            'content_length.numeric' => 'Content length must be a number.',
            'content_length.min' => 'Content length cannot be negative.',
        ];
    }

    public function updateCurriculumContent()
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        if ($this->activeCurriculumItem['type'] == 'video') {
            if ($this->curriculumVideo) {
                if ($this->curriculumVideo instanceof \Illuminate\Http\UploadedFile) {
                    $fileName = time() . '_' . $this->curriculumVideo->getClientOriginalName();
                    $filePath = $this->curriculumVideo->storeAs('curriculum_videos', $fileName, getStorageDisk());
                    if ($filePath) {
                        $this->curriculumVideo = $filePath;
                    } else {
                        $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.upload_failed'), message: __('courses::courses.upload_failed'));
                        return;
                    }
                }
                $curriculum = (new CurriculumService())->updateCurriculum(
                    $this->activeCurriculumItem['id'],
                    [
                        'media_path' => $this->curriculumVideo,
                        'type' => 'video',
                        'content_length' => $this->duration,
                        'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false
                    ]
                );
                $this->updateActiveCurriculumItem($curriculum->toArray());
                $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
                $this->curriculumVideo = null;
            } else {
                $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.please_add_a_video'), message: __('courses::courses.please_add_a_video'));
            }
        } elseif ($this->activeCurriculumItem['type'] == 'pdf') {
            if ($this->curriculumPdf) {
                if ($this->curriculumPdf instanceof \Illuminate\Http\UploadedFile) {
                    $fileName = time() . '_' . $this->curriculumPdf->getClientOriginalName();
                    $filePath = $this->curriculumPdf->storeAs('curriculum_videos', $fileName, getStorageDisk());
                    if ($filePath) {
                        $this->curriculumPdf = $filePath;
                    } else {
                        $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.upload_failed'), message: __('courses::courses.upload_failed'));
                        return;
                    }
                }
                // Convert content_length from minutes to seconds
                $contentLengthInSeconds = $this->content_length ? (int) $this->content_length * 60 : 0;
                $curriculum = (new CurriculumService())->updateCurriculum(
                    $this->activeCurriculumItem['id'],
                    [
                        'media_path' => $this->curriculumPdf,
                        'type' => 'pdf',
                        'content_length' => $contentLengthInSeconds,
                        'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false
                    ]
                );
                $this->updateActiveCurriculumItem($curriculum->toArray());
                $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
                $this->curriculumPdf = null;
            } else {
                $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.please_add_a_pdf'), message: __('courses::courses.please_add_a_pdf'));
            }
        } elseif ($this->activeCurriculumItem['type'] == 'yt_link') {
            $this->validate([
                'yt_link' => [
                    'required',
                    'url',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[a-zA-Z0-9_-]+(&.*)?$/', $value)) {
                            $fail('Please enter a valid YouTube video link.');
                        }
                    },
                ],
            ], [
                'yt_link.required' => 'Please enter a YouTube link.',
                'yt_link.url' => 'Please enter a valid URL.',
            ]);
            $curriculum = (new CurriculumService())->updateCurriculum(
                $this->activeCurriculumItem['id'],
                [
                    'media_path' => $this->yt_link,
                    'type' => $this->activeCurriculumItem['type'],
                    'content_length' => $this->duration,
                    'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false,
                ]
            );
            $this->updateActiveCurriculumItem($curriculum->toArray());
            $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
        } elseif ($this->activeCurriculumItem['type'] == 'vm_link') {
            $this->validate([
                'vm_link' => 'required|url',
            ], [
                'vm_link.required' => 'Please enter a valid Vimeo link',
                'vm_link.url' => 'Please enter a valid Vimeo link',
            ]);
            $curriculum = (new CurriculumService())->updateCurriculum(
                $this->activeCurriculumItem['id'],
                [
                    'media_path' => $this->vm_link,
                    'type' => $this->activeCurriculumItem['type'],
                    'content_length' => $this->duration,
                    'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false,
                ]
            );
            $this->updateActiveCurriculumItem($curriculum->toArray());
            $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
        } elseif ($this->activeCurriculumItem['type'] == 'url') {
            $this->validate([
                'url' => 'required|url',
            ], [
                'url.required' => 'Please enter a URL.',
                'url.url' => 'Please enter a valid URL.',
            ]);
            $contentLengthInSeconds = $this->content_length ? (int) $this->content_length * 60 : 0;
            $curriculum = (new CurriculumService())->updateCurriculum(
                $this->activeCurriculumItem['id'],
                [
                    'media_path' => $this->url,
                    'type' => 'url',
                    'content_length' => $contentLengthInSeconds,
                    'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false,
                ]
            );
            $this->updateActiveCurriculumItem($curriculum->toArray());
            $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
        } elseif ($this->activeCurriculumItem['type'] == 'article') {
            $this->validate([
                'article_content' => 'required|string'
            ]);
            $content = strip_tags($this->article_content);
            $wordCount = str_word_count($content);
            $totalMinutes = ceil($wordCount / 238);
            $duration = $totalMinutes > 0 ? $totalMinutes * 60 : 0;
            $curriculum = (new CurriculumService())->updateCurriculum(
                $this->activeCurriculumItem['id'],
                [
                    'article_content' => $this->article_content,
                    'type' => 'article',
                    'content_length' => $duration,
                    'is_preview' => !empty($this->activeCurriculumItem['is_preview']) ? $this->activeCurriculumItem['is_preview'] : false
                ]
            );
            $this->updateActiveCurriculumItem($curriculum->toArray());
            $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
        }
    }

    public function removeCurriculumContent()
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $this->curriculumVideo = null;
        $this->curriculumPdf = null;
        $this->yt_link = null;
        $this->vm_link = null;
        $this->url = null;

        $curriculum = (new CurriculumService())->updateCurriculum($this->activeCurriculumItem['id'], ['media_path' => null]);
        $this->updateActiveCurriculumItem($curriculum->toArray());
        $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_updated_successfully'), message: __('courses::courses.curriculum_updated_successfully'));
    }

    public function editCurriculumModal($curriculum)
    {
        $this->resetErrorBag();
        $this->title = $curriculum['title'];
        $this->description = $curriculum['description'];
        $this->editCurriculumData = $curriculum;
        $this->dispatch('initEditor', target: '#edit_curriculum_des_' . $this->section->id, content: $curriculum['description'], modal: '#edit-curriculum-' . $this->section->id);
    }

    public function updateCurriculumOrder($list)
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        (new CurriculumService())->sortCurriculumItems($list, $this->section->id);
    }

    public function deleteRecord($curriculumId)
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $isDeleted = (new CourseService())->deleteCurriculum((int) $curriculumId);
        if ($isDeleted) {
            $this->dispatch('showAlertMessage', type: 'success', title: __('courses::courses.curriculum_deleted_successfully'), message: __('courses::courses.curriculum_deleted_successfully'));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.curriculum_not_found'), message: __('courses::courses.curriculum_not_found'));
        }
    }
}

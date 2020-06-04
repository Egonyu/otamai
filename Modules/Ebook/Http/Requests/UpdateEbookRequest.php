<?php

namespace Modules\Ebook\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Ebook\Entities\Ebook;
use Modules\Base\Http\Requests\Request;

class UpdateEbookRequest extends Request
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        $rules= [
            'title' => 'required',
            'description' => 'required',
            'publication_year' => 'nullable|integer|min:1900',
            'categories' => 'required',
            'authors' => 'required',
            'book_cover' => 'nullable|mimes:jpeg,png,jpg',
            'file_type' => ['required', Rule::in('upload', 'embed_code', 'external_link')],
            'book_file' => 'required_if:file_type,upload|mimes:pdf,epub,docx,doc,txt,pptx,ppt,xls,xlsx',
            'file_url' => 'required_if:file_type,external_link|nullable|url|url_ext',
            'embed_code' => 'required_if:file_type,embed_code',
        ];
        
        if($this->has('ftypeuou')){
            $rules['book_file'] = 'nullable|mimes:pdf,epub,docx,doc,txt,pptx,ppt,xls,xlsx';
        }
        
        return $rules;
    }

    
}

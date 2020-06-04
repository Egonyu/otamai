@include('files::admin.pdf_picker.book-cover', [
    'title' => trans('ebook::ebooks.form.book_cover'),
    'inputName' => 'files[book_cover]',
    'file' => $ebook->bookcover,
])

<hr>

{{ Form::select('file_type', clean(trans('ebook::attributes.file_type')), $errors, clean(trans('ebook::ebooks.form.file_type')), $ebook, ['required' => true]) }}

<div class="link-field external_link-field {{ old('file_type', $ebook->file_type) !== 'external_link' ? 'd-none' :'' }}">
    {{ Form::text('file_url', trans('ebook::ebooks.form.book_file_external'), $errors, $ebook, ['required' => true]) }}
</div>

<div class="link-field embed_code-field {{ old('file_type', $ebook->file_type) !== 'embed_code' ? 'd-none' :'' }}" >
   {{ Form::textarea('embed_code', clean(trans('ebook::ebooks.form.embed_code')), $errors, $ebook, ['rows' => 2,'required' => true,'help'=> clean(trans('ebook::ebooks.form.embed_code_help_text'))]) }}

</div>

<div class="link-field upload-field {{ old('file_type', $ebook->file_type ?? 'upload') !== 'upload' ? 'd-none' :'' }}">
    @include('files::admin.pdf_picker.single', [
        'title' => trans('ebook::ebooks.form.book_file'),
        'inputName' => 'files[book_file]',
        'file' => $ebook->bookfile,
    ])
</div>
@push('scripts')
    <script>
    (function () {
        "use strict";
            $('#file_type').on('change', (e) => {
                $('.link-field').addClass('d-none');
                $(`.${e.currentTarget.value}-field`).removeClass('d-none');
            });
    })();
    </script>
@endpush

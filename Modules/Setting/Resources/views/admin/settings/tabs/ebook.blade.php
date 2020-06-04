{{ Form::checkbox('enable_ebook_upload', clean(trans('setting::attributes.enable_ebook_upload')), clean(trans('setting::settings.form.allow_user_to_upload_new_eBook_from_frontend')), $errors, $settings) }}
{{ Form::checkbox('auto_approve_ebook', clean(trans('setting::attributes.auto_approve_ebook')), clean(trans('setting::settings.form.auto_approve_ebook_after_upload')), $errors, $settings) }}
{{ Form::checkbox('auto_approve_reviews', clean(trans('setting::attributes.auto_approve_reviews')), clean(trans('setting::settings.form.approve_reviews_automatically')), $errors, $settings) }}

{{ Form::checkbox('enable_ebook_report', clean(trans('setting::attributes.enable_ebook_report')), '', $errors, $settings) }}
{{-- Form::checkbox('enable_ebook_print', clean(trans('setting::attributes.enable_ebook_print')), '', $errors, $settings) --}}
{{ Form::checkbox('enable_ebook_download', clean(trans('setting::attributes.enable_ebook_download')), '', $errors, $settings) }}
@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}" method="POST" enctype="multipart/form-data" id="fileUploadForm">
                        @csrf
                        <div class="row g-4">
                                        <!-- Kullanıcı Seçimi -->
            <div class="col-md-6">
                <label class="form-label">Personel</label>
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:user"></iconify-icon>
                    </span>
                    <select class="form-control" name="user_id" id="user_id" required>
                        <option value="">Personel Seçiniz</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('user_id') == $user->id || (isset($item) && $item->user_id == $user->id)) ? 'selected' : '' }}>
                                {{ $user->name }} {{ $user->surname }}
                            </option>
                        @endforeach
                    </select>
                    <x-form-error field="user_id" />
                </div>
            </div>

                                        <!-- Dosya Tipi Seçimi -->
            <div class="col-md-6">
                <label class="form-label">Dosya Tipi</label>
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:document"></iconify-icon>
                    </span>
                    <select class="form-control" name="file_type_id" id="file_type_id" required>
                        <option value="">Dosya Tipi Seçiniz</option>
                        @foreach($fileTypes as $fileType)
                            <option value="{{ $fileType->id }}" {{ (old('file_type_id') == $fileType->id || (isset($item) && $item->file_type_id == $fileType->id)) ? 'selected' : '' }}>
                                {{ $fileType->name }} {{ !empty($fileType->allowed_extensions) ? '(' . $fileType->allowed_extensions . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-form-error field="file_type_id" />
                </div>
            </div>

                                        <!-- Başlık -->
            <div class="col-md-12">
                <label class="form-label">Başlık</label>
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:text-align-left"></iconify-icon>
                    </span>
                    <input type="text" class="form-control"
                        name="title" placeholder="Dosya için başlık giriniz (opsiyonel)"
                        value="{{ old('title') ?? ($item->title ?? '') }}">
                    <x-form-error field="title" />
                </div>
            </div>

                            <!-- Dosya Yükleme -->
                            <div class="col-md-12">
                                <label class="form-label">Dosya <span class="text-danger">*</span></label>
                                <div class="file-upload-container">
                                    <div class="file-upload-area mb-3" id="dropzone">
                                        <div class="file-select-button">
                                            <iconify-icon icon="carbon:cloud-upload" class="icon"></iconify-icon>
                                            <span>Dosya Seçin veya Sürükleyin</span>
                                        </div>
                                        <input type="file" name="file" id="fileInput" class="file-input" {{ !isset($item->id) ? 'required' : '' }}>
                                    </div>
                                    @if($errors->has('file'))
                                        <div class="text-danger my-2">{{ $errors->first('file') }}</div>
                                    @endif

                                    <div class="file-preview-container {{ isset($item->id) ? 'd-block' : 'd-none' }}" id="filePreviewContainer">
                                        <div class="file-preview">
                                            <div class="file-info">
                                                <div class="file-icon">
                                                    <iconify-icon icon="carbon:document" class="icon"></iconify-icon>
                                                </div>
                                                <div class="file-details">
                                                    <div class="file-name" id="fileName">{{ $item->original_filename ?? '' }}</div>
                                                    <div class="file-size" id="fileSize">{{ $item->human_file_size ?? '' }}</div>
                                                </div>
                                            </div>
                                            <div class="file-actions">
                                                @if(isset($item->id))
                                                    <a href="{{ $item->file_url ?? '#' }}" target="_blank" class="btn btn-sm btn-info view-file">
                                                        <iconify-icon icon="carbon:view" class="icon"></iconify-icon> Görüntüle
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-sm btn-info view-file d-none" id="viewFileBtn" target="_blank">
                                                        <iconify-icon icon="carbon:view" class="icon"></iconify-icon> Görüntüle
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger" id="removeFileBtn">
                                                    <iconify-icon icon="carbon:trash-can" class="icon"></iconify-icon> Kaldır
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden file info inputs -->
                                <input type="hidden" name="temp_file" id="tempFile">
                                <input type="hidden" name="file_path" id="filePath" value="{{ $item->file_path ?? '' }}">
                                <input type="hidden" name="original_filename" id="originalFilename" value="{{ $item->original_filename ?? '' }}">
                                <input type="hidden" name="file_extension" id="fileExtension" value="{{ $item->file_extension ?? '' }}">
                                <input type="hidden" name="file_size" id="fileSizeInput" value="{{ $item->file_size ?? '' }}">
                            </div>

                                        <!-- Açıklama -->
            <div class="col-md-12">
                <label class="form-label">Açıklama</label>
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:text-annotation"></iconify-icon>
                    </span>
                    <textarea class="form-control" name="description"
                        placeholder="Dosya hakkında açıklama giriniz" rows="3">{{ old('description') ?? ($item->description ?? '') }}</textarea>
                    <x-form-error field="description" />
                </div>
            </div>

                                        <!-- Durum -->
            <div class="col-md-6">
                <label class="form-label">Durumu</label>
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="fluent:status-16-regular"></iconify-icon>
                    </span>
                    <select class="form-control" name="is_active">
                        <option value="1" {{ $item->is_active == 1 || is_null($item->id) ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="0" {{ $item->is_active == 0 && !is_null($item->id) ? 'selected' : '' }}>
                            Pasif
                        </option>
                    </select>
                    <x-form-error field="is_active" />
                </div>
            </div>

                                        <!-- Submit Button -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary-600">Kaydet</button>
            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
<style>
    /* Textarea için icon pozisyonu düzeltmesi */
    .icon-field textarea.form-control ~ .icon {
        top: 20px;
    }

    .file-upload-area {
        border: 2px dashed #ccc;
        border-radius: 5px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .file-upload-area:hover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }

    .file-upload-area.drag-over {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }

    .file-select-button {
        font-size: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .file-select-button .icon {
        font-size: 48px;
        color: #0d6efd;
    }

    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .file-preview-container {
        margin-top: 10px;
    }

    .file-preview {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 5px;
        background-color: #f8f9fa;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .file-icon {
        font-size: 24px;
        color: #0d6efd;
    }

    .file-name {
        font-weight: 500;
    }

    .file-size {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .file-actions {
        display: flex;
        gap: 10px;
    }
</style>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const dropzone = $('#dropzone');
        const fileInput = $('#fileInput');
        const filePreviewContainer = $('#filePreviewContainer');
        const fileName = $('#fileName');
        const fileSize = $('#fileSize');
        const viewFileBtn = $('#viewFileBtn');
        const removeFileBtn = $('#removeFileBtn');
        const tempFile = $('#tempFile');
        const filePath = $('#filePath');
        const originalFilename = $('#originalFilename');
        const fileExtension = $('#fileExtension');
        const fileSizeInput = $('#fileSizeInput');
        const fileTypeSelect = $('#file_type_id');

        // Drag and drop functionality
        dropzone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });

        dropzone.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });

        dropzone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');

            if(e.originalEvent.dataTransfer.files.length) {
                fileInput[0].files = e.originalEvent.dataTransfer.files;
                handleFileUpload(e.originalEvent.dataTransfer.files[0]);
            }
        });

        // File input change
        fileInput.on('change', function() {
            if(this.files.length) {
                handleFileUpload(this.files[0]);
            }
        });

        // Remove file button
        removeFileBtn.on('click', function() {
            resetFileUpload();

            // If there's a temp file, delete it from server
            if(tempFile.val()) {
                $.ajax({
                    url: "{{ route('backend.user_file_delete_temp') }}",
                    type: 'POST',
                    data: {
                        temp_file: tempFile.val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log('Temp file deleted');
                    }
                });
            }
        });

        // File type change - validate selected file against allowed extensions
        fileTypeSelect.on('change', function() {
            validateFileType();
        });

        // Handle file upload function
        function handleFileUpload(file) {
            // First validate against file type
            if(!validateFileType(file)) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('file_type_id', fileTypeSelect.val());
            formData.append('_token', "{{ csrf_token() }}");

            // Show loading indicator
            dropzone.html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Yükleniyor...</div></div>');

            $.ajax({
                url: "{{ route('backend.user_file_upload_temp') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.success) {
                        // Update UI with file info
                        fileName.text(response.original_filename);
                        fileSize.text(humanFileSize(response.file_size));

                        // Show file preview container
                        filePreviewContainer.removeClass('d-none');

                        // Update hidden inputs
                        tempFile.val(response.temp_file);
                        filePath.val(response.file_path);
                        originalFilename.val(response.original_filename);
                        fileExtension.val(response.file_extension);
                        fileSizeInput.val(response.file_size);

                        // Update view button
                        viewFileBtn.removeClass('d-none').attr('href', response.file_url);

                        // Reset dropzone
                        resetDropzone();
                    } else {
                        // Show error
                        alert(response.message || 'Dosya yüklenirken bir hata oluştu.');
                        resetDropzone();
                    }
                },
                error: function() {
                    alert('Dosya yüklenirken bir hata oluştu.');
                    resetDropzone();
                }
            });
        }

        // Helper function to format file size
        function humanFileSize(size) {
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let i = 0;

            while(size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }

            return Math.round(size * 100) / 100 + ' ' + units[i];
        }

        // Reset the dropzone to original state
        function resetDropzone() {
            dropzone.html(`
                <div class="file-select-button">
                    <iconify-icon icon="carbon:cloud-upload" class="icon"></iconify-icon>
                    <span>Dosya Seçin veya Sürükleyin</span>
                </div>
                <input type="file" name="file" id="fileInput" class="file-input">
            `);

            // Reattach event listener to new file input
            $('#fileInput').on('change', function() {
                if(this.files.length) {
                    handleFileUpload(this.files[0]);
                }
            });
        }

        // Reset the entire file upload section
        function resetFileUpload() {
            resetDropzone();
            filePreviewContainer.addClass('d-none');
            tempFile.val('');
            filePath.val('');
            originalFilename.val('');
            fileExtension.val('');
            fileSizeInput.val('');
            viewFileBtn.addClass('d-none').attr('href', '#');
        }

        // Validate file against selected file type
        function validateFileType(file) {
            const fileTypeId = fileTypeSelect.val();
            if(!fileTypeId) {
                alert('Lütfen önce bir dosya tipi seçiniz.');
                return false;
            }

            // If no file provided, just return true (used when file type changes)
            if(!file) {
                return true;
            }

            // Get selected option
            const selectedOption = fileTypeSelect.find('option:selected');
            const optionText = selectedOption.text();

            // Check if there are allowed extensions in the option text
            const match = optionText.match(/\((.*?)\)/);

            if(match) {
                const allowedExtensions = match[1].split(',').map(ext => ext.trim().toLowerCase());
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if(!allowedExtensions.includes(fileExtension)) {
                    alert(`Bu dosya tipi için izin verilen uzantılar: ${allowedExtensions.join(', ')}`);
                    resetDropzone();
                    return false;
                }
            }

            // Check file size (max 10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if(file.size > maxSize) {
                alert('Dosya boyutu çok büyük. Maksimum 10MB yükleyebilirsiniz.');
                resetDropzone();
                return false;
            }

            return true;
        }
    });
</script>
@endsection

<div class="d-flex align-items-center gap-10 justify-content-center">
    <button type="button"
            class="edit-assignment bg-warning-focus text-warning-600 bg-hover-warning-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
            data-id="{{ $row->id }}"
            data-shift-id="{{ $row->shift_definition_id }}"
            data-user="{{ $row->user?->name }} {{ $row->user?->surname }}"
            title="Vardiya Düzenle">
        <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
    </button>
    <button type="button"
            class="remove-assignment bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle"
            data-id="{{ $row->id }}"
            data-user="{{ $row->user?->name }} {{ $row->user?->surname }}"
            title="Vardiya Atamasını Kaldır">
        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
    </button>
</div>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0 fs-6"><?php echo $title;?></h6>
    <ul class="d-flex align-items-center gap-2 fs-6">
        <li class="fw-medium">
            <a href="{{ route('backend.index') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Anasayfa
            </a>
        </li>
        <li>/</li>
        <li class="fw-medium"><?php echo $subTitle;?></li>
    </ul>
</div>

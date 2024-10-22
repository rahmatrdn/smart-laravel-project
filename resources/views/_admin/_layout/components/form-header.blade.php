<div class="row mb-4">
    <div class="col-md-8">
        <div class="fs-sm">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ base_url($page['route'] . '/') }}" class="link-primary fw-bold" navigate>
                            {{ $page['title'] }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $type }} Data</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-0 text-start text-md-start text-lg-end">
            <a href="{{ base_url($page['route'] . '/') }}"
                class="btn btn-outline-warning btn-sm fw-bold" navigate>
                <b>← Kembali</b>
            </a>
        </div>
    </div>
</div>
<h4 class="mb-3">{{ $type }} Data <b>{{ $page['title'] }}</b></h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6">
                            <h4 class="mb-0">Detail <b>{{ $page['title'] }}</b></h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="">
                                <a href="{{ base_url($page['route'] . '/') }}" class="btn btn-outline-indigo btn-sm fw-bold" navigate>
                                    <b>← Kembali</b>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <div class="mb-0">
                                <label for="">Nama</label>
                                <p class="mb-0 fs-5"><strong>{{ title($data->name) }} </strong></p>
                            </div>
                            <hr class="dotted">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="">Jenis Anggota </label>
                                <p class="mb-0 fs-5"><strong>{{ getMemberCtg($data->category_id) }}</strong></p>
                            </div>
                            <div class="mb-2">
                                <label for="">Identitas ID ({{ getMemberIdentityCtg($data->category_id) }})</label>
                                <p class="mb-0 fs-5"><strong>{{ title($data->identity_no) }} {{ ($data->category_id == 2) ? " - ".$data->identity_type : "" }}</strong></p>
                            </div>
                            <div class="mb-2">
                                <label for="">Tahun Masuk </label>
                                <p class="mb-0 fs-5"><strong>{{ title($data->join_year) }}</strong></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
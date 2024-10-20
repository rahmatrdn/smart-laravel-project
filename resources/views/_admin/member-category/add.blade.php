    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="mb-4">Tambah Data <b>{{ $page['title'] }}</b></h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="">
                                <a href="{{ base_url($page['route'] . '/') }}"
                                    class="btn btn-outline-warning btn-sm fw-bold" navigate>
                                    <b>← Kembali</b>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <b>Terjadi kesalahan pada proses input data</b> <br>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ base_url($page['route'] . '/add') }}" form-navigate>
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Kategori</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}">
                            </div>
                            <button type="submit" class="btn btn-primary"><b>Simpan Data</b></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

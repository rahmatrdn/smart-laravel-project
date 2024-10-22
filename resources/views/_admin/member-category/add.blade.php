    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    @include('_admin._layout.components.form-header', ['type' => 'Tambah'])

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

                        <form method="POST" action="{{ base_url($page['route'] . '/add') }}" navigate-form>
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Kategori</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}">
                            </div>
                            <button type="submit" class="btn btn-primary bg-gradient"><b>Simpan Data</b></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                    class="btn btn-outline-indigo btn-sm fw-bold" navigate>
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

                        <form method="POST" action="{{ base_url($page['route'] . '/add') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori Anggota</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">- Pilih Kategori Anggota -</option>
                                    @foreach ($memberCategories as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="identity_no" class="form-label">No. Identitas</label>
                                <input type="text" class="form-control" name="identity_no" id="identity_no"
                                    value="{{ old('identity_no') }}" required>
                                <small>Perhatian! Jika Siswa pakai NISN, Jika Guru / Pegawai pakai NIP</small>
                            </div>
                            <div class="mb-3">
                                <label for="identity_type" class="form-label">Jenis Identitas (opsional)</label>
                                <input type="text" class="form-control" name="identity_type" id="identity_type"
                                    value="{{ old('identity_type') }}">
                                <small>Jika siswa, diisi dengan nama jurusan + kelas. Contoh: RPL 1, RPL 2, TKJ 1,
                                    dll.</small>
                            </div>
                            <div class="mb-3">
                                <label for="join_year" class="form-label">Tahun Masuk</label>
                                <input type="text" class="form-control" name="join_year" id="join_year"
                                    value="{{ old('join_year') }}">
                                <small>Tahun masuk anggota di sekolah</small>
                            </div>
                            <button type="submit" class="btn btn-primary"><b>Simpan Data</b></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

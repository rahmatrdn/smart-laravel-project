<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-4">Ubah Password Aplikasi</b></h4>
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

                    <form method="POST" action="{{ base_url($page['route'] . '/change-password') }}" navigate-form>
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" id="current_password">
                            <div>
                                <small>Pada Email: <b>{{ Auth::user()->email }}</b></small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="mb-3">
                            <label for="re_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="re_password" id="re_password">
                        </div>
                        <button type="submit" class="btn btn-primary bg-gradient"><b>Ubah Password</b></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

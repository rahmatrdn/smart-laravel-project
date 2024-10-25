    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-0    ">
                        <div class="col-md-6">
                            <h4 class="fw-bolder mb-4">{{ $page['title'] }}</h4>
                        </div>
                    </div>

                    <ul class="nav nav-pills">
                        <li class="nav-item me-2">
                            <a class="nav-link rounded-5 px-4 active" aria-current="page" href="#">Umum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-5 px-4" href="{{ base_url('setting/change-password') }}">Ubah
                                Password</a>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ base_url('setting/general') }}" method="POST">
                        @csrf
                        <div class="mb-3 row">
                            <label for="theme" class="col-sm-2 col-form-label">Warna Tampilan</label>
                            <div class="col-sm-4">
                                <select name="theme" id="theme" class="form-select">
                                    <option value="light" @selected($theme == 'light')>Terang (Light Mode)</option>
                                    <option value="dark" @selected($theme == 'dark')>Gelap (Dark Mode)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-0 row">
                            <label for="inputPassword" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-4">
                                <button class="btn btn-primary bg-gradient" type="submit"><b>Terapkan
                                        Perubahan</b></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4 class="fw-bolder mb-4">{{ $page['title'] }}</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-md-end text-start">
                                <a href="{{ base_url($page['route'] . '/add') }}" class="btn btn-primary btn fw-bold bg-gradient"
                                    navigate>
                                    @include('_admin._layout.icons.plus')
                                    <b>Tambah Data</b>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card rounded-3 border-1 border-primary-subtle shadow-sm">
                        <div class="card-body p-3">
                            <p class="text-gray-md letter-spacing-2 fs-1 mb-2">PENCARIAN DATA</p>
                            <form action="{{ base_url('member') }}" method="GET"
                                class="row gy-2 gx-3 align-items-center">
                                <input type="hidden" name="filter_on" value="true">
                                <div class="col- mb-2">
                                    <label class="visually-hidden" for="filter_name">Kata kunci Nama Buku</label>
                                    <input type="text" class="form-control" id="filter_name" autofocus
                                        name="filter_name" value="{{ $filter['filter_name'] ?? '' }}"
                                        placeholder="Kata kunci Nama Anggota atau No. Identitas">
                                </div>
                                <div class="col-md-auto mb-2">
                                    <label class="visually-hidden" for="filter_category_id">Preference</label>
                                    <select class="form-select" id="filter_category_id" name="filter_category_id">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($memberCategories as $b)
                                            <option value="{{ $b->id }}" @selected($b->id == get($filter['filter_category_id'] ?? ''))>
                                                {{ title($b->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <button type="submit" class="btn btn-primary bg-gradient"><b>Pencarian</b></button>
                                    @if (!empty($filter['filter_on']))
                                        <a href="{{ base_url('member') }}" class="btn btn-outline-warning ms-1"
                                            navigate>Reset
                                            Filter</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive-sm">
                        <table class="table table-bordered table-hover mt-3 table-sm">
                            <thead class="table-light">
                                <th class="table-header" style="--width: 80%">ANGGOTA</th>
                                <th class="table-header text-center">AKSI</th>
                            </thead>
                            <tbody>
                                @foreach ($data as $d)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="badge bg-light text-dark">{{ $d->category }}</span>
                                                <h5 class="mb-1 mt-2">
                                                    <strong>{{ title($d->name, $d->category_id) }}</strong>
                                                </h5>
                                                <p class="mb-0">{{ getMemberIdentityCtg($d->category_id) }}:
                                                    {{ $d->identity_no }} - ({{ $d->join_year }})
                                                </p>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-light btn-sm shadow-sm border-1 border-primary-subtle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-dots">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                        <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                        <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                    </svg>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item"
                                                            href="{{ base_url($page['route'] . "/detail/{$d->id}") }}"
                                                            navigate>Lihat</a>
                                                    </li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ base_url($page['route'] . "/update/{$d->id}") }}"
                                                            navigate>Edit</a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <a class="dropdown-item text-danger"
                                                        href="{{ base_url($page['route'] . "/delete/{$d->id}") }}"
                                                        confirm-message="Apakah kamu yakin menghapus {{ $d->name }}?"
                                                        navigate-api-confirm>Hapus</a>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (!count($data))
                        @include('_admin._layout.components.empty-data', ['title' => $page['title']])
                    @endif

                    <div>
                        {{ !empty($data) ? $data->links() : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ url('admin-ui') }}/assets/js/paginate.js"></script>

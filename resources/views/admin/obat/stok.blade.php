<x-layouts.app title="Kelola Stok Obat">
    <div class="container-fluid px-4 mt-4">
        <div class="card col-md-6">
            <div class="card-header">
                <h5>Kelola Stok: {{ $obat->nama_obat }}</h5>
            </div>

            <div class="card-body">

                {{-- ERROR HANDLING --}}
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('obat.stok.update', $obat->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Stok Saat Ini</label>
                        <input type="text" class="form-control"
                               value="{{ $obat->stok }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="stok" class="form-control"
                               min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Aksi</label>
                        <select name="aksi" class="form-control" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="tambah">Tambah Stok</option>
                            <option value="kurang">Kurangi Stok</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Stok
                    </button>

                    <a href="{{ route('obat.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
